<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Articles extends Model
{
    use HasFactory;
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

    public static function getCachedCategories(): array
    {
        return Cache::remember('categories:all', 3600, function () {
            return self::select('category')
                ->distinct()
                ->whereNotNull('category')
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
        });
    }

    public static function getCachedSources(): array
    {
        return Cache::remember('sources:all', 3600, function () {
            return self::join('sources', 'articles.source_id', '=', 'sources.id')
                ->select('sources.name', 'sources.id')
                ->distinct()
                ->orderBy('sources.name')
                ->get()
                ->toArray();
        });
    }

    public static function getCachedAuthors(): array
    {
        return Cache::remember('authors:all', 1800, function () {
            return self::select('author')
                ->distinct()
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->orderBy('author')
                ->limit(100)
                ->pluck('author')
                ->toArray();
        });
    }

    public static function getTrendingArticles(int $limit = 10): array
    {
        return Cache::remember('articles:trending', 900, function () use ($limit) {
            return self::with('source')
                ->where('published_at', '>=', now()->subHours(24))
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

}
