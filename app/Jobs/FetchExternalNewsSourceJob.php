<?php

namespace App\Jobs;

use App\Models\ExternalNewsSource;
use App\Services\ExternalNews\ExternalNewsRssIngestionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchExternalNewsSourceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $sourceId) {}

    public function handle(ExternalNewsRssIngestionService $ingestion): void
    {
        $source = ExternalNewsSource::query()->find($this->sourceId);
        if ($source === null || ! $source->is_active) {
            return;
        }

        if (! $source->supportsRssOrAtom()) {
            return;
        }

        $ingestion->ingestFromSource($source);
    }
}
