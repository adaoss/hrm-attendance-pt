<?php

namespace App\Filament\Resources\AttendanceResource\Pages;

use App\Filament\Resources\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate hours before creating
        if ($data['clock_in'] && $data['clock_out']) {
            $clockIn = \Carbon\Carbon::parse($data['clock_in']);
            $clockOut = \Carbon\Carbon::parse($data['clock_out']);
            
            $totalMinutes = $clockOut->diffInMinutes($clockIn);
            
            if (!empty($data['break_start']) && !empty($data['break_end'])) {
                $breakStart = \Carbon\Carbon::parse($data['break_start']);
                $breakEnd = \Carbon\Carbon::parse($data['break_end']);
                $totalMinutes -= $breakEnd->diffInMinutes($breakStart);
            }
            
            $data['total_hours'] = round($totalMinutes / 60, 2);
            $data['regular_hours'] = min($data['total_hours'], 8);
            $data['overtime_hours'] = max(0, $data['total_hours'] - 8);
        }
        
        return $data;
    }
}
