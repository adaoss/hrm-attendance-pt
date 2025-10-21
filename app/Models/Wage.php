<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wage extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'base_salary',
        'regular_hours_worked',
        'overtime_hours',
        'overtime_pay',
        'bonuses',
        'deductions',
        'gross_pay',
        'net_pay',
        'payment_date',
        'status',
        'breakdown',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'base_salary' => 'decimal:2',
        'regular_hours_worked' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonuses' => 'decimal:2',
        'deductions' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'payment_date' => 'date',
        'breakdown' => 'array',
    ];

    /**
     * Get the employee that owns the wage
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the month name
     */
    public function getMonthNameAttribute(): string
    {
        return date('F', mktime(0, 0, 0, $this->month, 1));
    }

    /**
     * Get the period string
     */
    public function getPeriodAttribute(): string
    {
        return "{$this->month_name} {$this->year}";
    }
}
