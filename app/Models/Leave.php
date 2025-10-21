<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type',
        'start_date',
        'end_date',
        'days_requested',
        'status', // pending, approved, rejected
        'reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'days_requested' => 'integer',
        'approved_at' => 'datetime',
    ];

    // Portuguese leave types as per Labor Code
    const TYPE_VACATION = 'vacation'; // Férias (22 days/year)
    const TYPE_SICK = 'sick'; // Baixa médica
    const TYPE_MATERNITY = 'maternity'; // Licença de maternidade (120-150 days)
    const TYPE_PATERNITY = 'paternity'; // Licença de paternidade (28 days)
    const TYPE_PARENTAL = 'parental'; // Licença parental
    const TYPE_MARRIAGE = 'marriage'; // Casamento (15 days)
    const TYPE_BEREAVEMENT = 'bereavement'; // Luto (5 days)
    const TYPE_UNPAID = 'unpaid'; // Licença sem vencimento

    public static function getLeaveTypes(): array
    {
        return [
            self::TYPE_VACATION => 'Férias',
            self::TYPE_SICK => 'Baixa Médica',
            self::TYPE_MATERNITY => 'Licença de Maternidade',
            self::TYPE_PATERNITY => 'Licença de Paternidade',
            self::TYPE_PARENTAL => 'Licença Parental',
            self::TYPE_MARRIAGE => 'Casamento',
            self::TYPE_BEREAVEMENT => 'Luto',
            self::TYPE_UNPAID => 'Sem Vencimento',
        ];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    /**
     * Get maximum days allowed for leave type based on Portuguese law
     */
    public static function getMaxDaysForType(string $type): ?int
    {
        return match($type) {
            self::TYPE_VACATION => 22,
            self::TYPE_MATERNITY => 150,
            self::TYPE_PATERNITY => 28,
            self::TYPE_MARRIAGE => 15,
            self::TYPE_BEREAVEMENT => 5,
            default => null,
        };
    }
}
