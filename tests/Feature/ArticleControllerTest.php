<?php

namespace Tests\Feature;

use App\Models\Articles;
use App\Models\Sources;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ArticleControllerTest extends TestCase
{


    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        Cache::flush();

        // Create test source
        $this->source = Sources::factory()->create([
            'name' => 'Guardian',
            'is_active' => true
        ]);
    }

    public function test_articles_index_returns_paginated_response()
    {
        // Arrange
        Articles::factory()
            ->count(25)
            ->create(['source_id' => $this->source->id]);

        // Act
        $response = $this->getJson('/api/v1/articles');

        // Assert
        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'summary',
                        'url',
                        'published_at',
                        'author',
                        'category',
                        'source' => ['name', 'id']
                    ]
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page'
                ]
            ])
            ->assertJson(['success' => true])
            ->assertJsonCount(25, 'data'); // Default pagination
    }

    public function test_articles_can_be_filtered_by_source()
    {
        // Arrange
        Articles::factory()->create([
            'source_id' => $this->source->id,
            'category' => 'technology',
            'title' => 'Tech Article'
        ]);


        // Act
        $response = $this->getJson('/api/v1/articles?source=Guardian');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'source' => [
                    'name' => 'Guardian',
                    'id' => $this->source->id
                ]
            ]);
    }
    public function test_articles_can_be_filtered_by_category()
    {
        // Arrange
        Articles::factory()->create([
            'source_id' => $this->source->id,
            'category' => 'technology',
            'title' => 'Tech Article'
        ]);

        Articles::factory()->create([
            'source_id' => $this->source->id,
            'category' => 'business',
            'title' => 'Business Article'
        ]);

        // Act
        $response = $this->getJson('/api/v1/articles?category=technology');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['category' => 'technology']);
    }

    public function test_articles_can_be_searched()
    {
        // Arrange
        Articles::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Laravel Framework News',
            'content' => 'This is about Laravel development'
        ]);

        Articles::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'React Component Updates',
            'content' => 'This is about React components'
        ]);

        // Act
        $response = $this->getJson('/api/v1/articles?q=Laravel');

        // Assert
        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Laravel Framework News']);
    }

    public function test_single_article_can_be_retrieved()
    {
        // Arrange
        $article = Articles::factory()->create([
            'source_id' => $this->source->id,
            'title' => 'Test Article'
        ]);

        // Act
        $response = $this->getJson("/api/v1/articles/{$article->id}");

        // Assert
        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $article->id,
                    'title' => 'Test Article'
                ]
            ]);
    }

}
