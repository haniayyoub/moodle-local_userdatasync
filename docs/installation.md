# Installation

## Plugin Installation

Copy the plugin directory to:

```text
local/userdatasync
```

The installed directory must contain `version.php` directly under `local/userdatasync`. Do not install the plugin inside an extra parent folder.

## Moodle Upgrade

From the Moodle root, run:

```bash
php admin/cli/upgrade.php
php admin/cli/purge_caches.php
```

You may also complete the upgrade through the Moodle web interface, but CLI upgrade is recommended for production sites.

## Oracle and OCI Requirements

For Oracle external sources, configure the required Oracle client support for both web PHP and CLI PHP.

Typical requirements:

- Oracle Instant Client or equivalent Oracle client libraries.
- OCI8 PHP extension.
- Network access from the Moodle server to the Oracle database.
- A read-only Oracle account with access to the configured source table or view.

Check CLI PHP modules:

```bash
php -m
php -m | grep -i oci
```

On Windows PowerShell:

```powershell
php -m | Select-String oci
```

## Initial Setup

1. Install the plugin at `local/userdatasync`.
2. Run Moodle upgrade.
3. Purge caches.
4. Open the plugin settings page.
5. Configure the external database connection.
6. Configure matching and field mappings.
7. Keep dry-run mode enabled for the first synchronization.
8. Use the connection test page.
9. Run the scheduled task manually and review the report.

## Validation Commands

From the Moodle root:

```bash
php admin/cli/purge_caches.php
php admin/cli/upgrade.php
php admin/tool/phpunit/cli/init.php
vendor/bin/phpcs --standard=local/userdatasync/phpcs.xml.dist local/userdatasync
vendor/bin/phpunit local_userdatasync_testsuite
```

## Non-default DB Prefix Considerations

The plugin uses Moodle table placeholder syntax for Moodle SQL. Validate installation and upgrade on a site with a non-default Moodle database prefix before production deployment.

Recommended checks:

- Fresh install.
- Upgrade from the previous plugin version.
- Report page access.
- Privacy API export/delete behavior.
- Scheduled cleanup task execution.

## Related Documentation

- [Configuration](configuration.md)
- [Scheduled tasks](scheduled_tasks.md)
- [Testing](testing.md)
- [Troubleshooting](troubleshooting.md)
