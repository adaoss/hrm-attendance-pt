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
     * Get the leave type details from database
     */
    public function leaveTypeDetails()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type', 'code');
    }

    /**
     * Get maximum days allowed for leave type based on Portuguese law
     * Now fetches from database instead of hardcoded values
     */
    public static function getMaxDaysForType(string $type): ?int
    {
        $leaveType = LeaveType::where('code', $type)
            ->where('is_active', true)
            ->first();

        if (!$leaveType) {
            return null;
        }

        // Return max_days if set, otherwise days_entitled
        return $leaveType->max_days ?? $leaveType->days_entitled;
    }
}
