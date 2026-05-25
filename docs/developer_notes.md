# Developer Notes

## Component

Frankenstyle component:

```text
local_userdatasync
```

## Namespace Structure

Main namespace:

```php
local_userdatasync
```

Task namespace:

```php
local_userdatasync\task
```

Privacy namespace:

```php
local_userdatasync\privacy
```

## Main Classes

- `local_userdatasync\external_db_client`
- `local_userdatasync\sync_service`
- `local_userdatasync\logger`
- `local_userdatasync\task\sync_user_data_task`
- `local_userdatasync\task\cleanup_logs_task`
- `local_userdatasync\privacy\provider`

## Scheduled Tasks

Scheduled tasks are registered in:

```text
db/tasks.php
```

Task classes are stored in:

```text
classes/task/
```

Both task classes extend:

```php
\core\task\scheduled_task
```

## Privacy Provider

Privacy API implementation:

```text
classes/privacy/provider.php
```

The provider declares metadata and implements export and deletion behavior for synchronization logs.

## Upgrade Process

Upgrade steps are implemented in:

```text
db/upgrade.php
```

Upgrade logic uses Moodle XMLDB APIs and `upgrade_plugin_savepoint()`.

## Moodle Coding Standards

Run PHPCS from the Moodle root:

```bash
vendor/bin/phpcs --standard=local/userdatasync/phpcs.xml.dist local/userdatasync
```

## PHPUnit

Initialize PHPUnit:

```bash
php admin/tool/phpunit/cli/init.php
```

Run plugin tests:

```bash
vendor/bin/phpunit local_userdatasync_testsuite
```

## Development Guidelines

- Use Moodle DML APIs for Moodle database access.
- Validate external identifiers before external SQL construction.
- Use `get_string()` for user-facing text.
- Protect admin actions with login, capabilities, and sesskey checks.
- Update Privacy API metadata if personal data processing changes.
- Add upgrade steps for schema changes.

## Related Documentation

- [Testing](testing.md)
- [Database schema](database_schema.md)
- [Privacy](privacy.md)
