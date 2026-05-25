# Testing

Run tests from a full Moodle checkout with the plugin installed at:

```text
local/userdatasync
```

## PHPCS

Run Moodle coding standards checks:

```bash
vendor/bin/phpcs --standard=local/userdatasync/phpcs.xml.dist local/userdatasync
```

Fix all reported errors before packaging.

## PHPUnit

Initialize PHPUnit:

```bash
php admin/tool/phpunit/cli/init.php
```

Run the plugin tests:

```bash
vendor/bin/phpunit local_userdatasync_testsuite
```

## Install Testing

On a clean Moodle test site:

```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

Confirm:

- Plugin installs without errors.
- `local_userdatasync_log` table is created.
- Admin settings page loads.
- Capabilities are registered.
- Scheduled tasks are registered.
- Connection test page loads for authorized administrators.

## Upgrade Testing

From a previous plugin version:

```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

Confirm:

- Upgrade completes.
- Existing log records are retained.
- Null `userid` values, if present, are converted to `0`.
- Settings are preserved.
- Scheduled tasks remain registered.

## Non-default DB Prefix Testing

Install Moodle using a non-default database prefix and repeat:

- Fresh install.
- Upgrade.
- Report page access.
- Privacy export/delete tests.
- Cleanup task execution.

This confirms raw Moodle SQL uses table placeholders correctly.

## Oracle Connectivity Testing

On a test site with Oracle client support:

1. Configure external database settings.
2. Use the connection test page.
3. Run the synchronization task in dry-run mode.
4. Review task output and sync logs.
5. Disable dry-run only after mappings are verified.

Task command:

```bash
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\sync_user_data_task"
```

## Cleanup Task Testing

Set a short retention period on a test site and run:

```bash
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\cleanup_logs_task"
```

Confirm old log records are removed according to retention settings.

## Related Documentation

- [Installation](installation.md)
- [Scheduled tasks](scheduled_tasks.md)
- [Troubleshooting](troubleshooting.md)
