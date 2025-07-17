<?php

namespace App\Console\Commands;

use App\Jobs\AggregateNewsJob;
use App\Models\Sources;
use Illuminate\Console\Command;

class AggregateNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:aggregate {--source=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aggregate news from all active sources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sources = Sources::where('is_active', true);

        if ($this->option('source')) {
            $sources->where('name', $this->option('source'));
        }

        $sources = $sources->get();

        if ($sources->isEmpty()) {
            $this->error('No active sources found');
            return;
        }

        $this->info("Starting news aggregation for {$sources->count()} source(s)");

        foreach ($sources as $source) {
            AggregateNewsJob::dispatch($source);
            $this->info("Dispatched job for {$source->name}");
        }

        $this->info('All jobs dispatched successfully');

    }
}
