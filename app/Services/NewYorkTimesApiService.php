<?php
// app/Services/NewYorkTimesApiService.php

namespace App\Services;

use App\Models\Sources;
use Carbon\Carbon;

class NewYorkTimesApiService extends ArticlesBaseService
{


    protected string $apiKey;
    protected string $apiURL;

    public function __construct(Sources $sources)
    {
        parent::__construct($sources);

        $this->apiKey =  config('news.sources.nytimes.api_key');
        $this->apiURL =  config('news.sources.nytimes.base_url');
    }

    private static array $characterReplacements = [
        '–' => '-', '—' => '-',  '…' => '...', '•' => '*',
            '€' => 'EUR', '£' => 'GBP', '¢' => 'cents',
            '©' => '(c)', '®' => '(R)', '™' => '(TM)',
    ];

    public function fetchArticles(int $limit = 100): array
    {
        // NYT API has different endpoints, we'll use the Most Popular and Article Search APIs
        $articles = [];

        // Fetch from Most Popular API (last 7 days)
        $popularArticles = $this->fetchMostPopularArticles($this->apiKey, min($limit, 50));
        $articles = array_merge($articles, $popularArticles);

        // Fetch from Article Search API for more recent articles
        if (count($articles) < $limit) {
            $remaining = $limit - count($articles);
            $searchArticles = $this->fetchSearchArticles($this->apiKey, $remaining);
            $articles = array_merge($articles, $searchArticles);
        }

        return $articles;
    }

    private function fetchMostPopularArticles(string $apiKey, int $limit): array
    {
        $url = "$this->apiURL/svc/mostpopular/v2/viewed/7.json";

        $params = [
            'api-key' => $apiKey,
        ];

        $response = $this->makeRequest($url, $params);

        $articles = $response['results'] ?? [];

        // NYT Most Popular API doesn't have a limit parameter, so we slice the results
        return array_slice($articles, 0, $limit);
    }

    private function fetchSearchArticles(string $apiKey, int $limit): array
    {
        $url = "$this->apiURL/svc/search/v2/articlesearch.json";

        $params = [
            'api-key' => $apiKey,
            'sort' => 'newest',
            'page' => 0,
            'begin_date' => Carbon::now()->subDays(7)->format('Ymd'),
            'end_date' => Carbon::now()->format('Ymd'),
            'fl' => 'web_url,headline,abstract,lead_paragraph,pub_date,byline,section_name,multimedia,source',
        ];

        $response = $this->makeRequest($url, $params);

        $articles = $response['response']['docs'] ?? [];

        return array_slice($articles, 0, $limit);
    }

    protected function transformArticle(array $rawArticle): array
    {
        // Handle different article formats from different NYT APIs
        if (isset($rawArticle['headline']['main'])) {
            // Article Search API format
            return $this->transformSearchArticle($rawArticle);
        } else {
            // Most Popular API format
            return $this->transformPopularArticle($rawArticle);
        }
    }

    private function transformSearchArticle(array $rawArticle): array
    {
        $title = $this->sanitizeText($rawArticle['headline']['main'] ?? '');
        $abstract = $this->sanitizeText($rawArticle['abstract'] ?? '');
        $leadParagraph = $this->sanitizeText($rawArticle['lead_paragraph'] ?? '');

        // Use abstract as summary, fall back to lead paragraph
        $summary = $abstract ?: $leadParagraph;

        // Extract author from byline
        $author = $this->extractAuthor($rawArticle['byline']['original'] ?? '');

        // Get main image
        $imageUrl = $this->extractImageUrl($rawArticle['multimedia'] ?? []);

        return [
            'title' => $title,
            'content' => $leadParagraph, // NYT Search API doesn't provide full content
            'summary' => $this->extractSummary($summary),
            'url' => $rawArticle['web_url'] ?? '',
            'published_at' => Carbon::parse($rawArticle['pub_date'])->format('Y-m-d H:i:s'),
            'author' => $author,
            'category' => $this->mapCategory($rawArticle['section_name'] ?? 'general'),
            'image_url' => $imageUrl,
        ];
    }

    private function transformPopularArticle(array $rawArticle): array
    {
        $title = $this->sanitizeText($rawArticle['title'] ?? '');
        $abstract = $this->sanitizeText($rawArticle['abstract'] ?? '');

        // Extract author from byline
        $author = $this->extractAuthor($rawArticle['byline'] ?? '');

        // Get main image from media
        $imageUrl = $this->extractPopularImageUrl($rawArticle['media'] ?? []);
        return [
            'title' => $title,
            'content' => $abstract, // Most Popular API provides abstract as content
            'summary' => $this->extractSummary($abstract),
            'url' => $rawArticle['url'] ?? '',
            'published_at' => Carbon::parse($rawArticle['published_date'])->format('Y-m-d H:i:s'),
            'author' => $author,
            'category' => $this->mapCategory($rawArticle['section'] ?? 'general'),
            'image_url' => $imageUrl,
        ];
    }

    private function extractAuthor(string $byline): string
    {
        // NYT bylines often start with "By " followed by author name
        $author = $this->sanitizeText($byline);

        // Remove "By " prefix if present
        if (strpos($author, 'By ') === 0) {
            $author = substr($author, 3);
        }

        // Clean up common patterns
        $author = preg_replace('/\s+and\s+/', ', ', $author);
        $author = trim($author);

        return $author ?: 'New York Times';
    }

    private function extractImageUrl(array $multimedia): ?string
    {
        if (empty($multimedia)) {
            return null;
        }

        // Find the best image (prefer larger images)
        $preferredTypes = ['xlarge', 'large', 'mediumThreeByTwo440', 'mediumThreeByTwo210'];

        foreach ($preferredTypes as $type) {
            foreach ($multimedia as $media) {
                if (isset($media['subtype']) && $media['subtype'] === $type) {
                    return 'https://static01.nyt.com/' . $media['url'];
                }
            }
        }

        // Fall back to first available image
        if (isset($multimedia[0]['url'])) {
            return 'https://static01.nyt.com/' . $multimedia[0]['url'];
        }

        return null;
    }

    private function extractPopularImageUrl(array $media): ?string
    {
        if (empty($media)) {
            return null;
        }

        // Most Popular API has different media structure
        foreach ($media as $mediaItem) {
            if (isset($mediaItem['media-metadata']) && is_array($mediaItem['media-metadata'])) {
                // Get the largest available image
                $mediaMetadata = $mediaItem['media-metadata'];
                $largestImage = end($mediaMetadata); // Last item is usually largest

                if (isset($largestImage['url'])) {
                    return $largestImage['url'];
                }
            }

        }
        return null;
    }


    private function sanitizeText(string $text): string
    {
        // Use static property to avoid recreating array each time
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace(array_keys(self::$characterReplacements), array_values(self::$characterReplacements), $text);
        $text = preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }


    private function extractSummary(string $content): string
    {
        $stripped = $this->sanitizeText($content);

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
            'Sports' => 'sports',
            'Health' => 'health',
            'Science' => 'science',
            'Politics' => 'politics',
            'World' => 'world',
            'U.S.' => 'us',
            'Opinion' => 'opinion',
            'Arts' => 'arts',
            'Style' => 'lifestyle',
            'Food' => 'food',
            'Travel' => 'travel',
            'Magazine' => 'magazine',
            'T Magazine' => 'magazine',
            'Real Estate' => 'real-estate',
            'Obituaries' => 'obituaries',
        ];

        return $mapping[$section] ?? strtolower(str_replace(' ', '-', $section));
    }
}
