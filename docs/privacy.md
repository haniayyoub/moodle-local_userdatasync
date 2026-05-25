# Privacy

## Privacy API Implementation

The plugin includes a Privacy API provider:

```text
classes/privacy/provider.php
```

The provider declares metadata for:

- External database data read and processed by Moodle.
- Synchronization log records stored in Moodle.

It also implements user data export and deletion methods for synchronization logs.

## External Data Processing

The plugin reads configured user profile data from an external database table or view and may write selected mapped values to Moodle user profiles.

The plugin does not export Moodle user data back to the external system.

## Data Categories Processed

Depending on configured mappings, the plugin may read and process:

- username or configured matching identifier
- firstname
- lastname
- email
- phone1
- phone2
- department
- institution
- configured mapped custom profile fields

Implemented custom profile field mappings include:

- faculty
- major
- degree

## Sync Logs

The plugin stores synchronization logs in:

```text
local_userdatasync_log
```

Current logs normally contain:

- Moodle user id
- Moodle field name
- synchronization status
- safe diagnostic message
- timestamp

Legacy columns exist for username, old value, and new value for upgrade compatibility. Current logging does not normally populate those personal-value columns.

## Export Support

For users with synchronization log records, the Privacy API provider exports log data from the system context.

Exported log entries include:

- field name
- status
- message
- old value legacy field
- new value legacy field
- time created

## Delete Support

The provider supports:

- deleting all plugin log data in the system context
- deleting one user's log data
- deleting log data for a list of approved users

## Administrator Responsibilities

Administrators should:

- Map only fields that Moodle needs.
- Prefer an external database view that exposes only required columns.
- Review retention settings.
- Restrict access to settings and reports.
- Document the external source in the site's privacy information.

## Related Documentation

- [Security](security.md)
- [Database schema](database_schema.md)
- [Configuration](configuration.md)
