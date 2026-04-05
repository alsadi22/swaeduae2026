<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Support\AttendanceCheckpointUrl;
use Illuminate\Console\Command;

class PrintAttendanceCheckpointLinkCommand extends Command
{
    protected $signature = 'swaeduae:attendance-link {event_uuid : The event UUID (from events.uuid, shown on Admin → Events → Edit)} {--days=7 : Number of days the signed URL stays valid (min 1)}';

    protected $description = 'Print a temporary signed URL for the attendance checkpoint (encode in a QR code or share as a link; volunteers must be logged in)';

    public function handle(): int
    {
        $event = Event::query()->where('uuid', $this->argument('event_uuid'))->firstOrFail();

        $days = max(1, (int) $this->option('days'));

        $this->line(AttendanceCheckpointUrl::temporarySignedShowUrl($event, $days));

        return self::SUCCESS;
    }
}
