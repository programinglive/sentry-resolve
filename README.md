# Sentry Resolve

[![Latest Version on Packagist](https://img.shields.io/packagist/v/programinglive/sentry-resolve.svg?style=flat-square)](https://packagist.org/packages/programinglive/sentry-resolve)
[![Tests](https://img.shields.io/github/actions/workflow/status/programinglive/sentry-resolve/run-tests.yml?branch=main)](https://github.com/programinglive/sentry-resolve/actions)
[![Total Downloads](https://img.shields.io/packagist/dt/programinglive/sentry-resolve.svg?style=flat-square)](https://packagist.org/packages/programinglive/sentry-resolve)

Automate Sentry issue resolution with PHP commands and CLI tools. This package provides a simple way to fetch, manage, and resolve Sentry issues across any PHP project.

## Features

- 🚀 **Framework Agnostic** - Works with Laravel, Symfony, or any PHP project
- 📋 **Issue Management** - Pull unresolved issues into a TODO file
- 🔧 **Bulk Resolution** - Resolve multiple issues at once
- 🖥️ **CLI Tool** - Standalone command-line interface
- 🧪 **Well Tested** - Comprehensive test coverage
- 📝 **Flexible Configuration** - Environment-based configuration

## Installation

### Composer Install

```bash
composer require --dev programinglive/sentry-resolve
```

### Laravel Installation

1. Install the package:
```bash
composer require --dev programinglive/sentry-resolve
```

> **Why dev-only?** This package automates fixing Sentry issues during development workflows and is not intended for production environments.

2. Publish the configuration:
```bash
php artisan vendor:publish --tag=sentry-resolve-config
```

3. Add environment variables to your `.env`:
```env
SENTRY_TOKEN=your_sentry_api_token
SENTRY_ORG=your_organization
SENTRY_PROJECT=your_project
```

## Configuration

### Environment Variables

```env
# Required
SENTRY_TOKEN=sntrys_eyJpYXQiOjE3...
SENTRY_ORG=your-organization-slug
SENTRY_PROJECT=your-project-slug
SENTRY_DSN=https://examplePublicKey@o0.ingest.sentry.io/0

# Optional (for Laravel integration)
SENTRY_TRACES_SAMPLE_RATE=1.0
```

### Getting Sentry Credentials

1. **API Token**: Go to Sentry → User Settings → API Tokens → Create New Token
   - Required scopes: `project:read`, `event:read`, `issue:read`, `issue:write`

2. **Organization & Project**: Found in your Sentry project URL
   - URL: `https://your-org-slug.sentry.io/projects/your-project-slug/`
   - Organization: `your-org-slug`
   - Project: `your-project-slug`

## Usage

### Standalone CLI

After installation, you can use the CLI tool directly:

```bash
# Test your configuration
./vendor/bin/sentry-resolve sentry:debug

# Pull issues
./vendor/bin/sentry-resolve sentry:pull --limit=10 --sort=freq

# Resolve issues
./vendor/bin/sentry-resolve sentry:resolve ISSUE-1 ISSUE-2

# Test a token
./vendor/bin/sentry-resolve sentry:test-token your_token_here
```

### Laravel Artisan Commands

```bash
# Test configuration
php artisan sentry:debug

# Pull latest issues
php artisan sentry:pull --limit=25 --sort=freq

# Resolve specific issues
php artisan sentry:resolve ISSUE-1 ISSUE-2

# Test a token
php artisan sentry:test-token your_token_here
```

### PHP Native Usage

```php
<?php

require 'vendor/autoload.php';

use Mahardhika\SentryResolve\SentryClient;

$client = new SentryClient(
    'your_token',
    'your_organization', 
    'your_project'
);

// Get issues
$issues = $client->getIssues([
    'query' => 'is:unresolved',
    'limit' => 25,
    'sort' => 'freq'
]);

// Resolve an issue
$client->resolveIssue('ISSUE-1');

// Test token
$auth = $client->testToken();
```

## Available Commands

### `sentry:pull`

Fetches unresolved issues from Sentry and generates a `SENTRY_TODO.md` file.

**Options:**
- `--limit` (-l): Number of issues to fetch (default: 25)
- `--query` (-q): Sentry search query (default: "is:unresolved")
- `--sort` (-s): Sort order - freq|new|priority|trend|user (default: "freq")
- `--output` (-o): Output file path (default: "SENTRY_TODO.md")

**Examples:**
```bash
# Pull 10 most frequent issues
php artisan sentry:pull --limit=10 --sort=freq

# Pull newest issues in production
php artisan sentry:pull --query="is:unresolved environment:production" --sort=new

# Custom output file
php artisan sentry:pull --output=PROJECT_TODO.md
```

### `sentry:resolve`

Resolves one or more Sentry issues.

**Arguments:**
- `identifiers`: One or more issue IDs (e.g., "ISSUE-1", "ISSUE-2")

**Examples:**
```bash
# Resolve single issue
php artisan sentry:resolve ISSUE-1

# Resolve multiple issues
php artisan sentry:resolve ISSUE-1 ISSUE-2 ISSUE-3
```

#### Resolution Logging

Every resolved issue is logged to a rotating log file (daily by default).

**Environment variables:**

- `SENTRY_RESOLVE_LOG_ENABLED` (default: `true`)
- `SENTRY_RESOLVE_LOG_PATH` (default: `storage/logs` in Laravel or `storage/logs` relative to the package)
- `SENTRY_RESOLVE_LOG_FREQUENCY` (options: `daily`, `monthly`, `yearly` — default: `daily`)
- `SENTRY_RESOLVE_LOG_PREFIX` (default: `sentry-resolve`)

**Log filenames:** `{prefix}-{YYYY-MM-DD}.log` (based on frequency).

### `sentry:debug`

Tests your Sentry configuration and displays current settings.

**Examples:**
```bash
php artisan sentry:debug
```

### `sentry:test-token`

Tests a specific Sentry token without requiring full configuration.

**Arguments:**
- `token`: The Sentry API token to test

**Examples:**
```bash
php artisan sentry:test-token sntrys_eyJpYXQiOjE3...
```

## Workflow Integration

### Daily Workflow

1. **Pull Issues**: Get latest issues from Sentry
```bash
php artisan sentry:pull --limit=10 --sort=freq
```

2. **Review**: Check the generated `SENTRY_TODO.md`
3. **Fix**: Create branches and fix issues
4. **Resolve**: Mark issues as resolved
```bash
php artisan sentry:resolve ISSUE-1 ISSUE-2
```

### Git Hooks (Optional)

Add to `.git/hooks/pre-commit`:
```bash
#!/bin/bash
# Check for Sentry TODO
if [ -f "SENTRY_TODO.md" ]; then
    if grep -q "Found [1-9]" SENTRY_TODO.md; then
        echo "⚠️  You have unresolved Sentry issues in SENTRY_TODO.md"
        echo "   Please review and fix them before committing"
        exit 1
    fi
fi
```

### CI/CD Integration

```yaml
# GitHub Actions example
- name: Check Sentry Issues
  run: |
    php artisan sentry:pull --limit=5
    if grep -q "Found [1-9]" SENTRY_TODO.md; then
      echo "❌ Unresolved Sentry issues found"
      cat SENTRY_TODO.md
      exit 1
    fi
```

## Advanced Usage

### Custom Query Examples

```bash
# Production errors only
php artisan sentry:pull --query="is:unresolved environment:production level:error"

# Issues affecting many users
php artisan sentry:pull --query="is:unresolved user.count:>10" --sort=user

# Specific time range
php artisan sentry:pull --query="is:unresolved firstSeen:>2023-01-01"
```

### Programmatic Usage

```php
use Mahardhika\SentryResolve\Commands\SentryPullCommand;
use Symfony\Component\Console\Application;

$app = new Application();
$client = new SentryClient($token, $org, $project);
$app->add(new SentryPullCommand($client));
$app->run();
```

## Testing

```bash
# Run tests
composer test

# Run tests with coverage
composer test-coverage
```

## Configuration File (Laravel)

Published config file at `config/sentry-resolve.php`:

```php
<?php

return [
    'token' => env('SENTRY_TOKEN'),
    'organization' => env('SENTRY_ORG'),
    'project' => env('SENTRY_PROJECT'),
    
    'defaults' => [
        'pull' => [
            'limit' => 25,
            'query' => 'is:unresolved',
            'sort' => 'freq',
            'output' => 'SENTRY_TODO.md',
        ],
    ],
];
```

## Troubleshooting

### Common Issues

**403 Forbidden Error**
- Check your token scopes
- Verify organization and project names
- Ensure token has `issue:write` scope for resolve operations

**No Issues Found**
- Verify your query syntax
- Check if issues are already resolved
- Confirm organization/project access

**Command Not Found (Laravel)**
- Run `php artisan package:discover`
- Ensure service provider is registered
- Check config is published

**`Sentry Resolve is not configured` error**
- Ensure `SENTRY_TOKEN`, `SENTRY_ORG`, and `SENTRY_PROJECT` are set in your environment or `config/sentry-resolve.php`.
- Run `php artisan config:clear` followed by `php artisan config:cache` to refresh Laravel's configuration.

### Debug Mode

Use the debug command to troubleshoot configuration:

```bash
php artisan sentry:debug
```

This will show:
- Token validity (first 20 characters)
- Organization and project names
- Configuration status

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

See [CONTRIBUTING.md](CONTRIBUTING.md) for detailed guidelines, along with our [Code of Conduct](CODE_OF_CONDUCT.md) and [Security Policy](SECURITY.md).

### Commit & Release Workflow

We use [`@programinglive/commiter`](https://www.npmjs.com/package/@programinglive/commiter) to enforce Conventional Commits and automate releases.

- Commit format: `type(scope): subject`
- Supported types: `feat`, `fix`, `perf`, `refactor`, `docs`, `style`, `test`, `build`, `ci`, `chore`, `revert`
- Husky hooks run `commitlint` for message validation and `npm test` (alias for `composer test`).

Release scripts:

```bash
npm run release       # auto-detect version bump
npm run release:patch # 1.0.0 -> 1.0.1
npm run release:minor # 1.0.0 -> 1.1.0
npm run release:major # 1.0.0 -> 2.0.0
```

After releasing, push the tag and commits:

```bash
git push --follow-tags origin main
```

## Project Resources

- [Product Requirements Document](docs/PRD.md)
- [Issue Templates](.github/ISSUE_TEMPLATE)
- [Pull Request Template](.github/pull_request_template.md)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for more information on what has changed recently.

## Support

- 📖 [Documentation](https://github.com/programinglive/sentry-resolve)
- 🐛 [Issue Tracker](https://github.com/programinglive/sentry-resolve/issues)
- 💬 [Discussions](https://github.com/programinglive/sentry-resolve/discussions)
