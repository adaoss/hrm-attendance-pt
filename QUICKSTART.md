# Quick Start Guide

Get your HRM Attendance PT system up and running in 15 minutes!

## Prerequisites

- PHP 8.2+ installed
- Composer installed
- MySQL database available

## Fast Track Installation

### 1. Get the Code (1 min)
```bash
git clone https://github.com/adaoss/hrm-attendance-pt.git
cd hrm-attendance-pt
```

### 2. Install Dependencies (3-5 min)
```bash
composer install
```

### 3. Configure Environment (2 min)
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` - minimal required changes:
```env
DB_DATABASE=hrm_attendance
DB_USERNAME=your_username
DB_PASSWORD=your_password

ZKTECO_DEVICE_IP=192.168.1.201
```

### 4. Setup Database (2 min)
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE hrm_attendance;"

# Run migrations
php artisan migrate

# Seed with sample data
php artisan db:seed

# Seed Portuguese holidays
php artisan holidays:seed 2025
```

### 5. Create Admin User (1 min)
```bash
php artisan make:filament-user
```
Enter your admin details when prompted.

### 6. Start the Application (1 min)
```bash
php artisan serve
```

### 7. Access Admin Panel
Open browser: **http://localhost:8000/admin**

Login with credentials from step 5.

## First Steps After Login

### 1. Create a Department
- Navigate to **HRM** → **Departments**
- Click **New Department**
- Enter: Name, Code (e.g., "IT", "HR")
- Save

### 2. Create a Work Schedule
- The default schedule (Mon-Fri, 9-18h) is already created
- Or create custom: **HRM** → **Work Schedules**

### 3. Add an Employee
- Navigate to **HRM** → **Employees**
- Click **New Employee**
- Fill in required fields:
  - Employee Number
  - Name
  - Email
  - Department
  - Work Schedule
  - NIF (9 digits)
  - NISS (11 digits)
- Save

### 4. Record Attendance (Manual)
- Navigate to **Attendance** → **Attendances**
- Click **New Attendance**
- Select employee, date, clock in/out times
- System automatically calculates hours
- Save

### 5. Create Leave Request
- Navigate to **Attendance** → **Leaves**
- Click **New Leave**
- Select employee, leave type, dates
- System calculates working days
- Save

## Configure ZKTeco Device (Optional)

### Quick Setup
1. Ensure device is on network
2. Note device IP address
3. Update `.env`:
   ```env
   ZKTECO_DEVICE_IP=192.168.1.201
   ```
4. Test connection:
   ```bash
   php artisan zkteco:test-connection
   ```
5. Sync attendance:
   ```bash
   php artisan attendance:sync
   ```

## Enable Automatic Sync

Add to crontab:
```bash
crontab -e
```

Add this line:
```
* * * * * cd /path/to/hrm-attendance-pt && php artisan schedule:run >> /dev/null 2>&1
```

## Common Tasks

### View Attendance Report
- Go to **Attendance** → **Attendances**
- Use filters to select date range, employee, or status
- Export if needed

### Check Overtime
- Go to **Attendance** → **Attendances**
- Filter by overtime hours > 0
- View calculated rates

### Check Vacation Balance
- Currently calculated programmatically
- View in employee leave requests
- Track used vs entitled days

### Review Compliance
- Check attendance records for notes about violations
- Look for warnings about rest periods
- Monitor weekly hours

## Demo Data

After running `php artisan db:seed`, you get:
- 3 departments (HR, IT, Finance)
- 1 standard work schedule (40h/week)
- Portuguese holidays for current and next year

## Next Steps

1. **Read Full Documentation**
   - [Installation Guide](INSTALLATION.md) - Complete setup
   - [Portuguese Laws](PORTUGUESE_LABOR_LAWS.md) - Legal compliance
   - [ZKTeco Integration](ZKTECO_INTEGRATION.md) - Device setup

2. **Customize System**
   - Add more departments
   - Create custom work schedules
   - Configure company holidays

3. **Train Users**
   - Show HR how to manage employees
   - Demonstrate leave requests
   - Explain attendance tracking

## Troubleshooting Quick Fixes

### Can't login to admin panel?
```bash
php artisan make:filament-user
```

### Database connection error?
Check `.env` database settings match your MySQL configuration.

### ZKTeco not connecting?
- Verify device IP: `ping 192.168.1.201`
- Check firewall allows port 4370
- Ensure device and server on same network

### Hours not calculating?
- Ensure both clock-in and clock-out are set
- Check timezone in `.env` is Europe/Lisbon
- Run: `php artisan config:clear`

### Page not found?
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

## Support

- **Issues**: https://github.com/adaoss/hrm-attendance-pt/issues
- **Documentation**: See `/docs` in this repository
- **Laravel Help**: https://laravel.com/docs
- **Filament Help**: https://filamentphp.com/docs

## What's Included

✅ Employee management (NIF/NISS tracking)
✅ Attendance tracking (automatic calculations)
✅ Leave management (all Portuguese leave types)
✅ Department administration
✅ Overtime tracking (Portuguese rates)
✅ Holiday calendar (13 Portuguese holidays)
✅ Work schedule management
✅ ZKTeco device integration
✅ Portuguese labor law compliance

## Production Checklist

Before going live:
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Use strong `APP_KEY`
- [ ] Secure database with strong password
- [ ] Configure proper web server (Apache/Nginx)
- [ ] Enable HTTPS/SSL
- [ ] Set up regular backups
- [ ] Configure cron for scheduled tasks
- [ ] Test ZKTeco device connection
- [ ] Train all users

---

**Time to First Productive Use**: ~15 minutes
**Time to Full Production Setup**: ~2 hours

Happy HR Management! 🎉
