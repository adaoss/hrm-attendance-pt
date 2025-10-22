<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    use HasFactory, BelongsToTeam;

    protected $fillable = [
        'name',
        'description',
        'monday_start',
        'monday_end',
        'tuesday_start',
        'tuesday_end',
        'wednesday_start',
        'wednesday_end',
        'thursday_start',
        'thursday_end',
        'friday_start',
        'friday_end',
        'saturday_start',
        'saturday_end',
        'sunday_start',
        'sunday_end',
        'break_duration', // in minutes
        'weekly_hours',
        'is_active',
        'team_id',
    ];

    protected $casts = [
        'break_duration' => 'integer',
        'weekly_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get work hours for a specific day
     */
    public function getHoursForDay(string $day): ?array
    {
        $startKey = strtolower($day) . '_start';
        $endKey = strtolower($day) . '_end';

        if (!$this->$startKey || !$this->$endKey) {
            return null;
        }

        return [
            'start' => $this->$startKey,
            'end' => $this->$endKey,
        ];
    }

    /**
     * Validate that weekly hours don't exceed 40 hours (Portuguese law)
     */
    public function validateWeeklyHours(): bool
    {
        return $this->weekly_hours <= 40;
    }
}
