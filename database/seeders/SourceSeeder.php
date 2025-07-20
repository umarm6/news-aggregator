<?php

namespace Database\Seeders;

use App\Models\Sources;
use Illuminate\Database\Seeder;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        Sources::updateOrCreate([
            'name' => 'NewsAPI',
            'api_endpoint' => 'https://newsapi.org/v2/',
            'api_key_required' => true,
            'rate_limit' => 1000,
            'is_active' => true,
        ]);

        Sources::updateOrCreate([
            'name' => 'Guardian',
            'api_endpoint' => 'https://content.guardianapis.com/',
            'api_key_required' => true,
            'rate_limit' => 12000,
            'is_active' => true,
        ]);

        Sources::updateOrCreate([
            'name' => 'NYTimes',
            'api_endpoint' => 'https://api.nytimes.com/',
            'api_key_required' => true,
            'rate_limit' => 4000,
            'is_active' => true,
        ]);


    }
}
