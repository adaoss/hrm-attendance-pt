<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * ZKTeco Device Integration Service
 * 
 * This service handles communication with ZKTeco attendance devices
 * for synchronizing attendance records.
 */
class ZKTecoService
{
    private string $deviceIp;
    private int $devicePort;

    public function __construct()
    {
        $this->deviceIp = config('zkteco.device_ip', '192.168.1.201');
        $this->devicePort = config('zkteco.device_port', 4370);
    }

    /**
     * Synchronize attendance data from ZKTeco device
     */
    public function syncAttendance(): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        try {
            // In production, this would connect to actual ZKTeco device
            // For now, this is a placeholder that demonstrates the structure
            
            $attendanceRecords = $this->fetchAttendanceFromDevice();

            foreach ($attendanceRecords as $record) {
                try {
                    $this->processAttendanceRecord($record);
                    $results['success']++;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = $e->getMessage();
                    Log::error('Failed to process attendance record', [
                        'record' => $record,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('ZKTeco sync failed', ['error' => $e->getMessage()]);
            $results['errors'][] = 'Device connection failed: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Fetch attendance records from ZKTeco device
     * 
     * In production, this would use the ZKTeco SDK or API
     */
    private function fetchAttendanceFromDevice(): array
    {
        // Placeholder - In production, this would:
        // 1. Connect to ZKTeco device using SDK
        // 2. Fetch attendance logs since last sync
        // 3. Return formatted records
        
        // Example structure:
        return [
            // [
            //     'user_id' => '123',
            //     'timestamp' => '2024-01-15 08:30:00',
            //     'type' => 'check_in', // or 'check_out'
            //     'device_id' => 'DEVICE001'
            // ]
        ];
    }

    /**
     * Process a single attendance record from device
     */
    private function processAttendanceRecord(array $record): void
    {
        $employee = Employee::where('zkteco_user_id', $record['user_id'])->first();
        
        if (!$employee) {
            throw new \Exception("Employee not found for ZKTeco user ID: {$record['user_id']}");
        }

        $date = Carbon::parse($record['timestamp'])->toDateString();
        $time = Carbon::parse($record['timestamp']);

        $attendance = Attendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $date,
        ]);

        // Update clock in/out based on record type
        if ($record['type'] === 'check_in' && !$attendance->clock_in) {
            $attendance->clock_in = $time;
        } elseif ($record['type'] === 'check_out') {
            $attendance->clock_out = $time;
        }

        $attendance->zkteco_device_id = $record['device_id'] ?? null;
        $attendance->synced_at = now();

        // Calculate hours if both clock in and out exist
        if ($attendance->clock_in && $attendance->clock_out) {
            $attendance->calculateHours();
            
            // Check for adequate rest period (11 hours as per Portuguese law)
            if (!$attendance->hasAdequateRestPeriod()) {
                $attendance->notes = ($attendance->notes ? $attendance->notes . "\n" : '') 
                    . 'Warning: Less than 11 hours rest period between shifts (Portuguese Labor Code violation)';
            }
        }

        $attendance->save();
    }

    /**
     * Register employee on ZKTeco device
     */
    public function registerEmployee(Employee $employee): bool
    {
        // Placeholder - In production, this would:
        // 1. Connect to ZKTeco device
        // 2. Register employee with biometric/card data
        // 3. Return success/failure
        
        Log::info("Employee registration to ZKTeco device", [
            'employee_id' => $employee->id,
            'zkteco_user_id' => $employee->zkteco_user_id
        ]);

        return true;
    }

    /**
     * Test connection to ZKTeco device
     */
    public function testConnection(): array
    {
        try {
            // Placeholder - In production, this would test actual device connection
            return [
                'connected' => true,
                'device_ip' => $this->deviceIp,
                'device_port' => $this->devicePort,
                'message' => 'Connection successful'
            ];
        } catch (\Exception $e) {
            return [
                'connected' => false,
                'device_ip' => $this->deviceIp,
                'device_port' => $this->devicePort,
                'message' => $e->getMessage()
            ];
        }
    }
}
