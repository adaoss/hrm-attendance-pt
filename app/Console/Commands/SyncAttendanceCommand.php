<?php

namespace App\Console\Commands;

use App\Services\ZKTecoService;
use Illuminate\Console\Command;

class SyncAttendanceCommand extends Command
{
    protected $signature = 'attendance:sync';
    protected $description = 'Synchronize attendance data from ZKTeco device';

    public function handle(ZKTecoService $zktecoService): int
    {
        $this->info('Starting attendance synchronization from ZKTeco device...');

        $results = $zktecoService->syncAttendance();

        $this->info("Synchronization completed:");
        $this->info("- Success: {$results['success']}");
        $this->info("- Failed: {$results['failed']}");

        if (!empty($results['errors'])) {
            $this->error("Errors:");
            foreach ($results['errors'] as $error) {
                $this->error("  - {$error}");
            }
        }

        return empty($results['errors']) ? self::SUCCESS : self::FAILURE;
    }
}
