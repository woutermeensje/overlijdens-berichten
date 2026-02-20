<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'path',
        'source_url',
        'meta_description',
        'content_html',
        'is_active',
        'is_imported',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_imported' => 'boolean',
    ];
}
