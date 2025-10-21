# HRM Attendance PT - Portuguese Labor Law Compliance System

A comprehensive Human Resource Management and Attendance system built with Laravel 11 and Filament 4.1, specifically designed to comply with Portuguese labor laws (Código do Trabalho) and integrated with ZKTeco biometric attendance devices.

## Features

### 🏢 Core HRM Functionality
- **Employee Management**: Complete employee records with Portuguese NIF and NISS
- **Department Management**: Organize employees by departments with managers
- **Work Schedules**: Flexible scheduling system with weekly hour tracking
- **Contract Types**: Support for permanent, fixed-term, and temporary contracts

### ⏱️ Attendance Management
- **Real-time Tracking**: Integration with ZKTeco biometric devices
- **Clock In/Out**: Automated attendance recording from devices
- **Break Management**: Track break times and calculate net working hours
- **Automated Calculations**: Automatic calculation of regular and overtime hours
- **Status Tracking**: Present, absent, late, and early departure statuses

### 🇵🇹 Portuguese Labor Law Compliance

#### Working Hours (Article 203)
- Maximum 8 hours per day
- Maximum 40 hours per week
- Automatic validation and warnings for violations

#### Overtime Compensation (Article 268)
- **First 2 hours**: 50% extra (1.5x rate)
- **Additional hours**: 75% extra (1.75x rate)
- **Weekends/Holidays**: 100% extra (2.0x rate)
- Automatic rate calculation based on hours worked

#### Rest Periods (Article 214)
- Minimum 11 consecutive hours between shifts
- Automatic validation and violation alerts
- Weekly rest day tracking

#### Vacation Rights (Article 238)
- 22 working days per year
- Proportional calculation for first year
- Vacation balance tracking

#### Leave Types
- **Férias** (Vacation): 22 days/year
- **Licença de Maternidade** (Maternity): 120-150 days
- **Licença de Paternidade** (Paternity): 28 days
- **Licença Parental** (Parental Leave)
- **Casamento** (Marriage): 15 days
- **Luto** (Bereavement): 5 days
- **Baixa Médica** (Sick Leave)
- **Sem Vencimento** (Unpaid Leave)

#### Portuguese Holidays
- Automatic seeding of all 13 Portuguese national holidays
- Easter date calculation (movable holiday)
- Holiday tracking for overtime calculations

### 🔌 ZKTeco Integration
- Automatic attendance synchronization
- Configurable sync intervals (default: 5 minutes)
- Employee registration on devices
- Device connection testing
- Support for multiple devices

### 📊 Filament Admin Panel
- Modern, responsive UI built with Filament 4.1
- Employee management interface
- Attendance tracking and reports
- Leave request management
- Department administration
- Real-time statistics and dashboards

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or PostgreSQL 12+
- ZKTeco attendance device (for production use)

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/adaoss/hrm-attendance-pt.git
   cd hrm-attendance-pt
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hrm_attendance
   DB_USERNAME=root
   DB_PASSWORD=your_password
   ```

5. **Configure ZKTeco device**
   Edit `.env` file:
   ```env
   ZKTECO_DEVICE_IP=192.168.1.201
   ZKTECO_DEVICE_PORT=4370
   ZKTECO_SYNC_INTERVAL=5
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Seed Portuguese holidays**
   ```bash
   php artisan holidays:seed 2025
   ```

8. **Create admin user**
   ```bash
   php artisan make:filament-user
   ```

9. **Start the application**
   ```bash
   php artisan serve
   ```

10. **Access the admin panel**
    Navigate to `http://localhost:8000/admin`

## Usage

### Setting Up Employees

1. Navigate to **Admin Panel** → **Employees**
2. Click **New Employee**
3. Fill in:
   - Personal information
   - Portuguese identification (NIF, NISS)
   - Employment details
   - ZKTeco User ID (from device)
4. Save the employee

### Managing Attendance

Attendance can be managed in two ways:

#### Automatic (ZKTeco Device)
1. Configure device IP and port in `.env`
2. Register employees on the device with their ZKTeco User ID
3. Enable automatic sync:
   ```bash
   php artisan attendance:sync
   ```
4. Or set up scheduled sync in cron:
   ```bash
   * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
   ```

#### Manual
1. Navigate to **Attendance** → **Attendances**
2. Click **New Attendance**
3. Select employee, date, and times
4. System automatically calculates hours and overtime

### Managing Leave Requests

1. Navigate to **Attendance** → **Leaves**
2. Click **New Leave**
3. Select:
   - Employee
   - Leave type (vacation, sick, etc.)
   - Start and end dates
4. System calculates working days automatically
5. Approve or reject requests

### Portuguese Labor Law Features

#### Overtime Calculation
The system automatically:
- Calculates overtime hours (over 8h/day or 40h/week)
- Applies correct rates based on Portuguese law
- Distinguishes between weekday and weekend/holiday rates
- Generates overtime records for approval

#### Vacation Management
- Tracks vacation entitlement (22 days/year)
- Calculates proportional days for first year
- Shows remaining vacation balance
- Validates against maximum days

#### Compliance Monitoring
The system monitors and alerts for:
- Insufficient rest periods (< 11 hours)
- Excessive working hours (> 40 hours/week)
- Missing break times
- Overtime without approval

## Configuration

### Portuguese Labor Law Settings

Edit `config/portuguese-labor.php`:

```php
return [
    'working_hours' => [
        'per_day' => 8,
        'per_week' => 40,
    ],
    'overtime' => [
        'rate_first_hours' => 1.5,
        'rate_additional' => 1.75,
        'rate_weekend_holiday' => 2.0,
    ],
    'vacation' => [
        'days_per_year' => 22,
    ],
    // ... more settings
];
```

### ZKTeco Device Settings

Edit `config/zkteco.php`:

```php
return [
    'device_ip' => env('ZKTECO_DEVICE_IP'),
    'device_port' => env('ZKTECO_DEVICE_PORT'),
    'sync_interval' => 5, // minutes
    'timeout' => 10, // seconds
    'retry_attempts' => 3,
];
```

## Scheduled Tasks

The application includes the following scheduled tasks:

- **Attendance Sync**: Every 5 minutes (configurable)
  ```bash
  php artisan attendance:sync
  ```

To enable scheduled tasks, add to crontab:
```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## API Endpoints

### ZKTeco Webhook
```
POST /api/zkteco/attendance
```
Accepts attendance data from ZKTeco devices.

## Portuguese Labor Code References

This application implements the following articles from the Portuguese Labor Code (Código do Trabalho):

- **Article 203**: Normal working period (8h/day, 40h/week)
- **Article 214**: Daily rest (11 consecutive hours)
- **Article 232**: Weekly rest (1 day, preferably Sunday)
- **Article 238**: Vacation duration (22 working days)
- **Article 252**: Special leaves (marriage, bereavement)
- **Article 268**: Overtime compensation rates
- **Article 40**: Maternity leave (120-150 days)
- **Article 43**: Paternity leave (28 days)

## Technology Stack

- **Framework**: Laravel 11
- **Admin Panel**: Filament 4.1
- **Database**: MySQL/PostgreSQL
- **PHP**: 8.2+
- **Frontend**: Livewire (via Filament)
- **Attendance Device**: ZKTeco biometric devices

## Security

- User authentication via Filament
- Role-based access control
- Secure password hashing
- CSRF protection
- SQL injection prevention
- XSS protection

## Documentation

This project includes comprehensive documentation:

- **[README.md](README.md)** - This file, main project documentation
- **[QUICKSTART.md](QUICKSTART.md)** - Get started in 15 minutes
- **[INSTALLATION.md](INSTALLATION.md)** - Complete installation guide
- **[PORTUGUESE_LABOR_LAWS.md](PORTUGUESE_LABOR_LAWS.md)** - Detailed legal compliance guide
- **[ZKTECO_INTEGRATION.md](ZKTECO_INTEGRATION.md)** - ZKTeco device integration manual
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Guidelines for contributing
- **[CHANGELOG.md](CHANGELOG.md)** - Version history and changes
- **[PROJECT_SUMMARY.md](PROJECT_SUMMARY.md)** - Complete project overview

## Support

For issues, questions, or contributions:
- **Issues**: https://github.com/adaoss/hrm-attendance-pt/issues
- **Documentation**: See the guides listed above
- **Laravel Help**: https://laravel.com/docs
- **Filament Help**: https://filamentphp.com/docs

## License

This project is licensed under the MIT License.

## Disclaimer

This system is designed to comply with Portuguese labor laws as of 2024. Laws may change, and users should consult with legal professionals to ensure full compliance with current regulations. The developers are not responsible for any legal issues arising from the use of this software.