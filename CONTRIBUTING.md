# Contributing to Sentry Resolve

Thank you for considering contributing to Sentry Resolve! This document provides guidelines and information for contributors.

## Development Setup

1. Fork the repository
2. Clone your fork:
```bash
git clone https://github.com/your-username/sentry-resolve.git
cd sentry-resolve
```

3. Install dependencies:
```bash
composer install
```

4. Run tests to ensure everything works:
```bash
composer test
```

## Code Style

This project follows PSR-12 coding standards. Use the following tools:

```bash
# Install coding standards tools
composer require --dev friendsofphp/php-cs-fixer

# Fix code style
vendor/bin/php-cs-fixer fix

# Check code style
vendor/bin/php-cs-fixer fix --dry-run --diff
```

## Running Tests

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run specific test
vendor/bin/phpunit tests/SentryClientTest.php
```

## Submitting Changes

1. Create a feature branch:
```bash
git checkout -b feature/your-feature-name
```

2. Make your changes and add tests
3. Ensure all tests pass:
```bash
composer test
```

4. Commit your changes with descriptive messages
5. Push to your fork:
```bash
git push origin feature/your-feature-name
```

6. Create a pull request

## Pull Request Guidelines

- Include tests for new functionality
- Update documentation as needed
- Follow PSR-12 coding standards
- Ensure all tests pass
- Write clear commit messages
- One feature per pull request when possible

## Bug Reports

When reporting bugs, please include:
- PHP version
- Package version
- Steps to reproduce
- Expected vs actual behavior
- Any error messages

## Feature Requests

Feature requests are welcome! Please:
- Check if it's already been requested
- Describe the use case
- Consider if it fits the package scope
- Be open to discussion

## Release Process

Releases are handled by maintainers:
1. Update version in `composer.json`
2. Update `CHANGELOG.md`
3. Create git tag
4. Release on GitHub
5. Publish to Packagist

Thank you for contributing! 🎉
