<?php

namespace Database\Factories;

use App\Models\Articles;
use App\Models\Sources;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Articles>
 */
class ArticlesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Articles::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(3, true),
            'summary' => $this->faker->paragraph(),
            'url' => $this->faker->unique()->url(),
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'author' => $this->faker->name(),
            'category' => $this->faker->randomElement(['technology', 'business', 'sports', 'health']),
            'image_url' => $this->faker->optional()->imageUrl(),
            'source_id' => Sources::factory(),
        ];
    }
}
