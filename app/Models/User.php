<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Document;
use App\Models\DocumentHistory;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'roles'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const ROLE_USER = 'user';
    public const ROLE_DOCUMENT_STAFF = 'pracownik_merytoryczny';
    public const ROLE_ADMIN = 'admin';

    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles ?? [], true);
    }

    public function isDocumentManager(): bool
    {
        return $this->hasRole(self::ROLE_DOCUMENT_STAFF);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function getRolesLabelAttribute(): string
    {
        $labels = array_map(function (string $role) {
            return match ($role) {
                self::ROLE_DOCUMENT_STAFF => 'Pracownik merytoryczny',
                self::ROLE_ADMIN => 'Administrator',
                default => 'Użytkownik',
            };
        }, $this->roles ?? [self::ROLE_USER]);

        return implode(', ', $labels);
    }

    public function getRoleLabelsAttribute(): array
    {
        return array_map(function (string $role) {
            return match ($role) {
                self::ROLE_DOCUMENT_STAFF => 'Pracownik merytoryczny',
                self::ROLE_ADMIN => 'Administrator',
                default => 'Użytkownik',
            };
        }, $this->roles ?? [self::ROLE_USER]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'roles' => 'array',
        ];
    }

    public function documents()
    {
        return $this->hasMany(Document::class, 'created_by');
    }

    public function documentHistories()
    {
        return $this->hasMany(DocumentHistory::class);
    }
}
