# Multi-Tenancy Support

This document describes the multi-tenancy implementation in the HRM Attendance PT system.

## Overview

The system now supports multi-tenancy through a **Teams** concept. Each team represents a separate organization/company that can use the system with complete data isolation.

## Architecture

### Teams Table

The `teams` table is the central entity for tenancy:

- `id`: Primary key
- `name`: Team/Company name
- `slug`: Unique identifier (URL-friendly)
- `description`: Optional description
- `settings`: JSON field for team-specific configuration
- `is_active`: Boolean flag to enable/disable teams
- `created_at`, `updated_at`: Timestamps

### Team Relationships

The following models now belong to a team:

1. **Users** - System users (admin, managers, etc.)
2. **Employees** - Employee records
3. **Departments** - Organizational departments
4. **Work Schedules** - Work schedule templates
5. **Holidays** - Team-specific or national holidays

### Indirect Team Relationships

These models inherit team context through their relationships:

1. **Attendance** - Through employee
2. **Leave** - Through employee
3. **Overtime** - Through employee
4. **Wages** - Through employee

## Global Scoping

### BelongsToTeam Trait

The `App\Models\Concerns\BelongsToTeam` trait provides automatic team scoping:

```php
use App\Models\Concerns\BelongsToTeam;

class Employee extends Model
{
    use BelongsToTeam;
}
```

This trait:

1. **Automatic Filtering**: Adds a global scope that filters all queries by the authenticated user's team
2. **Automatic Assignment**: Automatically assigns the current user's team_id when creating new records
3. **Team Relationship**: Provides the `team()` relationship method

### How It Works

When a user is authenticated:

```php
// Only returns employees from the user's team
$employees = Employee::all();

// Creating a new employee automatically assigns the team
$employee = Employee::create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    // team_id is automatically set from auth()->user()->team_id
]);
```

### Bypassing Team Scope

If you need to query across teams (e.g., for super admin functionality):

```php
use Illuminate\Database\Eloquent\Builder;

// Query all employees regardless of team
$allEmployees = Employee::withoutGlobalScope('team')->get();

// Query specific team
$teamEmployees = Employee::withoutGlobalScope('team')
    ->where('team_id', $teamId)
    ->get();
```

## Database Migrations

### Migration Order

1. `2025_10_22_060500_create_teams_table.php` - Creates the teams table
2. `2025_10_22_060501_add_team_id_to_tables.php` - Adds team_id to users, employees, departments, work_schedules
3. `2025_10_22_060502_add_team_id_to_holidays_table.php` - Adds team_id to holidays

### Running Migrations

```bash
php artisan migrate
```

### Seeding Initial Data

```bash
php artisan db:seed --class=TeamSeeder
```

This creates a default team that can be used immediately.

## Usage Examples

### Creating a New Team

```php
use App\Models\Team;

$team = Team::create([
    'name' => 'Acme Corporation',
    'slug' => 'acme-corp',
    'description' => 'A leading software company',
    'is_active' => true,
    'settings' => [
        'timezone' => 'Europe/Lisbon',
        'working_hours_per_day' => 8,
    ],
]);
```

### Assigning Users to Teams

```php
use App\Models\User;

$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@acme.com',
    'password' => bcrypt('password'),
    'team_id' => $team->id,
    'is_admin' => true,
]);
```

### Team-Scoped Queries

```php
// Assuming a user is authenticated and belongs to a team

// All these queries are automatically scoped to the user's team:
$employees = Employee::all();
$departments = Department::all();
$workSchedules = WorkSchedule::all();

// Relationships also respect team scoping:
$department = Department::with('employees')->first();
```

## Filament Admin Panel

### Team Management

A new "Teams" resource is available in the Filament admin panel under the "Settings" group:

- **List Teams**: View all teams
- **Create Team**: Add a new team/organization
- **Edit Team**: Modify team details and settings
- **Delete Team**: Remove a team (cascade deletes associated data)

### Team Filtering

All existing Filament resources (Employees, Departments, etc.) automatically filter by team through the global scope. No changes are needed to existing resources.

## Testing

### Unit Tests

Team functionality is tested in `tests/Unit/Models/TeamScopingTest.php`:

```bash
php artisan test --filter TeamScopingTest
```

Tests cover:
- Team creation
- Team relationships
- Model associations with teams
- Team counts

## Best Practices

1. **Always Set Team on User Creation**: When creating users, always assign them to a team
2. **Super Admin Access**: Implement super admin roles that can bypass team scoping if needed
3. **Team Settings**: Use the `settings` JSON field for team-specific configurations
4. **Data Migration**: When migrating existing data, assign all records to a default team
5. **Testing**: Always test with multiple teams to ensure proper data isolation

## Data Isolation

The team scoping ensures:

1. Users from Team A cannot see data from Team B
2. All queries are automatically filtered by team
3. New records are automatically associated with the user's team
4. Cascading deletes maintain referential integrity

## Future Enhancements

Potential improvements to consider:

1. **Team Switching**: Allow users to belong to multiple teams and switch between them
2. **Team Roles**: Implement team-specific roles and permissions
3. **Team Invitations**: Email-based team invitation system
4. **Team Billing**: Subscription/billing per team
5. **Team Analytics**: Dashboards showing team-wide metrics
6. **API Authentication**: Token-based API access with team context
7. **Team Domains**: Custom subdomains per team (e.g., acme.hrm-system.com)

## Troubleshooting

### Issue: Queries return empty results

**Solution**: Ensure the authenticated user has a `team_id` set.

```php
auth()->user()->team_id; // Should not be null
```

### Issue: Cannot create records

**Solution**: Check if the user is authenticated and has a team assigned.

```php
if (!auth()->check() || !auth()->user()->team_id) {
    // Assign user to a team first
}
```

### Issue: Need to query across teams

**Solution**: Use `withoutGlobalScope('team')`:

```php
$allRecords = Model::withoutGlobalScope('team')->get();
```

## Security Considerations

1. **Always validate team ownership**: When accepting user input (like IDs), verify they belong to the user's team
2. **API endpoints**: Ensure API authentication includes team context
3. **File uploads**: Scope file storage by team to prevent unauthorized access
4. **Background jobs**: Pass team context to queued jobs
5. **Database backups**: Consider per-team backup strategies

## Conclusion

The multi-tenancy implementation provides robust data isolation between teams while maintaining simplicity through global scopes. The `BelongsToTeam` trait makes it easy to add team support to new models, and the automatic scoping ensures developers don't accidentally leak data between teams.
