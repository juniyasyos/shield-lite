# Changelog

All notable changes to Shield Lite will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-01-XX

### Added
- **Trait-based Integration**: New `HasShieldRoles`, `HasShieldPermissions`, and `AuthorizesShield` traits for User model integration
- **Multiple Permission Drivers**: Support for both Spatie Permission package and built-in array-based permissions
- **Automatic Policy Resolution**: `GenericPolicy` class with `__call` magic method for automatic CRUD operation handling
- **PolicyResolver**: Automatic policy registration and resolution system
- **Configurable Ability Naming**: Flexible ability format configuration (dot notation, colon notation, underscore notation)
- **Ability Normalizer**: `Ability` class for consistent ability naming and normalization
- **Enhanced Configuration**: Comprehensive config file with driver selection, ability configuration, and policy settings
- **Laravel Gate Integration**: Deep integration with Laravel's authorization system
- **Super Admin Configuration**: Flexible super admin identification (by role, attribute, user ID, or callback)
- **Model Auto-discovery**: Automatic discovery of models for policy registration
- **Comprehensive Testing**: Full Pest test suite for all components
- **Enhanced Documentation**: Complete README, upgrade guide, and API reference

### Changed
- **BREAKING**: Removed UserResource from plugin core - must be created in application
- **BREAKING**: Configuration structure completely redesigned for flexibility
- **BREAKING**: Permission checking now uses configurable drivers instead of hard-coded Spatie integration
- **BREAKING**: Moved from simple role/permission checking to comprehensive policy-based authorization
- Upgraded to Laravel 12 compatibility
- Improved package structure with proper namespacing and organization
- Enhanced error handling and fallback mechanisms

### Removed
- **BREAKING**: `src/Resources/Users/` directory and associated UserResource classes
- **BREAKING**: Hard dependency on specific Filament resource structure
- **BREAKING**: Old configuration format and keys
- Deprecated helper functions and methods

### Fixed
- Improved compatibility with different Laravel and Filament versions
- Better handling of edge cases in permission checking
- More robust error handling in policy resolution
- Fixed issues with trait method conflicts

### Security
- Enhanced permission checking with proper authorization flows
- Improved super admin bypass mechanisms
- Better isolation between permission drivers

## [1.x.x] - Previous Versions

### Features from Previous Versions
- Basic role and permission management
- Filament panel integration  
- UserResource with basic CRUD operations
- Simple configuration system
- Laravel Gate integration

### Migration Notes
For users upgrading from 1.x versions, please see [UPGRADE.md](UPGRADE.md) for detailed migration instructions.

---

## Upgrade Instructions

**From 1.x to 2.0**: This is a major release with significant architectural changes. Please follow the detailed upgrade guide in [UPGRADE.md](UPGRADE.md).

## Compatibility

### Version 2.0+
- **PHP**: 8.2+
- **Laravel**: 10.x, 11.x, 12.x  
- **Filament**: 4.x
- **Spatie Permission**: 6.x (optional)

### Testing Framework
- **Pest**: 2.x
- **PHPUnit**: 10.x

## Development

### Architecture Improvements
The 2.0 release represents a complete architectural overhaul focused on:

1. **Flexibility**: Multiple permission backends and configurable ability naming
2. **Extensibility**: Trait-based integration allows for easy customization
3. **Performance**: Automatic policy resolution and caching support
4. **Maintainability**: Clean separation of concerns and comprehensive testing
5. **Developer Experience**: Improved documentation and upgrade tooling

### Code Quality
- Full type declarations throughout the codebase
- Comprehensive Pest test coverage
- PHPStan level 8 compliance
- PSR-12 coding standards
- Extensive inline documentation

## Contributing

We welcome contributions! The 2.0 release establishes a solid foundation for future enhancements:

- **Feature Requests**: Please open an issue with detailed requirements
- **Bug Reports**: Include steps to reproduce and environment details  
- **Pull Requests**: Follow the existing code style and include tests
- **Documentation**: Help improve examples and guides

## Acknowledgments

Thanks to all contributors who helped make the 2.0 release possible:

- Major architectural design and implementation
- Comprehensive testing and quality assurance
- Documentation writing and review
- Community feedback and testing

## Support

- **Documentation**: See [README.md](README.md) for complete usage guide
- **Migration**: See [UPGRADE.md](UPGRADE.md) for version upgrade instructions
- **Issues**: Report bugs and feature requests on GitHub
- **Discussions**: Join community discussions for help and tips
