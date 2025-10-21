<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'date',
        'clock_in',
        'clock_out',
        'break_start',
        'break_end',
        'total_hours',
        'regular_hours',
        'overtime_hours',
        'status', // present, absent, late, early_departure
        'notes',
        'zkteco_device_id',
        'synced_at',
    ];

    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'break_start' => 'datetime',
        'break_end' => 'datetime',
        'total_hours' => 'decimal:2',
        'regular_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'synced_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Calculate total working hours considering breaks
     * Portuguese law requires 11 consecutive hours of rest between working days
     */
    public function calculateHours(): void
    {
        if (!$this->clock_in || !$this->clock_out) {
            return;
        }

        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);
        
        // Calculate total time
        $totalMinutes = $clockOut->diffInMinutes($clockIn);
        
        // Subtract break time if recorded
        if ($this->break_start && $this->break_end) {
            $breakStart = Carbon::parse($this->break_start);
            $breakEnd = Carbon::parse($this->break_end);
            $totalMinutes -= $breakEnd->diffInMinutes($breakStart);
        }
        
        $this->total_hours = round($totalMinutes / 60, 2);
        
        // Calculate regular and overtime hours (8 hours per day as per Portuguese law)
        $regularLimit = 8;
        
        if ($this->total_hours <= $regularLimit) {
            $this->regular_hours = $this->total_hours;
            $this->overtime_hours = 0;
        } else {
            $this->regular_hours = $regularLimit;
            $this->overtime_hours = $this->total_hours - $regularLimit;
        }
    }

    /**
     * Check if there's adequate rest period (11 hours as per Portuguese law)
     */
    public function hasAdequateRestPeriod(): bool
    {
        if (!$this->clock_out) {
            return true;
        }

        $previousAttendance = static::where('employee_id', $this->employee_id)
            ->where('date', '<', $this->date)
            ->orderBy('date', 'desc')
            ->first();

        if (!$previousAttendance || !$previousAttendance->clock_out) {
            return true;
        }

        $previousClockOut = Carbon::parse($previousAttendance->clock_out);
        $currentClockIn = Carbon::parse($this->clock_in);

        // Must have at least 11 hours of rest
        return $currentClockIn->diffInHours($previousClockOut) >= 11;
    }
}
