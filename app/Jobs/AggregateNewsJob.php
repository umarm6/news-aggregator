<?php

namespace App\Jobs;

use App\Models\Sources;
use App\Services\GuardianApiService;
use App\Services\NewsApiService;
use App\Services\NewYorkTimesApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AggregateNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected Sources $sources;

    public function __construct(Sources $sources)
    {
        $this->sources = $sources;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->sources->is_active) {
            return;
        }

        Log::info("Starting news aggregation for {$this->sources->name}");

        $service = $this->getServiceForSource($this->sources);

        if (!$service) {
            Log::error("No service found for source: {$this->sources->name}");
            return;
        }

        try {
            $articles = $service->fetchArticles();
            $stored = $service->storeArticles($articles);

            Log::info("News aggregation completed for {$this->sources->name}", [
                'fetched' => count($articles),
                'stored' => $stored
            ]);
        } catch (\Exception $e) {
            Log::error("News aggregation failed for {$this->sources->name}", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getServiceForSource(Sources $sources)
    {
        return match (strtolower($sources->name)) {
            'guardian' => new GuardianApiService($sources),
            'newsapi' => new NewsApiService($sources),
            'nytimes' => new NewYorkTimesApiService($sources),
            default => null,
        };
    }

}
