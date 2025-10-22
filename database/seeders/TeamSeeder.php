<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a default team
        Team::create([
            'name' => 'Default Company',
            'slug' => 'default-company',
            'description' => 'Default team for the HRM system',
            'is_active' => true,
            'settings' => [
                'timezone' => 'Europe/Lisbon',
                'working_hours_per_day' => 8,
                'working_hours_per_week' => 40,
            ],
        ]);
    }
}
