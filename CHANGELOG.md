# Changelog

All notable changes to the HRM Attendance PT project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

**Multi-Tenancy Support**
- Teams table for managing multiple organizations/companies
- Team model with relationships to users, employees, departments, work schedules, and holidays
- BelongsToTeam trait for automatic team scoping and filtering
- Global scopes to ensure data isolation between teams
- Team Filament resource for admin panel management
- TeamSeeder for creating default team
- Comprehensive TENANCY.md documentation
- Unit tests for team functionality (TeamScopingTest)

**Database Changes**
- Added `team_id` foreign key to: users, employees, departments, work_schedules, holidays
- Three new migrations for team support
- JSON settings field on teams table for team-specific configuration

**Documentation**
- TENANCY.md guide covering architecture, usage, and best practices
- Updated README.md to highlight multi-tenancy feature
- Code examples and troubleshooting guide

## [1.0.0] - 2025-01-21

### Initial Release

#### Added

**Core Features**
- Complete HRM and Attendance management system
- Portuguese labor law compliance (Código do Trabalho)
- ZKTeco biometric device integration
- Filament 3.2 admin panel interface

**Employee Management**
- Employee CRUD operations
- Portuguese identification (NIF, NISS) tracking
- Department assignment
- Work schedule management
- Contract type tracking (permanent, fixed-term, temporary)
- Soft delete support

**Attendance System**
- Automatic attendance recording from ZKTeco devices
- Manual attendance entry
- Clock in/out tracking
- Break time management
- Automatic hour calculations
- Regular hours tracking (8h/day maximum)
- Overtime hours calculation
- Status tracking (present, absent, late, early departure)
- Rest period validation (11-hour minimum)

**Portuguese Labor Law Compliance**
- Working hours limits (8h/day, 40h/week) - Article 203
- Daily rest period (11h minimum) - Article 214
- Weekly rest day tracking - Article 232
- Vacation entitlement (22 days/year) - Article 238
- Proportional vacation for first year
- Special leave types - Article 252:
  - Marriage leave (15 days)
  - Bereavement leave (5 days)
  - Sick leave
- Parental leave:
  - Maternity leave (120-150 days) - Article 40
  - Paternity leave (28 days) - Article 43
- Overtime compensation rates - Article 268:
  - First 2 hours: 50% extra (1.5x)
  - Additional hours: 75% extra (1.75x)
  - Weekends/holidays: 100% extra (2.0x)

**Holiday Management**
- Portuguese national holidays (13 holidays)
- Easter date calculation (movable holiday)
- Holiday seeding command
- Working day calculations excluding holidays

**Leave Management**
- Leave request system
- All Portuguese leave types
- Automatic working days calculation
- Leave approval workflow
- Vacation balance tracking

**ZKTeco Integration**
- Device configuration
- Automatic attendance synchronization
- Employee registration on devices
- Connection testing
- Error handling and retry logic
- Scheduled sync (every 5 minutes)
- API webhook endpoint

**Services**
- PortugueseLaborLawService for all calculations
- ZKTecoService for device communication
- Automated compliance validation

**Console Commands**
- `attendance:sync` - Sync attendance from ZKTeco
- `holidays:seed {year}` - Seed Portuguese holidays
- Scheduled tasks via Laravel scheduler

**Admin Panel (Filament)**
- Employee resource with full CRUD
- Attendance resource with filters
- Leave management resource
- Department resource
- Work schedule management
- Overtime tracking
- Holiday calendar
- Responsive interface
- Advanced filtering and search

**Database**
- 8 comprehensive migrations
- Database seeder with sample data
- Holiday seeding for multiple years
- Proper relationships and constraints

**Documentation**
- Comprehensive README
- Portuguese Labor Laws guide (PORTUGUESE_LABOR_LAWS.md)
- ZKTeco Integration guide (ZKTECO_INTEGRATION.md)
- Installation guide (INSTALLATION.md)
- Quick start guide (QUICKSTART.md)
- Inline code documentation

**Testing**
- Unit tests for Portuguese labor law calculations
- PHPUnit configuration
- Test cases for overtime rates
- Test cases for working days calculation
- Test cases for leave entitlements

**Configuration**
- Environment configuration (.env.example)
- Portuguese timezone (Europe/Lisbon)
- Portuguese locale (pt)
- ZKTeco device configuration
- Labor law settings
- Database configuration

**Security**
- User authentication
- Admin panel access control
- Password hashing
- CSRF protection
- Environment variable security
- Secure session management

### Technical Details

**Framework & Versions**
- Laravel 11.x
- Filament 3.2
- PHP 8.2+
- MySQL 5.7+

**Architecture**
- Service layer pattern
- Repository pattern
- Model relationships
- Observers for automation
- Form validation
- Database transactions

**Performance**
- Optimized queries
- Eager loading
- Database indexing
- Caching support
- Queue support

### Known Limitations

- ZKTeco SDK integration is implemented as a service layer (requires actual SDK for production use)
- UI is currently admin-panel only (no employee self-service portal)
- Reports are basic (advanced reporting to be added in future versions)

### Future Enhancements

Planned for future releases:
- Employee self-service portal
- Advanced reporting and analytics
- Mobile app integration
- Biometric data management
- Payroll integration
- Email notifications
- Multi-language support
- Real-time dashboard
- Export to Excel/PDF
- API documentation (Swagger)

---

## Version History

- **1.0.0** (2025-01-21) - Initial release with full Portuguese labor law compliance

---

## Migration Guide

This is the initial release, no migration needed.

## Contributors

- Initial development and Portuguese labor law implementation

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
