<?php

namespace App\Services;

use Carbon\Carbon;

class GuardianApiService extends ArticlesBaseService
{
    public function fetchArticles(int $limit = 100): array
    {
        $apiKey = config('news.sources.guardian.api_key');
        $apiUrl= config('news.sources.guardian.base_url');
        $url = "$apiUrl/search";

        $params = [
            'api-key' => $apiKey,
            'page-size' => min($limit, 200),
            'order-by' => 'newest',
            'show-fields' => 'headline,body,byline,thumbnail,short-url',
            'from-date' => Carbon::now()->subDays(7)->format('Y-m-d'),
        ];

        $response = $this->makeRequest($url, $params);

        return $response['response']['results'] ?? [];
    }

    protected function transformArticle(array $rawArticle): array
    {
        return [
            'title' => $rawArticle['fields']['headline'] ?? $rawArticle['webTitle'] ?? '',
            'content' => $rawArticle['fields']['body'] ?? '',
            'summary' => $this->extractSummary($rawArticle['fields']['body'] ?? ''),
            'url' => $rawArticle['fields']['short-url'] ?? $rawArticle['webUrl'] ?? '',
            'published_at' => Carbon::parse($rawArticle['webPublicationDate'])->format('Y-m-d H:i:s'),
            'author' => $rawArticle['fields']['byline'] ?? 'Guardian Staff',
            'category' => $this->mapCategory($rawArticle['sectionName'] ?? 'general'),
            'image_url' => $rawArticle['fields']['thumbnail'] ?? null,
        ];
    }

    private function extractSummary(string $content): string
    {
        // Remove HTML tags
        $stripped = strip_tags($content);

        // Convert HTML entities
        $stripped = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Normalize whitespace
        $stripped = preg_replace('/\s+/', ' ', $stripped);
        $stripped = trim($stripped);

        // Ensure the string is valid UTF-8
        if (!mb_check_encoding($stripped, 'UTF-8')) {
            $stripped = mb_convert_encoding($stripped, 'UTF-8', 'UTF-8');
        }

        // Use UTF-8 safe string functions
        if (mb_strlen($stripped, 'UTF-8') > 200) {
            return mb_substr($stripped, 0, 200, 'UTF-8') . '...';
        }

        return $stripped;
    }

    private function mapCategory(string $section): string
    {
        $mapping = [
            'Technology' => 'technology',
            'Business' => 'business',
            'Sport' => 'sports',
            'Society' => 'health',
            'Science' => 'technology',
        ];

        return $mapping[$section] ?? strtolower($section);
    }
}
