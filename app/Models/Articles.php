<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Articles extends Model
{

    protected $fillable = [
        'title',
        'content',
        'url',
        'external_id',
        'published_at',
        'author',
        'category',
        'source_id',
        'image_url',
        'summary'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];


    public function source(): BelongsTo
    {
        return $this->belongsTo(Sources::class);
    }
}
