<?php

namespace App\Models;

use App\Services\DocumentFamily;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    public const TYPE_DOCUMENT = 'document';

    public const TYPE_CHANGE = 'change';

    public const TYPE_REPEAL = 'repeal';

    public const RELATION_STATE_ACTIVE = 'active';

    public const RELATION_STATE_CHANGED = 'changed';

    public const RELATION_STATE_REPEALED = 'repealed';

    protected $fillable = [
        'system_identifier',
        'title',
        'document_number',
        'description',
        'category',
        'status',
        'type',
        'source_document_id',
        'active',
        'file_path',
        'original_filename',
        'valid_from',
        'valid_to',
        'created_by',
        'folder_id',
    ];

    protected $casts = [
        'active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public static function types(): array
    {
        return [
            self::TYPE_DOCUMENT,
            self::TYPE_CHANGE,
            self::TYPE_REPEAL,
        ];
    }

    public static function relationStates(): array
    {
        return [
            self::RELATION_STATE_ACTIVE,
            self::RELATION_STATE_CHANGED,
            self::RELATION_STATE_REPEALED,
        ];
    }

    protected static function booted()
    {
        static::creating(function (Document $document) {
            if (empty($document->system_identifier)) {
                $document->system_identifier = 'DOC-'.strtoupper(Str::random(8));
            }

            if (empty($document->created_by) && Auth::check()) {
                $document->created_by = Auth::id();
            }
        });

        static::saved(function (Document $document) {
            app(DocumentFamily::class)->forget();
            $family = $document->connectedDocuments();

            if (! $family->contains('type', self::TYPE_REPEAL)) {
                return;
            }

            DB::transaction(function () use ($family, $document) {
                $activeIds = $family->where('active', true)->pluck('id');
                self::query()->whereIn('id', $activeIds)->update(['active' => false]);

                foreach ($activeIds as $id) {
                    DocumentHistory::create([
                        'document_id' => $id,
                        'user_id' => Auth::id() ?? $document->created_by,
                        'changes' => ['active' => ['old' => true, 'new' => false]],
                    ]);
                }
            });

            $document->active = false;
            app(DocumentFamily::class)->forget();
        });

        static::deleted(fn () => app(DocumentFamily::class)->forget());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function folder()
    {
        return $this->belongsTo(Folder::class);
    }

    public function histories()
    {
        return $this->hasMany(DocumentHistory::class)->orderBy('created_at', 'desc');
    }

    public function sourceDocument()
    {
        return $this->belongsTo(self::class, 'source_document_id');
    }

    public function derivedDocuments()
    {
        return $this->hasMany(self::class, 'source_document_id');
    }

    public function latestDerivedDocument()
    {
        return $this->hasOne(self::class, 'source_document_id')->latestOfMany('created_at');
    }

    public function relationColorType(): ?string
    {
        $family = $this->connectedDocuments();

        if ($family->contains('type', self::TYPE_REPEAL)) {
            return self::TYPE_REPEAL;
        }

        return $family->contains('type', self::TYPE_CHANGE) ? self::TYPE_CHANGE : null;
    }

    public function connectedDocuments(): Collection
    {
        return app(DocumentFamily::class)->forDocument($this);
    }

    public function relationState(): string
    {
        return match ($this->relationColorType()) {
            self::TYPE_CHANGE => self::RELATION_STATE_CHANGED,
            self::TYPE_REPEAL => self::RELATION_STATE_REPEALED,
            default => self::RELATION_STATE_ACTIVE,
        };
    }
}
