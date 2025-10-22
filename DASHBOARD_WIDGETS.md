# Dashboard Widgets

This document describes the comprehensive dashboard widgets available for team administrators in the HRM Attendance PT system.

## Overview

The dashboard provides a comprehensive view of team health, attendance patterns, leave management, and overtime tracking. All widgets are designed to be multi-tenant aware and automatically filter data based on the authenticated user's team.

## Available Widgets

### 1. TeamStatsOverview - Key Performance Indicators

**Type**: Stats Overview Widget  
**Sort Order**: 1 (appears first)  
**Purpose**: Display critical team metrics at a glance

**Displayed Metrics**:
- **Active Employees**: Total number of active team members
- **Departments**: Total number of organizational units
- **Today's Attendance**: Number of employees marked present today
- **Pending Approvals**: Number of leave requests awaiting approval (color-coded: warning if > 5)
- **Monthly Attendance Rate**: Percentage of attendance for current month (color-coded: green ≥95%, amber ≥85%, red <85%)

**Features**:
- Automatic calculation of working days (excluding weekends)
- Color-coded alerts based on thresholds
- Real-time data updates

### 2. EmployeesByDepartmentChart - Department Distribution

**Type**: Doughnut Chart Widget  
**Sort Order**: 2  
**Purpose**: Visualize employee distribution across departments

**Features**:
- Interactive doughnut chart with color-coded segments
- Shows employee count per department
- Helps identify department sizes and resource allocation
- Legend displayed at bottom for easy identification

**Use Cases**:
- Quick department size comparison
- Resource allocation planning
- Identifying imbalanced departments

### 3. AttendanceTrendChart - 30-Day Attendance Analysis

**Type**: Line Chart Widget  
**Sort Order**: 3  
**Width**: Full width for better visibility  
**Purpose**: Display attendance patterns over the last 30 days

**Features**:
- Line chart showing daily attendance counts
- 30-day rolling window
- Smooth curve with area fill for better visualization
- Date labels on X-axis
- Count scale on Y-axis starting from zero

**Use Cases**:
- Identify attendance trends and patterns
- Spot anomalies or unusual attendance drops
- Monitor team health over time
- Correlate attendance with business cycles

### 4. LeaveBalanceWidget - Leave Management Overview

**Type**: Stats Overview Widget  
**Sort Order**: 4  
**Purpose**: Track vacation and leave usage in compliance with Portuguese labor law

**Displayed Metrics**:
- **Total Vacation Entitlement**: Total vacation days available (22 days × number of employees)
- **Vacation Days Used**: Total vacation days consumed (with usage percentage)
- **Vacation Remaining**: Available vacation days for the team
- **Sick Leave**: Total sick leave days taken in current year
- **Other Leave**: Days for maternity, paternity, marriage, bereavement, etc.

**Portuguese Labor Law Compliance**:
- Implements Article 238 (22 vacation days per year)
- Tracks all Portuguese leave types
- Color-coded indicators for usage levels
- Warning alerts when vacation usage exceeds 75%
- Danger alerts when remaining vacation falls below 25%

### 5. OvertimeStatsChart - Overtime Analysis

**Type**: Bar Chart Widget  
**Sort Order**: 5  
**Purpose**: Display top 10 employees with most overtime hours for current month

**Features**:
- Bar chart sorted by overtime hours (descending)
- Shows employee name (first name + last initial)
- Current month data only
- Gradient color scheme
- Excludes rejected overtime requests

**Use Cases**:
- Identify workload distribution issues
- Spot potential burnout risks
- Support workforce planning
- Ensure compliance with overtime regulations
- Resource management and balancing

### 6. PendingApprovalsWidget - Action Items

**Type**: Interactive Table Widget  
**Sort Order**: 6  
**Width**: Full width  
**Purpose**: Display all pending leave requests requiring approval

**Displayed Columns**:
- **Employee**: Full name (searchable and sortable)
- **Leave Type**: Color-coded badge with Portuguese labels
  - Férias (Vacation) - Green
  - Baixa Médica (Sick) - Red
  - Maternidade/Paternidade - Blue
  - Casamento (Marriage) - Amber
  - Luto (Bereavement) - Gray
  - Sem Vencimento (Unpaid) - Gray
- **Start Date**: dd/mm/yyyy format
- **End Date**: dd/mm/yyyy format
- **Days**: Number of days requested
- **Reason**: Truncated with tooltip on hover

**Actions**:
- **Review Button**: Direct link to leave request edit page
- Opens in same tab for seamless workflow

**Features**:
- Sorted by start date (ascending)
- Empty state with helpful message
- Searchable employee names
- Sortable columns

## Testing the Widgets

### Using Test Data Seeder

To populate your database with test data for development and testing:

```bash
php artisan db:seed --class=TestDataSeeder
```

This will create:
- 1 test team ("Test Company")
- 1 admin user (email: admin@example.com, password: password)
- 4 departments (Engineering, HR, Sales, Marketing)
- 20 employees with realistic Portuguese names
- 30 days of attendance records (90% attendance rate)
- Overtime records for some employees
- 15 leave requests in various states (pending, approved, rejected)

**Login Credentials**:
- Email: `admin@example.com`
- Password: `password`

After seeding, navigate to `/admin` to see all widgets in action.

## Multi-Tenancy Support

All widgets automatically filter data by the authenticated user's team:

```php
$teamId = auth()->user()->team_id;
```

This ensures complete data isolation between different teams/organizations using the system.

## Widget Configuration

### Customizing Widget Order

Widget sort order is controlled by the `$sort` property:

```php
protected static ?int $sort = 1; // Lower numbers appear first
```

### Changing Widget Width

By default, widgets use half width. For full-width widgets:

```php
protected int | string | array $columnSpan = 'full';
```

### Customizing Widget Heading

```php
protected static ?string $heading = 'Your Custom Heading';
```

## Technical Implementation

### Dependencies

The widgets use:
- **Filament 4.1**: Base widget framework
- **Chart.js**: Chart rendering (via Filament)
- **Laravel 12**: Backend framework
- **Carbon**: Date manipulation

### Performance Considerations

All widgets use:
- Efficient database queries with proper indexing
- Eager loading to prevent N+1 queries
- Scoped queries for multi-tenancy
- Cached calculations where appropriate

### Database Indexes

Ensure these indexes exist for optimal performance:
- `attendances.employee_id`
- `attendances.date`
- `attendances.status`
- `leaves.employee_id`
- `leaves.status`
- `leaves.start_date`
- `overtimes.employee_id`
- `overtimes.date`
- `employees.team_id`
- `departments.team_id`

## Troubleshooting

### Widgets Not Appearing

1. Clear cache: `php artisan cache:clear`
2. Check `app/Providers/Filament/AdminPanelProvider.php` has:
   ```php
   ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
   ```

### Empty/No Data

1. Ensure you have a team assigned to your user
2. Run the test data seeder: `php artisan db:seed --class=TestDataSeeder`
3. Check that employees, departments, etc. have the correct `team_id`

### Performance Issues

1. Add database indexes (see above)
2. Consider reducing the date range for AttendanceTrendChart (from 30 to 14 days)
3. Limit the number of results in OvertimeStatsChart (currently 10)

## Future Enhancements

Potential improvements for the dashboard:

- [ ] Add filtering capabilities (by department, date range)
- [ ] Export functionality for charts
- [ ] Drill-down capabilities
- [ ] Real-time updates via WebSockets
- [ ] Customizable widget layout
- [ ] Additional metrics (absenteeism rate, turnover rate)
- [ ] Comparison with previous periods
- [ ] Predictive analytics

## Related Documentation

- [TENANCY.md](TENANCY.md) - Multi-tenancy implementation
- [PORTUGUESE_LABOR_LAWS.md](PORTUGUESE_LABOR_LAWS.md) - Legal compliance details
- [README.md](README.md) - Main project documentation
