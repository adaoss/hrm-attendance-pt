<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'date',
        'hours',
        'rate_multiplier', // 1.5 for first 2 hours, 1.75 for additional hours
        'status', // pending, approved, rejected, paid
        'notes',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'rate_multiplier' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Calculate overtime rate based on Portuguese Labor Code
     * First 2 hours: 50% extra (1.5x)
     * Additional hours: 75% extra (1.75x)
     * Sundays and holidays: 100% extra (2x)
     */
    public static function calculateRateMultiplier(float $hours, bool $isWeekendOrHoliday = false): float
    {
        if ($isWeekendOrHoliday) {
            return 2.0; // 100% extra for weekends and holidays
        }

        // For regular weekdays
        if ($hours <= 2) {
            return 1.5; // 50% extra for first 2 hours
        }

        return 1.75; // 75% extra for additional hours
    }
}
