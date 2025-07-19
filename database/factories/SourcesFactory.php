<?php

namespace Database\Factories;

use App\Models\Sources;
use Illuminate\Database\Eloquent\Factories\Factory;

class SourcesFactory extends Factory
{
    protected $model = Sources::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'api_endpoint' => $this->faker->url(),
            'api_key_required' => $this->faker->boolean(70), // 70% chance of requiring API key
            'rate_limit' => $this->faker->numberBetween(1000, 10000),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the source requires an API key.
     */
    public function requiresApiKey(): static
    {
        return $this->state(fn (array $attributes) => [
            'api_key_required' => true,
        ]);
    }

    /**
     * Indicate that the source is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create predefined news sources.
     */
    public function newsApi(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'NewsAPI',
            'api_endpoint' => 'https://newsapi.org/v2/',
            'api_key_required' => true,
            'rate_limit' => 1000,
            'is_active' => true,
        ]);
    }

    public function guardian(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Guardian',
            'api_endpoint' => 'https://content.guardianapis.com/',
            'api_key_required' => true,
            'rate_limit' => 12000,
            'is_active' => true,
        ]);
    }

    public function nyTimes(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'NYTimes',
            'api_endpoint' => 'https://api.nytimes.com/',
            'api_key_required' => true,
            'rate_limit' => 4000,
            'is_active' => true,
        ]);
    }
}
