# Database Schema

## Log Table

The plugin creates one Moodle table:

```text
local_userdatasync_log
```

The table stores synchronization audit records.

## Columns

### id

Primary key.

### userid

Moodle user id associated with the synchronization event.

The field is:

```text
NOTNULL="true" DEFAULT="0"
```

The value `0` is used for system or anonymous log events.

### username

Legacy compatibility column. Current logging does not normally populate this field.

### fieldname

Moodle field or custom profile field evaluated during synchronization.

### oldvalue

Legacy compatibility column. Current logging does not normally populate this field.

### newvalue

Legacy compatibility column. Current logging does not normally populate this field.

### status

Synchronization result status.

Examples include:

- `updated`
- `skipped_empty`
- `skipped_same`
- `invalid_email`
- `invalid_phone`
- `user_not_found`
- `error`
- `dry_run`
- `skipped_overwrite_disabled`

### message

Safe diagnostic message for audit and troubleshooting.

### timecreated

Unix timestamp for log creation time.

## Indexes

The table defines non-unique indexes on:

- `userid`
- `username`
- `fieldname`
- `status`
- `timecreated`

These support report filtering, user privacy lookups, and retention cleanup.

## Retention Strategy

The cleanup scheduled task deletes records older than the configured retention period.

Setting:

```text
local_userdatasync/retentiondays
```

When the value is `0` or less, cleanup is disabled.

## Upgrade Behavior

Upgrade steps are defined in:

```text
db/upgrade.php
```

Current upgrade behavior includes:

- Adding default configuration values for retention, maximum runtime, and pagination when needed.
- Updating null `userid` log values to `0`.
- Changing `userid` to not-null with default `0`.
- Saving Moodle plugin upgrade savepoints.

## XMLDB Notes

Schema is defined in:

```text
db/install.xml
```

The XMLDB version is:

```text
2026052500
```

No foreign key is defined on `userid`. This is intentional for audit log retention and to allow system log records using `userid = 0`.

## Related Documentation

- [Privacy](privacy.md)
- [Security](security.md)
- [Testing](testing.md)
