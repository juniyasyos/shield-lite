# Contributing to Shield Lite

Thank you for considering contributing to Shield Lite! This document outlines the guidelines for contributing to this project.

## 🎯 How to Contribute

### Reporting Bugs

Before creating bug reports, please check the issue list as you might find that the issue has already been reported. When creating a bug report, include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps to reproduce the problem**
- **Provide specific examples** to demonstrate the steps
- **Include Laravel, PHP, and Spatie Permission versions**
- **Describe the behavior you observed** and what you expected to see

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub issues. When creating an enhancement suggestion:

- **Use a clear and descriptive title**
- **Provide a step-by-step description** of the suggested enhancement
- **Explain why this enhancement would be useful** to Shield Lite users
- **List some other packages** where this enhancement exists, if applicable

### Pull Requests

1. **Fork the repository**
2. **Create a feature branch** from `main`
3. **Make your changes**
4. **Add or update tests** as necessary
5. **Ensure the test suite passes**
6. **Make sure your code follows the existing style**
7. **Write a good commit message**
8. **Submit a pull request**

## 🛠️ Development Setup

### Prerequisites

- PHP 8.2+
- Composer
- Laravel 12.0+
- Spatie Laravel Permission 6.0+

### Local Development

1. **Clone the repository:**
   ```bash
   git clone https://github.com/juniyasyos/shield-lite.git
   cd shield-lite
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Run tests:**
   ```bash
   composer test
   # or
   vendor/bin/pest
   ```

### Testing

Shield Lite uses Pest for testing. All new features should include tests.

**Running Tests:**
```bash
# Run all tests
vendor/bin/pest

# Run specific test file
vendor/bin/pest tests/Feature/SpatieIntegrationTest.php

# Run with coverage
vendor/bin/pest --coverage
```

**Writing Tests:**

Tests should follow the existing patterns:

```php
<?php

use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('New Feature', function () {
    
    it('should do something specific', function () {
        // Arrange
        $user = User::factory()->create();
        $permission = Permission::create(['name' => 'test.permission', 'guard_name' => 'web']);
        
        // Act
        $user->givePermissionTo($permission);
        
        // Assert
        expect($user->can('test.permission'))->toBeTrue();
    });
    
});
```

## 📝 Code Style

### PHP Code Style

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Add docblocks for classes and public methods
- Keep methods small and focused

### Example:

```php
<?php

namespace juniyasyos\ShieldLite\Support;

/**
 * Helper class for managing resource names.
 */
class ResourceName
{
    /**
     * Convert a model class or instance to a resource name.
     *
     * @param string|object $modelOrClass The model class or instance
     * @return string The formatted resource name
     */
    public static function fromModel($modelOrClass): string
    {
        $class = is_string($modelOrClass) ? $modelOrClass : get_class($modelOrClass);
        $base = class_basename($class);
        $snake = Str::snake($base);
        
        return Str::plural($snake);
    }
}
```

### Documentation

- Update README.md if your changes affect usage
- Add inline comments for complex logic
- Update docblocks when changing method signatures

## 🏗️ Architecture Guidelines

### Core Principles

1. **Spatie Integration**: Build on top of Spatie Permission, don't reinvent
2. **Zero Boilerplate**: Minimize code users need to write
3. **Laravel Native**: Use Laravel's built-in authorization patterns
4. **Backward Compatibility**: Avoid breaking changes when possible
5. **Performance**: Consider cache implications and performance

### Adding New Features

When adding new features:

1. **Check if Spatie already provides it** - don't duplicate functionality
2. **Follow the existing patterns** in the codebase
3. **Add comprehensive tests** covering edge cases
4. **Update documentation** with examples
5. **Consider configuration options** for flexibility

### File Structure

```
src/
├── Contracts/          # Interfaces
├── Database/
│   └── Seeders/       # Database seeders
├── Drivers/           # Permission drivers
├── Policies/          # Policy classes
├── Support/           # Helper classes
└── ShieldLiteServiceProvider.php
```

## 🔄 Branching Strategy

- **main**: Stable releases
- **develop**: Development branch (if used)
- **feature/xxx**: New features
- **fix/xxx**: Bug fixes
- **docs/xxx**: Documentation updates

### Commit Messages

Use conventional commits format:

```
type(scope): description

[optional body]

[optional footer]
```

**Types:**
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Adding or updating tests
- `chore`: Maintenance tasks

**Examples:**
```
feat(policies): add automatic policy registration
fix(cache): resolve permission cache invalidation issue
docs(readme): add comprehensive usage examples
```

## 🧪 Testing Guidelines

### Test Organization

```
tests/
├── Feature/           # Integration tests
├── Unit/             # Unit tests
└── Pest.php         # Pest configuration
```

### Test Categories

1. **Unit Tests**: Test individual classes/methods
2. **Feature Tests**: Test integration with Laravel/Spatie
3. **Policy Tests**: Test authorization logic
4. **Cache Tests**: Test permission caching behavior

### Test Database

Tests should use SQLite in-memory database for speed:

```php
// In Pest.php or test setup
uses(RefreshDatabase::class);
```

## 📚 Documentation

### README Updates

When updating README.md:

- Keep examples practical and realistic
- Include both basic and advanced usage
- Test all code examples before committing
- Use consistent formatting and style

### Code Comments

```php
/**
 * Brief description of what the method does.
 *
 * Longer explanation if needed, including:
 * - Important behavior notes
 * - Parameter expectations
 * - Return value details
 *
 * @param string $parameter Description of parameter
 * @return bool Description of return value
 * @throws Exception When this might throw
 */
public function methodName(string $parameter): bool
{
    // Implementation
}
```

## 🔍 Review Process

### Pull Request Checklist

Before submitting a PR, ensure:

- [ ] Tests pass locally
- [ ] Code follows style guidelines
- [ ] Documentation is updated
- [ ] Commit messages are clear
- [ ] No unnecessary changes (formatting, etc.)
- [ ] Feature is properly tested

### Review Criteria

PRs will be reviewed based on:

1. **Code Quality**: Clean, readable, maintainable
2. **Testing**: Adequate test coverage
3. **Documentation**: Clear and complete
4. **Compatibility**: Works with supported versions
5. **Performance**: No significant performance regression

## 🚀 Release Process

Shield Lite follows semantic versioning:

- **MAJOR**: Breaking changes
- **MINOR**: New features (backward compatible)
- **PATCH**: Bug fixes (backward compatible)

### Version Compatibility

| Shield Lite | Laravel | PHP | Spatie Permission |
|-------------|---------|-----|-------------------|
| 4.x         | 12.x+   | 8.2+ | 6.x              |
| 3.x         | 10.x-11.x | 8.1+ | 5.x            |

## 💬 Communication

- **Issues**: For bug reports and feature requests
- **Discussions**: For questions and general discussion
- **Pull Requests**: For code contributions
- **Discord/Slack**: [Link if available]

## 📄 License

By contributing to Shield Lite, you agree that your contributions will be licensed under the MIT License.

## 🙏 Recognition

Contributors will be recognized in:
- CHANGELOG.md for significant contributions
- README.md contributors section
- GitHub contributors graph

Thank you for contributing to Shield Lite! 🎉
