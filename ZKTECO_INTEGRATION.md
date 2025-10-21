# ZKTeco Device Integration Guide

## Overview

This guide explains how to integrate ZKTeco biometric attendance devices with the HRM Attendance PT system for automatic attendance tracking.

## Supported Devices

The system is designed to work with ZKTeco devices that support:
- TCP/IP communication
- Standard attendance data format
- User management

Common compatible models:
- ZKTeco K40
- ZKTeco F18
- ZKTeco MB360
- ZKTeco MA300
- ZKTeco iClock series
- Other ZKTeco devices with SDK/API support

## Configuration

### 1. Device Network Setup

Configure your ZKTeco device with a static IP address on your network:

```
Device IP: 192.168.1.201
Port: 4370 (default)
```

### 2. Application Configuration

Edit your `.env` file:

```env
ZKTECO_DEVICE_IP=192.168.1.201
ZKTECO_DEVICE_PORT=4370
ZKTECO_SYNC_INTERVAL=5
```

Configuration options:
- `ZKTECO_DEVICE_IP`: IP address of your ZKTeco device
- `ZKTECO_DEVICE_PORT`: Communication port (usually 4370)
- `ZKTECO_SYNC_INTERVAL`: How often to sync in minutes

### 3. Advanced Configuration

Edit `config/zkteco.php` for advanced settings:

```php
return [
    'device_ip' => env('ZKTECO_DEVICE_IP', '192.168.1.201'),
    'device_port' => env('ZKTECO_DEVICE_PORT', 4370),
    'sync_interval' => env('ZKTECO_SYNC_INTERVAL', 5),
    
    'timeout' => 10, // Connection timeout in seconds
    'retry_attempts' => 3, // Number of retry attempts
    'retry_delay' => 5, // Delay between retries
    
    'auto_sync' => true, // Enable automatic synchronization
    'sync_on_startup' => false, // Sync when application starts
    'keep_device_records' => true, // Keep records on device after sync
];
```

## Employee Registration

### 1. Register Employee in System

Create an employee record in the admin panel with all required information.

### 2. Assign ZKTeco User ID

When creating/editing an employee:
1. Go to **Admin Panel** → **Employees** → **Edit Employee**
2. Scroll to **ZKTeco Integration** section
3. Enter the **ZKTeco User ID** (must match the ID on the device)

### 3. Register Employee on Device

#### Option A: Using Device Interface
1. Access the device admin menu
2. Navigate to User Management
3. Add new user with:
   - User ID (must match the system)
   - Fingerprint/Card enrollment
   - Name

#### Option B: Using System (if SDK is implemented)
```bash
php artisan employee:register-on-device {employee_id}
```

## Attendance Synchronization

### Manual Sync

Run synchronization manually:

```bash
php artisan attendance:sync
```

Output example:
```
Starting attendance synchronization from ZKTeco device...
Synchronization completed:
- Success: 45
- Failed: 0
```

### Automatic Sync

The system can automatically sync attendance data at regular intervals.

#### Setup Cron Job

Add to your crontab:
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

The system will sync every 5 minutes (configurable via `ZKTECO_SYNC_INTERVAL`).

#### Schedule Configuration

In `routes/console.php`:
```php
Schedule::command('attendance:sync')->everyFiveMinutes();
```

To change the interval:
```php
// Every minute
Schedule::command('attendance:sync')->everyMinute();

// Every 10 minutes
Schedule::command('attendance:sync')->everyTenMinutes();

// Hourly
Schedule::command('attendance:sync')->hourly();
```

## Data Flow

### Attendance Recording Process

1. **Employee clocks in/out** on ZKTeco device
2. **Device stores** the attendance record
3. **System syncs** data from device (automatic or manual)
4. **System processes** the attendance:
   - Matches ZKTeco User ID to Employee
   - Creates/updates Attendance record
   - Calculates working hours
   - Calculates overtime
   - Validates rest periods
   - Checks compliance with Portuguese labor laws
5. **Alerts generated** if violations detected

### Data Structure

#### From ZKTeco Device
```php
[
    'user_id' => '123',           // ZKTeco User ID
    'timestamp' => '2024-01-15 08:30:00',
    'type' => 'check_in',         // or 'check_out'
    'device_id' => 'DEVICE001'
]
```

#### To Attendance Table
```php
[
    'employee_id' => 1,
    'date' => '2024-01-15',
    'clock_in' => '2024-01-15 08:30:00',
    'clock_out' => '2024-01-15 17:30:00',
    'total_hours' => 8.00,
    'regular_hours' => 8.00,
    'overtime_hours' => 0.00,
    'status' => 'present',
    'zkteco_device_id' => 'DEVICE001',
    'synced_at' => '2024-01-15 17:35:00'
]
```

## Testing Connection

### Using Artisan Command

Test device connectivity:
```bash
php artisan zkteco:test-connection
```

### Programmatically

```php
use App\Services\ZKTecoService;

$service = new ZKTecoService();
$result = $service->testConnection();

if ($result['connected']) {
    echo "Connection successful!";
    echo "Device IP: {$result['device_ip']}";
    echo "Device Port: {$result['device_port']}";
} else {
    echo "Connection failed: {$result['message']}";
}
```

## Troubleshooting

### Common Issues

#### 1. Cannot Connect to Device

**Symptoms**: Connection timeout, sync fails

**Solutions**:
- Verify device IP address is correct
- Check device is powered on and connected to network
- Ping device: `ping 192.168.1.201`
- Check firewall settings
- Verify port 4370 is open
- Ensure device and server are on same network/VLAN

#### 2. No Attendance Data Syncing

**Symptoms**: Sync runs but no data appears

**Solutions**:
- Check ZKTeco User IDs match employee records
- Verify employees have `zkteco_user_id` set in system
- Check device has attendance records
- Review sync logs: `storage/logs/laravel.log`
- Ensure device time is synchronized

#### 3. Wrong Employee Matched

**Symptoms**: Attendance appears under wrong employee

**Solutions**:
- Verify ZKTeco User IDs are unique
- Check for duplicate `zkteco_user_id` in employee records
- Update employee's `zkteco_user_id` to match device

#### 4. Hours Not Calculating Correctly

**Symptoms**: Total hours seem wrong

**Solutions**:
- Check both clock-in and clock-out exist
- Verify break times if recorded
- Check timezone settings (`APP_TIMEZONE=Europe/Lisbon`)
- Review calculation in Attendance model

### Debug Mode

Enable detailed logging in `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Check logs:
```bash
tail -f storage/logs/laravel.log
```

## API Integration

### Webhook Endpoint

The system provides a webhook endpoint for real-time attendance data:

```
POST /api/zkteco/attendance
```

#### Request Format
```json
{
    "user_id": "123",
    "timestamp": "2024-01-15 08:30:00",
    "type": "check_in",
    "device_id": "DEVICE001"
}
```

#### Response
```json
{
    "status": "received",
    "employee_id": 1,
    "attendance_id": 456
}
```

### Configure Webhook on Device

If your ZKTeco device supports webhooks:
1. Access device web interface
2. Navigate to Settings → Network → Webhook
3. Enter: `http://your-domain.com/api/zkteco/attendance`
4. Set method to POST
5. Enable webhook

## Multiple Devices

### Supporting Multiple Locations

To support multiple ZKTeco devices:

#### 1. Environment Configuration
```env
ZKTECO_DEVICE_1_IP=192.168.1.201
ZKTECO_DEVICE_1_PORT=4370
ZKTECO_DEVICE_1_LOCATION=Main Office

ZKTECO_DEVICE_2_IP=192.168.2.201
ZKTECO_DEVICE_2_PORT=4370
ZKTECO_DEVICE_2_LOCATION=Branch Office
```

#### 2. Modified Configuration
```php
// config/zkteco.php
return [
    'devices' => [
        'main_office' => [
            'ip' => env('ZKTECO_DEVICE_1_IP'),
            'port' => env('ZKTECO_DEVICE_1_PORT'),
            'location' => env('ZKTECO_DEVICE_1_LOCATION'),
        ],
        'branch_office' => [
            'ip' => env('ZKTECO_DEVICE_2_IP'),
            'port' => env('ZKTECO_DEVICE_2_PORT'),
            'location' => env('ZKTECO_DEVICE_2_LOCATION'),
        ],
    ],
];
```

#### 3. Sync Multiple Devices
```bash
php artisan attendance:sync --device=main_office
php artisan attendance:sync --device=branch_office
php artisan attendance:sync --all-devices
```

## Security Considerations

### Network Security
- Place ZKTeco devices on isolated VLAN
- Use firewall rules to restrict access
- Enable device password protection
- Disable unused device features

### Data Security
- Attendance data is stored encrypted
- Access logs maintained
- Regular backup of attendance records
- User permissions control who can modify attendance

### Compliance
- GDPR compliant biometric data handling
- Attendance data retention policies
- Employee consent for biometric enrollment
- Right to access and delete personal data

## SDK Integration

### Installing ZKTeco SDK (Optional)

For full SDK integration:

```bash
composer require zkteam/zk-library
```

Then update `ZKTecoService.php` to use the SDK instead of placeholder methods.

### SDK Features
- Real-time attendance monitoring
- User management
- Device configuration
- Attendance reports
- Access control

## Best Practices

1. **Regular Syncing**: Sync frequently (every 5-15 minutes) to ensure data freshness

2. **Device Maintenance**: 
   - Keep device firmware updated
   - Clean sensors regularly
   - Check device logs periodically

3. **Data Validation**:
   - Review synced data regularly
   - Check for anomalies
   - Validate employee mappings

4. **Backup**:
   - Regular database backups
   - Export attendance data periodically
   - Keep device backup

5. **Testing**:
   - Test connection after network changes
   - Verify sync after device updates
   - Test employee registration process

## Support Resources

- **ZKTeco Official Documentation**: https://www.zkteco.com
- **Community Forums**: Search for ZKTeco integration discussions
- **Technical Support**: Contact ZKTeco support for device-specific issues

## Appendix: Sample Implementations

### Example: Custom Sync Logic

```php
// app/Console/Commands/CustomSyncCommand.php
namespace App\Console\Commands;

use App\Services\ZKTecoService;
use Illuminate\Console\Command;

class CustomSyncCommand extends Command
{
    protected $signature = 'attendance:custom-sync {--days=7}';
    
    public function handle(ZKTecoService $service)
    {
        $days = $this->option('days');
        $this->info("Syncing last {$days} days...");
        
        // Custom sync logic here
        $results = $service->syncAttendanceRange(
            now()->subDays($days),
            now()
        );
        
        $this->info("Sync completed: {$results['success']} records");
    }
}
```

### Example: Real-time Monitoring

```php
// app/Listeners/AttendanceSyncedListener.php
namespace App\Listeners;

use App\Events\AttendanceSynced;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttendanceSyncedListener implements ShouldQueue
{
    public function handle(AttendanceSynced $event)
    {
        $attendance = $event->attendance;
        
        // Check for violations
        if (!$attendance->hasAdequateRestPeriod()) {
            // Send notification to HR
            Notification::send(
                User::role('hr')->get(),
                new RestPeriodViolationNotification($attendance)
            );
        }
    }
}
```
