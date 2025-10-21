# Portuguese Labor Law Implementation Guide

## Overview

This document details how the HRM Attendance PT system implements Portuguese labor laws according to the Código do Trabalho (Portuguese Labor Code).

## Working Hours (Artigo 203 - Período Normal de Trabalho)

### Daily Working Hours
- **Maximum**: 8 hours per day
- **Weekly Total**: 40 hours per week (5 days × 8 hours)

### Implementation
```php
// In config/portuguese-labor.php
'working_hours' => [
    'per_day' => 8,
    'per_week' => 40,
]

// In Attendance model
public function calculateHours(): void
{
    $regularLimit = 8;
    if ($this->total_hours <= $regularLimit) {
        $this->regular_hours = $this->total_hours;
        $this->overtime_hours = 0;
    } else {
        $this->regular_hours = $regularLimit;
        $this->overtime_hours = $this->total_hours - $regularLimit;
    }
}
```

## Rest Periods (Artigo 214 - Descanso Diário)

### Daily Rest
- **Minimum**: 11 consecutive hours between working days
- **Purpose**: Ensure employee health and work-life balance

### Implementation
```php
// In Attendance model
public function hasAdequateRestPeriod(): bool
{
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
```

### Violations
The system automatically detects and logs violations of the 11-hour rest period.

## Weekly Rest (Artigo 232 - Descanso Semanal)

### Requirements
- **Minimum**: 1 day of rest per week
- **Preferred**: Sunday (but can be another day)
- **Additional**: Plus the daily rest of 11 hours

### Implementation
Work schedules are configured with rest days (typically Saturday and Sunday).

## Overtime Compensation (Artigo 268 - Trabalho Suplementar)

### Overtime Rates

#### Weekdays
1. **First 2 hours**: 50% extra compensation (1.5× base rate)
2. **Additional hours**: 75% extra compensation (1.75× base rate)

#### Weekends and Holidays
- **All hours**: 100% extra compensation (2.0× base rate)

### Implementation
```php
// In PortugueseLaborLawService
public function calculateOvertimeRate(float $overtimeHours, bool $isWeekendOrHoliday = false): array
{
    if ($isWeekendOrHoliday) {
        return [
            'hours' => $overtimeHours,
            'rate' => 2.0,
            'description' => 'Weekend/Holiday rate (100% extra)'
        ];
    }

    if ($overtimeHours <= 2) {
        return [
            'hours' => $overtimeHours,
            'rate' => 1.5,
            'description' => 'First 2 hours (50% extra)'
        ];
    } else {
        return [
            'hours_first_2' => 2,
            'rate_first_2' => 1.5,
            'hours_additional' => $overtimeHours - 2,
            'rate_additional' => 1.75,
            'description' => 'Mixed rate'
        ];
    }
}
```

### Example Calculation
Employee works 11 hours on a weekday:
- Regular hours: 8 hours at 1.0× rate
- First 2 overtime hours: 2 hours at 1.5× rate
- Additional overtime: 1 hour at 1.75× rate

## Vacation Rights (Artigo 238 - Férias)

### Annual Vacation
- **Duration**: 22 working days per year
- **Proportional**: In the first year of employment
- **Paid**: Full salary during vacation

### First Year Calculation
```
vacation_days = (months_worked / 12) × 22
```

### Implementation
```php
// In Employee model
public function getVacationDaysEntitled(int $year): int
{
    $hireYear = $this->hire_date->year;
    
    if ($year == $hireYear) {
        $monthsWorked = 12 - $this->hire_date->month + 1;
        return (int) floor(($monthsWorked / 12) * 22);
    }
    
    return 22;
}

// In PortugueseLaborLawService
public function getVacationBalance(Employee $employee, int $year): array
{
    $entitlement = $this->calculateVacationEntitlement($employee, $year);
    
    $usedDays = Leave::where('employee_id', $employee->id)
        ->where('leave_type', Leave::TYPE_VACATION)
        ->where('status', 'approved')
        ->whereYear('start_date', $year)
        ->sum('days_requested');

    return [
        'year' => $year,
        'entitled_days' => $entitlement['entitled_days'],
        'used_days' => $usedDays,
        'remaining_days' => max(0, $entitlement['entitled_days'] - $usedDays),
    ];
}
```

## Special Leaves (Artigo 252 - Faltas Justificadas)

### Leave Types and Durations

#### Marriage Leave (Casamento)
- **Duration**: 15 consecutive days
- **Payment**: Fully paid
- **Timing**: Must be taken consecutively around the marriage date

#### Bereavement Leave (Falecimento)
- **Duration**: 5 consecutive days
- **Payment**: Fully paid
- **For**: Death of spouse, parents, children, or close family

#### Sick Leave (Baixa Médica)
- **Duration**: As prescribed by doctor
- **Payment**: Varies by duration (paid by employer for first days, then social security)
- **Requirements**: Medical certificate required

### Implementation
```php
// In Leave model
const TYPE_MARRIAGE = 'marriage'; // 15 days
const TYPE_BEREAVEMENT = 'bereavement'; // 5 days
const TYPE_SICK = 'sick'; // Variable

public static function getMaxDaysForType(string $type): ?int
{
    return match($type) {
        self::TYPE_MARRIAGE => 15,
        self::TYPE_BEREAVEMENT => 5,
        default => null,
    };
}
```

## Parental Leave

### Maternity Leave (Artigo 40 - Licença Parental Inicial)
- **Duration**: 120 to 150 days
- **Minimum**: 120 days (with full payment)
- **Optional Extension**: Up to 150 days (with reduced payment)
- **Mandatory**: 6 weeks must be taken after birth
- **Payment**: 100% of salary for 120 days, 80% for 150 days

### Paternity Leave (Artigo 43 - Licença Parental do Pai)
- **Duration**: 28 days
- **Mandatory**: 7 working days (first must be at birth)
- **Optional**: Additional 21 days
- **Payment**: Fully paid

### Implementation
```php
// In Leave model
const TYPE_MATERNITY = 'maternity'; // 120-150 days
const TYPE_PATERNITY = 'paternity'; // 28 days

// In PortugueseLaborLawService
public function getLeaveEntitlement(string $leaveType): ?array
{
    return match($leaveType) {
        Leave::TYPE_MATERNITY => [
            'days' => 150,
            'min_days' => 120,
            'description' => 'Maternity leave (120-150 days, Article 40)',
            'paid' => true
        ],
        Leave::TYPE_PATERNITY => [
            'days' => 28,
            'description' => 'Paternity leave (28 days, Article 43)',
            'paid' => true
        ],
    ];
}
```

## Portuguese National Holidays

### Fixed Holidays
1. **Ano Novo** (New Year's Day) - January 1
2. **Dia da Liberdade** (Freedom Day) - April 25
3. **Dia do Trabalhador** (Labour Day) - May 1
4. **Dia de Portugal** (Portugal Day) - June 10
5. **Assunção de Nossa Senhora** (Assumption of Mary) - August 15
6. **Implantação da República** (Republic Day) - October 5
7. **Dia de Todos os Santos** (All Saints' Day) - November 1
8. **Restauração da Independência** (Restoration of Independence) - December 1
9. **Imaculada Conceição** (Immaculate Conception) - December 8
10. **Natal** (Christmas) - December 25

### Movable Holidays (Based on Easter)
11. **Sexta-feira Santa** (Good Friday) - 2 days before Easter
12. **Páscoa** (Easter Sunday)
13. **Corpo de Deus** (Corpus Christi) - 60 days after Easter

### Implementation
```php
// In Holiday model
public static function getPortugueseNationalHolidays(int $year): array
{
    return [
        ['name' => 'Ano Novo', 'date' => "$year-01-01", 'is_national' => true],
        ['name' => 'Sexta-feira Santa', 'date' => self::getEasterDate($year, -2), 'is_national' => true],
        ['name' => 'Páscoa', 'date' => self::getEasterDate($year), 'is_national' => true],
        // ... other holidays
    ];
}

private static function getEasterDate(int $year, int $offset = 0): string
{
    $easter = easter_date($year);
    if ($offset !== 0) {
        $easter = strtotime("+$offset days", $easter);
    }
    return date('Y-m-d', $easter);
}
```

## Contract Types

### Permanent Contract (Contrato Sem Termo)
- **Duration**: Indefinite
- **Most Common**: Standard employment contract
- **Termination**: Requires specific grounds and procedures

### Fixed-Term Contract (Contrato a Termo)
- **Duration**: Limited period
- **Maximum**: 2 years (can be renewed up to 3 times)
- **Requirements**: Must have valid justification

### Temporary Contract (Contrato Temporário)
- **Duration**: Specific task or temporary replacement
- **Through**: Temporary work agency
- **Limitations**: Various restrictions apply

### Implementation
```php
// In Employee model
protected $fillable = [
    'contract_type', // permanent, fixed_term, temporary
    // ... other fields
];

// In migration
$table->enum('contract_type', ['permanent', 'fixed_term', 'temporary'])
    ->default('permanent');
```

## Compliance Monitoring

### Automatic Violations Detection

The system automatically monitors and alerts for:

1. **Insufficient Rest Period**
   - Alert when less than 11 hours between shifts

2. **Excessive Working Hours**
   - Warning when exceeding 8 hours/day
   - Warning when exceeding 40 hours/week

3. **Missing Break Times**
   - Alert when break not recorded after 5 hours of work

4. **Vacation Balance**
   - Track remaining vacation days
   - Alert when balance is low or expiring

### Implementation
```php
// In PortugueseLaborLawService
public function validateWorkingHours(Employee $employee, Carbon $weekStart): array
{
    $violations = [];

    if ($regularHours > 40) {
        $violations[] = [
            'type' => 'weekly_hours_exceeded',
            'message' => "Regular weekly hours exceed legal limit of 40h",
            'severity' => 'high'
        ];
    }

    if (!$attendance->hasAdequateRestPeriod()) {
        $violations[] = [
            'type' => 'insufficient_rest',
            'message' => 'Less than 11 hours rest between shifts',
            'severity' => 'critical'
        ];
    }

    return [
        'is_compliant' => empty($violations),
        'violations' => $violations
    ];
}
```

## Working Days Calculation

### Excluding Non-Working Days
When calculating leave days, the system excludes:
- Weekends (Saturday and Sunday)
- National holidays
- Company holidays (if configured)

### Implementation
```php
// In PortugueseLaborLawService
public function isWorkingDay(Carbon $date): bool
{
    if ($date->isWeekend()) {
        return false;
    }
    
    return !Holiday::isHoliday($date->toDateString());
}

public function calculateWorkingDays(Carbon $startDate, Carbon $endDate): int
{
    $workingDays = 0;
    $current = $startDate->copy();

    while ($current->lte($endDate)) {
        if ($this->isWorkingDay($current)) {
            $workingDays++;
        }
        $current->addDay();
    }

    return $workingDays;
}
```

## Employee Identification

### Portuguese Identification Numbers

#### NIF (Número de Identificação Fiscal)
- **Format**: 9 digits
- **Purpose**: Tax identification
- **Required**: For all employees

#### NISS (Número de Identificação de Segurança Social)
- **Format**: 11 digits
- **Purpose**: Social security identification
- **Required**: For all employees

### Implementation
```php
// In Employee migration
$table->string('nif', 9)->nullable()->comment('Portuguese Tax ID');
$table->string('niss', 11)->nullable()->comment('Portuguese Social Security Number');

// In Employee resource form
Forms\Components\TextInput::make('nif')
    ->label('NIF (Tax ID)')
    ->mask('999999999')
    ->length(9),
Forms\Components\TextInput::make('niss')
    ->label('NISS (Social Security)')
    ->mask('99999999999')
    ->length(11),
```

## References

- **Código do Trabalho** - Portuguese Labor Code (Lei n.º 7/2009)
- **Article 203** - Normal working period
- **Article 214** - Daily rest
- **Article 232** - Weekly rest
- **Article 238** - Vacation duration
- **Article 252** - Justified absences
- **Article 268** - Overtime work
- **Article 40** - Initial parental leave (maternity)
- **Article 43** - Father's parental leave (paternity)

## Legal Disclaimer

This implementation is based on the Portuguese Labor Code as of 2024. Labor laws may change, and specific situations may require legal consultation. This system provides tools to help comply with Portuguese labor laws, but users should consult with legal professionals to ensure full compliance with current regulations.
