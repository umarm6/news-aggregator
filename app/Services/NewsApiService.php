<?php

namespace App\Services;

use Carbon\Carbon;

class NewsApiService extends ArticlesBaseService
{
    public function fetchArticles(int $limit = 100 , $q = 'technology OR business OR sports OR health' ): array
    {
        $apiKey = config('news.sources.newsapi.api_key');
        $url = 'https://newsapi.org/v2/everything';

        $params = [
            'apiKey' => $apiKey,
            'pageSize' => min($limit, 100),
            'sortBy' => 'publishedAt',
            'language' => 'en',
            'q' => $q,
            'from' => Carbon::now()->subDays(7)->format('Y-m-d'),
        ];

        $response = $this->makeRequest($url, $params);

        return $response['articles'] ?? [];
    }

    protected function transformArticle(array $rawArticle): array
    {
        return [
            'title' => $rawArticle['title'] ?? '',
            'content' => $rawArticle['content'] ?? '',
            'summary' => $rawArticle['description'] ?? '',
            'url' => $rawArticle['url'] ?? '',
            'published_at' => Carbon::parse($rawArticle['publishedAt'])->format('Y-m-d H:i:s'),
            'author' => $rawArticle['author'] ?? 'Unknown',
            'category' => $this->extractCategory($rawArticle),
            'image_url' => $rawArticle['urlToImage'] ?? null,
        ];
    }

    private function extractCategory(array $article): string
    {
        $content = strtolower($article['title'] . ' ' . ($article['description'] ?? ''));

        $categories = [
            'technology' => ['tech', 'software', 'ai', 'computer', 'digital'],
            'business' => ['business', 'economy', 'market', 'finance', 'stock'],
            'sports' => ['sport', 'football', 'basketball', 'soccer', 'game'],
            'health' => ['health', 'medical', 'medicine', 'hospital', 'doctor'],
        ];

        foreach ($categories as $category => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword)) {
                    return $category;
                }
            }
        }

        return 'general';
    }
}
