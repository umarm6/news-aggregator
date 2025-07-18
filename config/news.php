<?php

return [
    'sources' => [
        'newsapi' => [
            'api_key' => env('NEWSAPI_KEY'),
            'base_url' => 'https://newsapi.org/v2/',
            'rate_limit' => 1000,
        ],
        'guardian' => [
            'api_key' => env('GUARDIAN_API_KEY'),
            'base_url' => 'https://content.guardianapis.com/',
            'rate_limit' => 12000,
        ],

        'nytimes' => [
            'api_key' => env('NYTIMES_API_KEY'),
            'base_url' => 'https://api.nytimes.com/',
            'rate_limit' => 4000, // 4000 requests per day
        ],
    ],
];
