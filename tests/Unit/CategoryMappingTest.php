<?php

namespace Tests\Unit;

use App\Models\Sources;
use App\Services\GuardianApiService;
use Tests\TestCase;

class CategoryMappingTest extends TestCase
{
    public function test_category_map(): void
    {

        $this->seed();

        $source = Sources::where('name','=','Guardian')->first();
        $svc = new GuardianApiService($source);

        // Create reflection method
        $reflectionClass = new \ReflectionClass($svc);
        $mapCategoryMethod = $reflectionClass->getMethod('mapCategory');

        // Test the private method
        $this->assertSame('sports', $mapCategoryMethod->invoke($svc, 'Sport'));
        $this->assertSame('technology', $mapCategoryMethod->invoke($svc, 'Science'));
        $this->assertSame('business', $mapCategoryMethod->invoke($svc, 'Business'));
    }
}
