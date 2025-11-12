# Release Notes

## v1.0.7 — 2025-11-05

### 🐛 Bug Fixes
- Ensure `sentry:pull` removes stale `SENTRY_TODO.md` when no matching issues are returned, keeping the issue queue clean.

### 📚 Documentation
- Updated the PRD to capture the new `sentry:pull` behavior and refreshed the last-updated date.

### ✅ Testing
- Added regression coverage guaranteeing both default and custom output files are deleted when no issues are fetched.


No functional changes. Tag published to distribute the latest fixes captured in v1.0.4.

---

## v1.0.6 — 2025-10-31

### 🐛 Bug Fixes
- Resolve Sentry issues by short ID lookup for more reliable automation.

### 🧹 Chores
- Tagged the 1.0.5 release and aligned packaging metadata.

---

## v1.0.5 — 2025-10-31

No functional changes. Tag published to distribute the latest fixes captured in v1.0.4.

---

## v1.0.4 — 2025-10-31

### 🐛 Bug Fixes
- Improved the CLI workflow and documentation for the Sentry commands.

---

## v1.0.3 — 2025-10-30

### 🐛 Bug Fixes
- Prevent query flag collisions when invoking `sentry:pull`.

---

## v1.0.2 — 2025-10-30

### ✨ Features
- Added resolution logging with rotation to keep long-running automations tidy.

### 🧹 Chores
- Bumped commiter tooling to version 1.1.0.

---

## v1.0.1 — 2025-10-29

### ✨ Features
- Initial open-source package setup, including the core commands and client wrapper.

### 🧹 Chores
- Integrated commiter tooling and published the 1.0.0 release artifacts.
- Configured PHPUnit to run with coverage by default.

---

## v1.0.0 — 2025-10-29

### ✨ Features
- First public release of Sentry Resolve featuring the SentryClient, console commands, Laravel integration, CLI binary, and documentation.
