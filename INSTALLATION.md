# Installation and Setup Guide

## System Requirements

### Server Requirements
- PHP 8.2 or higher
- Composer 2.x
- MySQL 5.7+ or PostgreSQL 12+
- Web server (Apache/Nginx)
- 512MB RAM minimum (1GB recommended)
- 500MB disk space

### PHP Extensions Required
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- cURL
- GD or Imagick

### Optional
- Redis (for caching and queues)
- Supervisor (for queue workers)
- Node.js and NPM (for asset compilation if customizing)

## Installation Steps

### 1. Clone Repository

```bash
git clone https://github.com/adaoss/hrm-attendance-pt.git
cd hrm-attendance-pt
```

### 2. Install Dependencies

```bash
composer install
```

If you encounter memory issues:
```bash
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### 3. Environment Configuration

Copy the example environment file:
```bash
cp .env.example .env
```

Generate application key:
```bash
php artisan key:generate
```

### 4. Configure Environment

Edit `.env` file with your settings:

#### Application Settings
```env
APP_NAME="HRM Attendance PT"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
APP_TIMEZONE=Europe/Lisbon
APP_LOCALE=pt
```

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrm_attendance
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password
```

#### ZKTeco Device Configuration
```env
ZKTECO_DEVICE_IP=192.168.1.201
ZKTECO_DEVICE_PORT=4370
ZKTECO_SYNC_INTERVAL=5
```

#### Portuguese Labor Law Settings
```env
WORKING_HOURS_PER_DAY=8
WORKING_HOURS_PER_WEEK=40
OVERTIME_RATE_FIRST=1.5
OVERTIME_RATE_ADDITIONAL=1.75
MIN_REST_HOURS=11
VACATION_DAYS_PER_YEAR=22
```

### 5. Database Setup

Create the database:
```bash
mysql -u root -p
CREATE DATABASE hrm_attendance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

Run migrations:
```bash
php artisan migrate
```

Seed database with default data:
```bash
php artisan db:seed
```

Seed Portuguese holidays:
```bash
php artisan holidays:seed 2025
php artisan holidays:seed 2026
```

### 6. Create Admin User

Create your first admin user:
```bash
php artisan make:filament-user
```

Follow the prompts:
- Name: Your Name
- Email: admin@example.com
- Password: (choose a secure password)

### 7. Storage Setup

Create symbolic link for storage:
```bash
php artisan storage:link
```

Set proper permissions:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

If using Apache, you may need:
```bash
chown -R www-data:www-data storage bootstrap/cache
```

### 8. Web Server Configuration

#### Apache Configuration

Create virtual host file `/etc/apache2/sites-available/hrm-attendance.conf`:

```apache
<VirtualHost *:80>
    ServerName hrm-attendance.local
    ServerAlias www.hrm-attendance.local
    DocumentRoot /path/to/hrm-attendance-pt/public
    
    <Directory /path/to/hrm-attendance-pt/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/hrm-attendance-error.log
    CustomLog ${APACHE_LOG_DIR}/hrm-attendance-access.log combined
</VirtualHost>
```

Enable site and restart Apache:
```bash
sudo a2ensite hrm-attendance
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### Nginx Configuration

Create configuration file `/etc/nginx/sites-available/hrm-attendance`:

```nginx
server {
    listen 80;
    server_name hrm-attendance.local;
    root /path/to/hrm-attendance-pt/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/hrm-attendance /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 9. Schedule Setup

Add to crontab for scheduled tasks:
```bash
crontab -e
```

Add this line:
```
* * * * * cd /path/to/hrm-attendance-pt && php artisan schedule:run >> /dev/null 2>&1
```

This enables:
- Automatic attendance synchronization
- Regular compliance checks
- Automated reports

### 10. Queue Worker (Optional but Recommended)

For better performance, set up queue workers:

#### Using Supervisor

Install Supervisor:
```bash
sudo apt-get install supervisor
```

Create config file `/etc/supervisor/conf.d/hrm-attendance-worker.conf`:

```ini
[program:hrm-attendance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/hrm-attendance-pt/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/hrm-attendance-pt/storage/logs/worker.log
stopwaitsecs=3600
```

Start supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hrm-attendance-worker:*
```

### 11. SSL Configuration (Production)

For production, always use HTTPS. Using Let's Encrypt with Certbot:

```bash
sudo apt-get install certbot python3-certbot-apache
# For Apache
sudo certbot --apache -d hrm-attendance.example.com

# OR for Nginx
sudo apt-get install certbot python3-certbot-nginx
sudo certbot --nginx -d hrm-attendance.example.com
```

### 12. Verify Installation

Access your installation:
```
http://your-domain.com/admin
```

Login with admin credentials created in step 6.

### 13. Initial Configuration

After logging in:

1. **Configure Departments**
   - Navigate to Admin → Departments
   - Create your company departments

2. **Configure Work Schedules**
   - Navigate to Admin → Work Schedules
   - Create standard schedules (9-18h, etc.)

3. **Add Employees**
   - Navigate to Admin → Employees
   - Add employee records with NIF and NISS

4. **Configure ZKTeco Device**
   - Update `.env` with device IP
   - Test connection: `php artisan zkteco:test-connection`
   - Register employees on device

5. **Verify Holidays**
   - Navigate to Admin → Holidays
   - Confirm Portuguese holidays are loaded

## Post-Installation

### Testing the System

1. **Test Attendance Recording**
   - Create manual attendance record
   - Verify hour calculations
   - Check overtime calculations

2. **Test Leave Management**
   - Create leave request
   - Verify working days calculation
   - Check vacation balance

3. **Test ZKTeco Sync**
   ```bash
   php artisan attendance:sync
   ```

### Security Hardening

1. **Environment File**
   - Secure `.env` file permissions:
     ```bash
     chmod 600 .env
     ```

2. **Disable Debug Mode**
   ```env
   APP_DEBUG=false
   ```

3. **Set Strong Passwords**
   - Admin accounts
   - Database users
   - Application key

4. **Configure Firewall**
   ```bash
   sudo ufw allow 80/tcp
   sudo ufw allow 443/tcp
   sudo ufw allow 22/tcp
   sudo ufw enable
   ```

5. **Regular Updates**
   ```bash
   composer update
   php artisan migrate
   ```

### Backup Strategy

1. **Database Backup**
   ```bash
   # Create backup script
   mysqldump -u username -p hrm_attendance > backup_$(date +%Y%m%d).sql
   ```

2. **File Backup**
   ```bash
   # Backup important files
   tar -czf backup_files_$(date +%Y%m%d).tar.gz storage .env
   ```

3. **Automated Backups**
   Add to crontab:
   ```bash
   0 2 * * * /path/to/backup-script.sh
   ```

### Monitoring

1. **Log Monitoring**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **System Health**
   - Check: `http://your-domain.com/up`

3. **Error Tracking**
   - Monitor `storage/logs/` directory
   - Set up external monitoring (Sentry, Bugsnag, etc.)

## Upgrading

### From Source

```bash
# Backup first!
mysqldump -u username -p hrm_attendance > backup_pre_upgrade.sql

# Pull latest changes
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Restart services
sudo systemctl restart php8.2-fpm
sudo supervisorctl restart all
```

## Troubleshooting

### Common Issues

#### 1. "500 Internal Server Error"
- Check Laravel logs: `storage/logs/laravel.log`
- Check web server logs
- Verify file permissions
- Check `.env` configuration

#### 2. "Database Connection Failed"
- Verify database credentials in `.env`
- Check database server is running
- Test connection: `mysql -u username -p`

#### 3. "Page Not Found" for /admin
- Clear config cache: `php artisan config:clear`
- Check web server configuration
- Verify `public/` is document root

#### 4. Filament Assets Not Loading
- Run: `php artisan filament:assets`
- Clear browser cache
- Check public directory permissions

#### 5. ZKTeco Sync Not Working
- Verify device IP and port
- Check network connectivity
- Review ZKTeco logs
- Test connection manually

### Getting Help

- **Documentation**: Check `/docs` directory
- **GitHub Issues**: https://github.com/adaoss/hrm-attendance-pt/issues
- **Laravel Documentation**: https://laravel.com/docs
- **Filament Documentation**: https://filamentphp.com/docs

## Development Setup

For local development:

### Using Laravel Sail (Docker)

```bash
# Install Sail
composer require laravel/sail --dev

# Publish Sail configuration
php artisan sail:install

# Start containers
./vendor/bin/sail up -d

# Run migrations
./vendor/bin/sail artisan migrate

# Access application
http://localhost
```

### Using Laravel Valet (macOS)

```bash
# Install Valet
composer global require laravel/valet
valet install

# Link project
cd /path/to/hrm-attendance-pt
valet link hrm-attendance

# Access application
http://hrm-attendance.test
```

### Using PHP Built-in Server

```bash
php artisan serve
# Access: http://localhost:8000
```

## Performance Optimization

### Production Optimizations

```bash
# Optimize configuration
php artisan config:cache

# Optimize routes
php artisan route:cache

# Optimize views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize --classmap-authoritative
```

### Database Optimization

```bash
# Add indexes for frequently queried fields
php artisan make:migration add_indexes_to_attendance_table

# Optimize database
php artisan db:optimize
```

### Caching

Configure Redis in `.env`:
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

## Next Steps

After successful installation:

1. Read [Portuguese Labor Laws Documentation](PORTUGUESE_LABOR_LAWS.md)
2. Review [ZKTeco Integration Guide](ZKTECO_INTEGRATION.md)
3. Configure your first department and employees
4. Set up ZKTeco device integration
5. Test attendance recording and reporting
6. Train your HR staff on the system

## Support

For issues during installation:
- Check the troubleshooting section
- Review system logs
- Consult the documentation
- Open an issue on GitHub with detailed information
