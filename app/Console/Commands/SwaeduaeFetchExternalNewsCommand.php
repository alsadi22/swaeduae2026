<?php

namespace App\Console\Commands;

use App\Jobs\FetchExternalNewsSourceJob;
use App\Models\ExternalNewsFetchLog;
use App\Models\ExternalNewsSource;
use Illuminate\Console\Command;

class SwaeduaeFetchExternalNewsCommand extends Command
{
    protected $signature = 'swaeduae:fetch-external-news
                            {--source=* : Limit to source id or slug}
                            {--force : Ignore fetch interval and run for every matching source}';

    protected $description = 'Dispatch RSS/Atom fetch jobs for active external news sources.';

    public function handle(): int
    {
        $filters = $this->option('source');
        $force = (bool) $this->option('force');

        $query = ExternalNewsSource::query()
            ->where('is_active', true)
            ->whereIn('type', [ExternalNewsSource::TYPE_RSS, ExternalNewsSource::TYPE_ATOM])
            ->orderByDesc('priority')
            ->orderBy('name');

        if (is_array($filters) && $filters !== []) {
            $query->where(function ($q) use ($filters): void {
                foreach ($filters as $raw) {
                    $v = trim((string) $raw);
                    if ($v === '') {
                        continue;
                    }
                    if (ctype_digit($v)) {
                        $q->orWhere('id', (int) $v);
                    } else {
                        $q->orWhere('slug', $v);
                    }
                }
            });
        }

        $sources = $query->get();
        $dispatched = 0;

        foreach ($sources as $source) {
            if (! $force && ! $this->isDue($source)) {
                continue;
            }

            FetchExternalNewsSourceJob::dispatch($source->id);
            $dispatched++;
            $this->line("Queued fetch for source #{$source->id} ({$source->slug})");
        }

        if ($dispatched === 0) {
            $this->info('No sources due for fetch (use --force or --source).');
        }

        return self::SUCCESS;
    }

    private function isDue(ExternalNewsSource $source): bool
    {
        $last = ExternalNewsFetchLog::query()
            ->where('source_id', $source->id)
            ->where('status', ExternalNewsFetchLog::STATUS_SUCCESS)
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->first();

        if ($last === null) {
            return true;
        }

        $interval = max(5, (int) $source->fetch_interval_minutes);

        return $last->finished_at->copy()->addMinutes($interval)->isPast();
    }
}
