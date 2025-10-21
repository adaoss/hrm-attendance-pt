# Contributing to HRM Attendance PT

Thank you for your interest in contributing to the HRM Attendance PT project! This document provides guidelines and instructions for contributing.

## Code of Conduct

By participating in this project, you agree to:
- Be respectful and inclusive
- Accept constructive criticism gracefully
- Focus on what is best for the community
- Show empathy towards other community members

## How Can I Contribute?

### Reporting Bugs

Before creating a bug report:
1. Check the [existing issues](https://github.com/adaoss/hrm-attendance-pt/issues)
2. Verify you're using the latest version
3. Try to isolate the problem

When creating a bug report, include:
- **Title**: Clear and descriptive
- **Description**: Detailed explanation of the issue
- **Steps to Reproduce**: Numbered list of steps
- **Expected Behavior**: What you expected to happen
- **Actual Behavior**: What actually happened
- **Environment**: 
  - OS (e.g., Ubuntu 22.04)
  - PHP version
  - MySQL version
  - Laravel version
- **Logs**: Relevant error messages from `storage/logs/`
- **Screenshots**: If applicable

### Suggesting Enhancements

Enhancement suggestions are welcome! Include:
- **Clear title** describing the enhancement
- **Detailed description** of the proposed feature
- **Use cases** showing why it would be useful
- **Examples** of how it would work
- **Mockups** if it's a UI enhancement

### Pull Requests

1. **Fork the repository**
   ```bash
   git clone https://github.com/YOUR_USERNAME/hrm-attendance-pt.git
   ```

2. **Create a feature branch**
   ```bash
   git checkout -b feature/your-feature-name
   ```

3. **Make your changes**
   - Follow the coding standards (below)
   - Write/update tests
   - Update documentation

4. **Test your changes**
   ```bash
   composer test
   php artisan test
   ```

5. **Commit your changes**
   ```bash
   git commit -m "Add feature: description of feature"
   ```

6. **Push to your fork**
   ```bash
   git push origin feature/your-feature-name
   ```

7. **Open a Pull Request**
   - Provide clear description
   - Reference any related issues
   - Include screenshots for UI changes

## Development Setup

### Requirements
- PHP 8.2+
- Composer
- MySQL 5.7+
- Node.js (for asset compilation)

### Setup Steps

1. Clone your fork
2. Install dependencies:
   ```bash
   composer install
   ```

3. Copy environment file:
   ```bash
   cp .env.example .env
   ```

4. Generate app key:
   ```bash
   php artisan key:generate
   ```

5. Set up database:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. Run tests:
   ```bash
   php artisan test
   ```

## Coding Standards

### PHP Code Style

Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards:

```php
<?php

namespace App\Services;

class ExampleService
{
    private string $property;

    public function __construct(string $property)
    {
        $this->property = $property;
    }

    public function doSomething(): array
    {
        return [
            'key' => 'value',
        ];
    }
}
```

### Laravel Conventions

- Use Eloquent ORM for database operations
- Follow Laravel naming conventions:
  - Models: Singular, PascalCase (e.g., `Employee`)
  - Controllers: PascalCase + Controller (e.g., `EmployeeController`)
  - Migrations: Snake case (e.g., `create_employees_table`)
  - Views: Snake case (e.g., `employee_list.blade.php`)

### Code Quality

Run Laravel Pint for code formatting:
```bash
./vendor/bin/pint
```

### Testing

- Write tests for new features
- Maintain existing test coverage
- Use descriptive test names:
  ```php
  public function test_employee_can_have_vacation_balance_calculated()
  {
      // Test implementation
  }
  ```

## Portuguese Labor Law Contributions

When contributing features related to Portuguese labor laws:

1. **Reference the law**: Include article numbers from Código do Trabalho
2. **Add comments**: Explain the legal requirement
3. **Update documentation**: Add to PORTUGUESE_LABOR_LAWS.md
4. **Include tests**: Verify calculations are correct

Example:
```php
/**
 * Calculate overtime rate based on Portuguese Labor Code Article 268
 * First 2 hours: 50% extra (1.5x)
 * Additional hours: 75% extra (1.75x)
 */
public function calculateOvertimeRate(float $hours): float
{
    // Implementation
}
```

## Documentation

### Code Comments

- Document complex logic
- Explain "why" not just "what"
- Use PHPDoc blocks for methods:
  ```php
  /**
   * Calculate vacation days for employee
   *
   * @param Employee $employee
   * @param int $year
   * @return int Number of vacation days
   */
  public function calculateVacationDays(Employee $employee, int $year): int
  ```

### Updating Documentation

When adding features:
1. Update README.md if it affects main features
2. Update relevant guides (INSTALLATION.md, etc.)
3. Add to CHANGELOG.md
4. Update API documentation if applicable

## Database Changes

### Migrations

- Use descriptive migration names
- Include `up()` and `down()` methods
- Test both migrating and rolling back
- Add comments for complex fields:
  ```php
  $table->string('nif', 9)->nullable()
      ->comment('Portuguese Tax Identification Number');
  ```

### Seeds

- Keep seeders idempotent (safe to run multiple times)
- Use factories for test data
- Document what data is seeded

## Git Workflow

### Branch Naming

- `feature/description` - New features
- `fix/description` - Bug fixes
- `docs/description` - Documentation updates
- `refactor/description` - Code refactoring
- `test/description` - Test additions/updates

### Commit Messages

Follow conventional commits:
- `feat: add employee vacation balance tracking`
- `fix: correct overtime calculation for weekends`
- `docs: update ZKTeco integration guide`
- `test: add tests for leave calculations`
- `refactor: improve attendance service structure`

### Pull Request Process

1. Update documentation
2. Add tests
3. Ensure CI passes
4. Request review from maintainers
5. Address review comments
6. Squash commits if requested

## ZKTeco Integration Contributions

When contributing ZKTeco-related features:

1. Test with actual devices if possible
2. Document device compatibility
3. Handle connection errors gracefully
4. Add device-specific configuration options
5. Update ZKTECO_INTEGRATION.md

## Adding New Leave Types

To add a new leave type:

1. Add constant to `Leave` model:
   ```php
   const TYPE_NEW = 'new_type';
   ```

2. Update `getLeaveTypes()` method:
   ```php
   'new_type' => 'New Leave Type Name',
   ```

3. Add to migration enum values

4. Update `PortugueseLaborLawService` if it has special rules

5. Document in PORTUGUESE_LABOR_LAWS.md

## Security

### Reporting Security Issues

**DO NOT** open public issues for security vulnerabilities.

Instead:
1. Email details to security@example.com
2. Include steps to reproduce
3. Describe the potential impact
4. Suggest a fix if possible

### Security Best Practices

- Never commit secrets or API keys
- Use environment variables for sensitive data
- Sanitize user inputs
- Use parameterized queries
- Validate all file uploads
- Implement proper authentication/authorization

## Performance

When contributing:
- Profile database queries
- Use eager loading to avoid N+1 problems
- Add indexes for frequently queried columns
- Consider caching for expensive operations
- Test with realistic data volumes

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

- Open a [GitHub Issue](https://github.com/adaoss/hrm-attendance-pt/issues)
- Check existing [documentation](README.md)
- Review [closed issues](https://github.com/adaoss/hrm-attendance-pt/issues?q=is%3Aissue+is%3Aclosed)

## Recognition

Contributors will be acknowledged in:
- CHANGELOG.md
- GitHub contributors list
- Release notes

Thank you for contributing to HRM Attendance PT! 🎉
