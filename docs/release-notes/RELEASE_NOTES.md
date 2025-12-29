# Release Notes

| Version | Date | Description |
| --------- | ----------- | ------- |
| 1.1.5 | 2025-12-29 | improve mobile responsiveness and center hero content (0a6547a) |
| 1.1.4 | 2025-12-29 | refine FAQ layout to 2 columns for better readability (cddd17e) |
| 1.1.3 | 2025-12-29 | add release notes for v1.1.2 (a13432a) |
| 1.1.2 | 2025-12-29 | make release notes script robust and sync data (b02d677) |
| 1.1.1 | 2025-12-29 | Release Notes Format Fix |
| 1.1.0 | 2025-12-29 | UI/UX Overhaul and Branding |
| 1.0.8 | 2024-12-02 | Missing Configuration Handling |
| 1.0.7 | 2024-11-05 | Stale TODO Cleanup |
| 1.0.6 | 2024-10-31 | Short ID Lookup |
| 1.0.5 | 2024-10-31 | Meta/Tooling Alignment |
| 1.0.4 | 2024-10-31 | CLI Workflow Improvements |
| 1.0.3 | 2024-10-30 | Query Flag Collision Fix |
| 1.0.2 | 2024-10-30 | Resolution Logging |
| 1.0.1 | 2024-10-29 | Open Source Setup |
| 1.0.0 | 2024-10-29 | Initial Release |






## 1.1.5 – 🐛 Bug Fixes

Released on **2025-12-29**.

- improve mobile responsiveness and center hero content (0a6547a)
- resolve mobile layout overflow and code wrapping (eb4afad)

## 1.1.4 – 💄 Styles

Released on **2025-12-29**.

- refine FAQ layout to 2 columns for better readability (cddd17e)

## 1.1.3 – 📝 Documentation

Released on **2025-12-29**.

- add release notes for v1.1.2 (a13432a)
- add version and download badges to website hero (90cd77f)

## 1.1.2 – 🐛 Bug Fixes

Released on **2025-12-29**.

- make release notes script robust and sync data (b02d677)

## v1.1.0 — 2025-12-29

### ✨ Features
- Launched the project documentation website on Netlify with Dark/Light mode support.
- Enhanced `sentry:pull` command output to provide actionable resolution tips (`sentry:resolve {ID}`).
- **UI/UX Overhaul**: Improved the website layout with better contrast, a responsive 2-column feature grid, and single-column installation steps for enhanced code readability.
- Added official Sentry and GitHub branding logos to the website.

### 🐛 Bug Fixes
- Fixed `PrismJS` loading errors and improved syntax highlighting.
- Resolved terminal component styling issues and contrast problems in Light Mode.
- Darkened text colors in Light Mode to improve legibility and sharpness.
- Enforced dark backgrounds for all code blocks to ensure readability across all themes.

### 🧹 Chores
- Set Light Mode as the default website theme.
- Removed the GitHub Actions workflow to optimize the development loop.
- Updated `README.md` and `docs/PRD.md` to reflect the new website.

---

## v1.0.8 — 2025-12-02

### 🐛 Bug Fixes
- Prevent `EntryNotFoundException` when running Sentry commands without `SENTRY_TOKEN`, `SENTRY_ORG`, or `SENTRY_PROJECT` by handling missing configuration gracefully and surfacing clear guidance in CLI output.

### ✅ Testing
- Added regression coverage ensuring each command reports missing configuration without crashing.

---

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
