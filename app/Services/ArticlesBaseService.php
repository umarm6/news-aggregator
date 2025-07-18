<?php

namespace App\Services;

use App\Models\Articles;
use App\Models\Sources;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

abstract class ArticlesBaseService
{
    protected Sources $sources;
    protected array $config;

    public function __construct(Sources $sources)
    {
        $this->sources = $sources;
        $this->config = config('news.sources.' . strtolower($sources->name), []);
    }

    abstract public function fetchArticles(int $limit = 100): array;
    abstract protected function transformArticle(array $rawArticle): array;

    protected function makeRequest(string $url, array $params = []): array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get($url, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("API request failed for {$this->sources->name}", [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return [];
        } catch (\Exception $e) {

            Log::error("API request exception for {$this->sources->name}", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    public function storeArticles(array $articles): int
    {
        $stored = 0;

        foreach ($articles as $articleData) {
            $transformed = $this->transformArticle($articleData);

            if ($this->isValidArticle($transformed)) {

                $article = Articles::updateOrCreate(
                    ['url' => $transformed['url']],
                    array_merge($transformed, ['source_id' => $this->sources->id])
                );

                if ($article->wasRecentlyCreated) {
                    $stored++;
                }
            }
        }

        return $stored;
    }

    protected function isValidArticle(array $article): bool
    {
         return !empty($article['title']) &&
            !empty($article['url']) &&
            !empty($article['published_at']);
    }
}
