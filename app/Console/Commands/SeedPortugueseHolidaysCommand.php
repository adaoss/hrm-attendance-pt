<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use Illuminate\Console\Command;

class SeedPortugueseHolidaysCommand extends Command
{
    protected $signature = 'holidays:seed {year?}';
    protected $description = 'Seed Portuguese national holidays for a given year';

    public function handle(): int
    {
        $year = $this->argument('year') ?? date('Y');

        $this->info("Seeding Portuguese national holidays for year {$year}...");

        $holidays = Holiday::getPortugueseNationalHolidays($year);

        foreach ($holidays as $holidayData) {
            Holiday::updateOrCreate(
                ['date' => $holidayData['date']],
                [
                    'name' => $holidayData['name'],
                    'is_national' => $holidayData['is_national'],
                    'is_recurring' => false,
                ]
            );

            $this->info("  - {$holidayData['name']}: {$holidayData['date']}");
        }

        $this->info("Successfully seeded " . count($holidays) . " holidays.");

        return self::SUCCESS;
    }
}
