<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Models\DocumentHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'system_identifier',
        'title',
        'document_number',
        'description',
        'category',
        'status',
        'active',
        'file_path',
        'original_filename',
        'valid_from',
        'valid_to',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function (Document $document) {
            if (empty($document->system_identifier)) {
                $document->system_identifier = 'DOC-' . strtoupper(Str::random(8));
            }

            if (empty($document->created_by) && Auth::check()) {
                $document->created_by = Auth::id();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function histories()
    {
        return $this->hasMany(DocumentHistory::class)->orderBy('created_at', 'desc');
    }
}
