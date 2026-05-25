# local_userdatasync

## Overview

`local_userdatasync` is a Moodle local plugin that synchronizes selected Moodle user profile fields from an external database table or view. It is intended for sites where user profile data is maintained in an institutional source system and selected fields need to be reflected in Moodle.

The plugin is commonly configured for an external Oracle source. The implemented external database client uses Moodle's bundled ADOdb library and also includes aliases for MySQL/MariaDB, PostgreSQL, and SQL Server drivers where the required PHP extensions and database clients are available.

Synchronization is controlled by Moodle admin settings. Administrators configure the external source table or view, the local and remote matching fields, and the Moodle profile fields that should be updated. The plugin runs through Moodle scheduled tasks and records audit/synchronization log entries for reporting and troubleshooting.

## Features

- External database integration using Moodle's bundled ADOdb layer.
- Oracle aliases including `oracle`, `oci`, `oci8`, and `oci8po`.
- Configurable source table or view.
- Safe external table and column identifier validation.
- Configurable Moodle matching field and remote matching column.
- Field mappings for supported standard Moodle user fields.
- Field mappings for selected custom profile fields.
- Scheduled synchronization task.
- Scheduled log cleanup task.
- Admin connection testing interface.
- Audit and synchronization report.
- Privacy API metadata, export, and deletion support.
- Configurable batch size, maximum runtime, and pagination behavior.
- Dry-run mode for testing mappings before writing changes.
- Moodle coding-standard-oriented structure and `phpcs.xml.dist`.

## Requirements

The plugin is intended for Moodle 4.3, Moodle 4.4, and Moodle 5.x deployments. The plugin currently declares Moodle `2022112800` as the minimum required version and avoids PHP 8-only string helpers so it can remain compatible with supported PHP versions for the declared Moodle range.

For Oracle connectivity, both web PHP and CLI PHP must have access to the required Oracle client libraries and PHP database extension used by ADOdb. In most Oracle deployments this means Oracle Instant Client and the OCI8 PHP extension.

Required Moodle capabilities are:

- `local/userdatasync:viewreport`
- `local/userdatasync:managesettings`

The connection test page also checks `moodle/site:config`.

## Architecture Summary

Main components:

- `settings.php`: plugin settings and admin page registrations.
- `index.php`: synchronization report page.
- `testconnection.php`: admin-only external database connection test page.
- `classes/external_db_client.php`: external database connection, identifier validation, record fetching, and connection testing.
- `classes/sync_service.php`: synchronization workflow.
- `classes/logger.php`: sync log persistence and task output.
- `classes/task/sync_user_data_task.php`: scheduled synchronization task.
- `classes/task/cleanup_logs_task.php`: scheduled cleanup task for old logs.
- `classes/privacy/provider.php`: Privacy API metadata, export, and deletion support.
- `db/install.xml`: sync log table definition.
- `db/upgrade.php`: plugin upgrade steps.
- `db/access.php`: capability definitions.
- `db/tasks.php`: scheduled task definitions.

## Moodle Compatibility

The plugin is documented for Moodle 4.3, Moodle 4.4, and Moodle 5.x administrators. It should be tested on the target Moodle version before production use, including install, upgrade, scheduled task execution, external database connectivity, and non-default database prefix handling.

## Author Information

Copyright (C) 2026 Hani Ayyoub

Component name:

```text
local_userdatasync
```

## Documentation

- [Installation](installation.md)
- [Configuration](configuration.md)
- [Scheduled tasks](scheduled_tasks.md)
- [Field mapping](field_mapping.md)
- [Security](security.md)
- [Privacy](privacy.md)
- [Database schema](database_schema.md)
- [Troubleshooting](troubleshooting.md)
- [Developer notes](developer_notes.md)
- [Testing](testing.md)
- [Changelog](changelog.md)
- [License](license.md)
