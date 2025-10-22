<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'date',
        'is_national',
        'is_recurring',
        'description',
        'team_id',
    ];

    protected $casts = [
        'date' => 'date',
        'is_national' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Portuguese national holidays as per law
     */
    public static function getPortugueseNationalHolidays(int $year): array
    {
        return [
            ['name' => 'Ano Novo', 'date' => "$year-01-01", 'is_national' => true],
            ['name' => 'Sexta-feira Santa', 'date' => self::getEasterDate($year, -2), 'is_national' => true],
            ['name' => 'Páscoa', 'date' => self::getEasterDate($year), 'is_national' => true],
            ['name' => 'Dia da Liberdade', 'date' => "$year-04-25", 'is_national' => true],
            ['name' => 'Dia do Trabalhador', 'date' => "$year-05-01", 'is_national' => true],
            ['name' => 'Corpo de Deus', 'date' => self::getEasterDate($year, 60), 'is_national' => true],
            ['name' => 'Dia de Portugal', 'date' => "$year-06-10", 'is_national' => true],
            ['name' => 'Assunção de Nossa Senhora', 'date' => "$year-08-15", 'is_national' => true],
            ['name' => 'Implantação da República', 'date' => "$year-10-05", 'is_national' => true],
            ['name' => 'Dia de Todos os Santos', 'date' => "$year-11-01", 'is_national' => true],
            ['name' => 'Restauração da Independência', 'date' => "$year-12-01", 'is_national' => true],
            ['name' => 'Imaculada Conceição', 'date' => "$year-12-08", 'is_national' => true],
            ['name' => 'Natal', 'date' => "$year-12-25", 'is_national' => true],
        ];
    }

    /**
     * Calculate Easter date for a given year
     */
    private static function getEasterDate(int $year, int $offset = 0): string
    {
        $easter = easter_date($year);
        if ($offset !== 0) {
            $easter = strtotime("+$offset days", $easter);
        }
        return date('Y-m-d', $easter);
    }

    /**
     * Check if a given date is a holiday
     */
    public static function isHoliday(string $date): bool
    {
        return static::where('date', $date)->exists();
    }
}
