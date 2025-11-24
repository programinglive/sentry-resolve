# Sentry Resolve Product Requirements Document (PRD)

## Overview

Sentry Resolve is an open-source PHP package that automates common Sentry issue management tasks. It provides both framework-agnostic tools and Laravel integrations to streamline triaging, resolving, and tracking Sentry errors.

## Goals

- **Automation**: Reduce manual steps when pulling and resolving Sentry issues.
- **Framework neutrality**: Provide a standalone CLI and simple PHP API usable in any project.
- **Laravel experience**: Offer first-class integration via service provider and artisan commands.
- **Developer productivity**: Enable teams to build workflows based on Sentry data quickly.

## Target Audience

- PHP developers using Sentry for error tracking.
- Teams maintaining Laravel applications needing scheduled or automated Sentry workflows.
- DevOps engineers embedding Sentry automation in CI/CD pipelines.

## Key Features

1. **Sentry API Client**
   - Typed PHP client wrapping Sentry REST API endpoints used by commands.
   - Token, organization, and project configuration via environment variables or config files.

2. **Console Commands**
   - `sentry:pull`: generates `SENTRY_TODO.md` with prioritized unresolved issues and removes the file when no matching issues are returned to prevent stale tasks.
   - `sentry:resolve`: marks one or more Sentry issues as resolved.
   - `sentry:debug`: verifies local configuration.
   - `sentry:test-token`: validates API tokens interactively.

3. **Standalone CLI**
   - Binary executable bridging all console commands for non-Laravel usage.

4. **Laravel Integration**
   - Auto-discovered service provider with publishable configuration.
   - Container bindings for `SentryClient` and artisan command registration.
   - Fails fast with a clear error when required Sentry configuration (token, organization, project) is missing.

## Non-Goals

- Implementing full Sentry API coverage.
- Replacing Sentry dashboards or analytics.
- Managing Sentry releases or deployments (left to sentry-cli).

## Success Metrics

- Package downloads and GitHub stars.
- Adoption in automated workflows (documented by community contributions).
- Reduction in manual resolution steps reported by users.

## Roadmap Ideas

- Scheduling helpers for periodic issue pulls.
- Additional analytics and reporting formats (e.g., Slack notifications).
- Symfony bundle or Laravel Nova integration.
- Configurable resolution states (`resolvedInNextRelease`, `ignored`, etc.).

## Risks and Mitigations

| Risk | Mitigation |
|------|------------|
| Sentry API changes | Encapsulate requests via the `SentryClient` and track API deprecations. |
| Rate limits | Allow command options to tune query limits and encourage scheduled use. |
| Token leakage | Documentation emphasizes secure storage; tokens never logged except truncated preview. |

## References

- [Sentry API Docs](https://docs.sentry.io/api/)
- [Sentry Issue Workflow](https://docs.sentry.io/product/issues/)

---

_Last updated: 2025-11-05_
