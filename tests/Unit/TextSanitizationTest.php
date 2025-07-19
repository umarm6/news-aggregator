<?php

namespace Tests\Unit;

use App\Models\Sources;
use App\Services\GuardianApiService;
use Tests\TestCase;

class TextSanitizationTest extends TestCase
{
    private GuardianApiService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $source = Sources::where('name', 'Guardian')->first();
        $this->service = new GuardianApiService($source);
    }


    public function test_extract_summary_truncates_correctly()
    {
        $reflection = new \ReflectionMethod($this->service, 'extractSummary');

        $longContent = str_repeat('This is a long text. ', 20);
        $result = $reflection->invoke($this->service, $longContent);

        $this->assertLessThanOrEqual(203, mb_strlen($result, 'UTF-8')); // 200 + "..."
        $this->assertStringEndsWith('...', $result);
    }

}
