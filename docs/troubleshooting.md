# Troubleshooting

## Oracle Connection Problems

Check:

- Oracle client libraries are installed.
- OCI8 PHP extension is enabled.
- Web PHP and CLI PHP use compatible Oracle configuration.
- Moodle can reach the Oracle host.
- Database host, service, username, and password are correct.
- The configured external user can read the source table or view.

Useful commands:

```bash
php -m
php -i
php admin/cli/purge_caches.php
```

## OCI Extension Issues

Check CLI PHP:

```bash
php -m | grep -i oci
```

On Windows PowerShell:

```powershell
php -m | Select-String oci
```

If OCI is available to web PHP but not CLI PHP, scheduled tasks may fail.

## Scheduled Task Failures

Run tasks manually:

```bash
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\sync_user_data_task"
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\cleanup_logs_task"
```

Check:

- Moodle cron is running.
- Plugin is enabled.
- External database settings are complete.
- Field mappings are configured.
- Dry-run mode is understood.

## Empty Sync Results

If no records update:

- Confirm the source table or view returns rows.
- Confirm the remote matching column exists.
- Confirm remote matching values match Moodle user records.
- Confirm mapped external columns contain values.
- Review `skipemptyvalues`, `updateonlyifchanged`, `allowoverwrite`, and `dryrun`.

## Invalid Field Mappings

Invalid external identifiers are rejected before SQL is built.

Check:

- Column names are correct.
- Oracle quoted identifiers are used only when needed.
- Dot-qualified table names are valid.
- The configured source exposes all mapped columns.

## Permission Issues

Check:

- User is logged in.
- Role assignment exists at system context.
- User has `local/userdatasync:viewreport` for the report.
- User has `local/userdatasync:managesettings` for settings and connection testing.
- User has `moodle/site:config` for the connection test page.

## Debugging Recommendations

On a non-production site, enable Moodle developer debugging and run:

```bash
php admin/cli/purge_caches.php
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\sync_user_data_task"
```

Review:

- Scheduled task output.
- Synchronization report.
- Moodle web server logs.
- PHP error logs.
- Oracle client logs where applicable.

## Related Documentation

- [Installation](installation.md)
- [Configuration](configuration.md)
- [Scheduled tasks](scheduled_tasks.md)
