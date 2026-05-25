# Changelog

## 0.2.0 - 2026-05-25

Initial Moodle Plugins Directory submission candidate.

### Security

- Converted the external database connection test action to POST-only behavior.
- Added explicit login protection to the connection test page.
- Confirmed sesskey validation for the connection test action.
- Retained capability checks for report, settings, and connection testing.
- Added safe external table and column identifier validation.

### Privacy

- Added Privacy API metadata for external database processing.
- Declared synchronization log metadata.
- Added export and deletion support for sync logs.
- Clarified that Moodle reads/processes external source data and does not export Moodle user data back to that source.

### Scheduled Tasks

- Added scheduled synchronization task.
- Added scheduled log cleanup task.
- Set the synchronization task as blocking.
- Localized task output strings.

### Database

- Added `local_userdatasync_log` audit table.
- Updated `userid` handling to use not-null with default `0`.
- Added upgrade handling for existing null `userid` values.
- Added indexes for reporting and cleanup paths.

### Localization

- Moved user-facing and admin-facing strings into `lang/en/local_userdatasync.php`.
- Localized synchronization and cleanup task output.

### Moodle Standards

- Added `phpcs.xml.dist`.
- Normalized copyright headers.
- Removed PHP 8-only string helper usage for broader declared compatibility.
- Added Moodle-style documentation under `docs/`.
