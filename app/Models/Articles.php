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


    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%");
        });
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBySource($query, $source)
    {
        return $query->whereHas('source', function ($q) use ($source) {
            $q->where('name', $source);
        });
    }

    public function scopeByAuthor($query, $author)
    {
        return $query->where('author', $author);
    }

    public function scopeByDateRange($query, $from, $to)
    {
          return $query->where('published_at','>=',$from)->where('published_at','<=',"$to 23:59:59");
    }
}
