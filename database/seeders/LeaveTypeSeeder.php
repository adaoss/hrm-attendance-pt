<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'code' => 'vacation',
                'name' => 'Férias',
                'description' => 'Annual vacation - 22 working days per year (Article 238 of Portuguese Labor Code)',
                'days_entitled' => 22,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 238',
                    'proportional_first_year' => true,
                ],
            ],
            [
                'code' => 'maternity',
                'name' => 'Licença de Maternidade',
                'description' => 'Maternity leave - 120 to 150 days (Article 40 of Portuguese Labor Code)',
                'days_entitled' => 150,
                'min_days' => 120,
                'max_days' => 150,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 40',
                    'mandatory_period' => 6, // weeks before/after birth
                ],
            ],
            [
                'code' => 'paternity',
                'name' => 'Licença de Paternidade',
                'description' => 'Paternity leave - 28 days (Article 43 of Portuguese Labor Code)',
                'days_entitled' => 28,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 43',
                    'mandatory_days' => 7, // First 7 days must be taken
                ],
            ],
            [
                'code' => 'parental',
                'name' => 'Licença Parental',
                'description' => 'Parental leave - Shared leave for child care',
                'days_entitled' => null,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Articles 41-44',
                    'shared' => true,
                ],
            ],
            [
                'code' => 'marriage',
                'name' => 'Casamento',
                'description' => 'Marriage leave - 15 consecutive days (Article 252 of Portuguese Labor Code)',
                'days_entitled' => 15,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 252',
                    'consecutive' => true,
                ],
            ],
            [
                'code' => 'bereavement',
                'name' => 'Luto',
                'description' => 'Bereavement leave - 5 consecutive days for immediate family (Article 252 of Portuguese Labor Code)',
                'days_entitled' => 5,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => true,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 252',
                    'consecutive' => true,
                    'immediate_family_only' => true,
                ],
            ],
            [
                'code' => 'sick',
                'name' => 'Baixa Médica',
                'description' => 'Sick leave - Medical certificate required',
                'days_entitled' => null,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => true,
                'requires_approval' => false, // Medical certificate serves as approval
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 254',
                    'requires_medical_certificate' => true,
                ],
            ],
            [
                'code' => 'unpaid',
                'name' => 'Sem Vencimento',
                'description' => 'Unpaid leave - By mutual agreement',
                'days_entitled' => null,
                'min_days' => null,
                'max_days' => null,
                'is_paid' => false,
                'requires_approval' => true,
                'is_active' => true,
                'metadata' => [
                    'legal_reference' => 'Article 320',
                    'mutual_agreement_required' => true,
                ],
            ],
        ];

        foreach ($leaveTypes as $leaveType) {
            LeaveType::updateOrCreate(
                ['code' => $leaveType['code']],
                $leaveType
            );
        }
    }
}
