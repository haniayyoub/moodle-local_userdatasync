# Scheduled Tasks

The plugin uses Moodle scheduled tasks for synchronization and log cleanup.

## Synchronization Task

Class:

```text
local_userdatasync\task\sync_user_data_task
```

Definition:

```text
db/tasks.php
```

The synchronization task is configured with:

```text
blocking: 1
minute: R
hour: *
```

The blocking flag reduces the risk of concurrent synchronization task execution.

The task:

1. Raises the PHP time limit when Moodle supports `core_php_time_limit`.
2. Creates the sync service.
3. Reads plugin settings.
4. Fetches external records in batches.
5. Applies configured mappings.
6. Writes sync logs and last-run statistics.

Manual execution from Moodle root:

```bash
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\sync_user_data_task"
```

## Cleanup Logs Task

Class:

```text
local_userdatasync\task\cleanup_logs_task
```

Definition:

```text
db/tasks.php
```

The cleanup task is scheduled for a randomized minute during hour `3`.

The task:

1. Reads `local_userdatasync/retentiondays`.
2. Stops if retention is `0` or less.
3. Deletes log records older than the configured retention period.

Manual execution from Moodle root:

```bash
php admin/cli/scheduled_task.php --execute="local_userdatasync\task\cleanup_logs_task"
```

## Cron Behavior

Moodle cron must be running for scheduled tasks to execute automatically.

Typical cron command:

```bash
php admin/cli/cron.php
```

Check scheduled tasks in Moodle:

```text
Site administration > Server > Tasks > Scheduled tasks
```

## Related Documentation

- [Configuration](configuration.md)
- [Troubleshooting](troubleshooting.md)
- [Testing](testing.md)
