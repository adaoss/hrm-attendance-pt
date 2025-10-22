<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes, BelongsToTeam;

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'hire_date',
        'department_id',
        'position',
        'work_schedule_id',
        'zkteco_user_id',
        'nif', // Portuguese tax identification number
        'niss', // Portuguese social security number
        'contract_type', // permanent, fixed-term, temporary
        'weekly_hours',
        'is_active',
        'team_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'weekly_hours' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    public function overtimes()
    {
        return $this->hasMany(Overtime::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Calculate vacation days entitled based on Portuguese law
     * 22 working days per year as per Portuguese Labor Code
     */
    public function getVacationDaysEntitled(int $year): int
    {
        $hireYear = $this->hire_date->year;
        
        // First year - proportional vacation days
        if ($year == $hireYear) {
            $monthsWorked = 12 - $this->hire_date->month + 1;
            return (int) floor(($monthsWorked / 12) * 22);
        }
        
        return 22; // Full 22 days for subsequent years
    }
}
