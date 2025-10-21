<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Portuguese Labor Law Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration based on Portuguese Labor Code (Código do Trabalho)
    |
    */

    // Working Hours (Article 203)
    'working_hours' => [
        'per_day' => env('WORKING_HOURS_PER_DAY', 8),
        'per_week' => env('WORKING_HOURS_PER_WEEK', 40),
    ],

    // Overtime Rates (Article 268)
    'overtime' => [
        'rate_first_hours' => env('OVERTIME_RATE_FIRST', 1.5), // 50% extra for first 2 hours
        'rate_additional' => env('OVERTIME_RATE_ADDITIONAL', 1.75), // 75% extra
        'rate_weekend_holiday' => 2.0, // 100% extra
        'first_hours_threshold' => 2, // First 2 hours at lower rate
    ],

    // Rest Periods (Article 214)
    'rest_periods' => [
        'min_daily_rest_hours' => env('MIN_REST_HOURS', 11),
        'min_weekly_rest_days' => 1,
        'preferred_weekly_rest_day' => 'sunday',
    ],

    // Vacation (Article 238)
    'vacation' => [
        'days_per_year' => env('VACATION_DAYS_PER_YEAR', 22),
        'proportional_first_year' => true,
    ],

    // Leave Types
    'leave' => [
        'maternity' => [
            'days' => 150,
            'min_days' => 120,
            'paid' => true,
        ],
        'paternity' => [
            'days' => 28,
            'paid' => true,
        ],
        'marriage' => [
            'days' => 15,
            'paid' => true,
        ],
        'bereavement' => [
            'days' => 5,
            'paid' => true,
        ],
    ],

    // Break Times
    'breaks' => [
        'required_after_hours' => 5, // Break required after 5 hours of work
        'min_break_duration' => 30, // minutes
    ],
];
