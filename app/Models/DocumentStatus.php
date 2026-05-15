<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DocumentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted()
    {
        static::creating(function (DocumentStatus $status) {
            if (empty($status->slug)) {
                $status->slug = Str::slug($status->name);
            }
        });
    }
}
