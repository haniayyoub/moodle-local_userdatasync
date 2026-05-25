# Configuration

Plugin settings are available at:

```text
Site administration > Plugins > Local plugins > User Data Synchronization (External Source)
```

## General Settings

### Enable Plugin

Setting:

```text
local_userdatasync/enabled
```

When disabled, scheduled synchronization is skipped.

## External Database Settings

### Database Type

Setting:

```text
local_userdatasync/dbtype
```

Implemented aliases include:

- `oracle`, `oci`, `oci8` -> `oci8`
- `oci8po` -> `oci8po`
- `mysqli`, `mariadb`, `mysql` -> `mysqli`
- `pgsql`, `postgres`, `postgresql` -> `postgres`
- `sqlsrv`, `mssql`, `mssqlnative` -> `mssqlnative`

### External DB Host

Setting:

```text
local_userdatasync/dbhost
```

Hostname, IP address, or driver-specific host value.

### Database or Service Name

Setting:

```text
local_userdatasync/dbname
```

Database name, Oracle service name, or equivalent connection target.

### Username and Password

Settings:

```text
local_userdatasync/dbuser
local_userdatasync/dbpass
```

Use a dedicated read-only account restricted to the configured table or view.

### Encoding

Setting:

```text
local_userdatasync/dbencoding
```

Default:

```text
utf8mb4
```

### Table or View Name

Setting:

```text
local_userdatasync/dbtable
```

The external table or view. The value is validated as a safe identifier.

Best practice: use a database view that exposes only required columns.

## Matching Settings

### Local Matching Field

Setting:

```text
local_userdatasync/localmatchingfield
```

Supported values:

- `id`
- `username`
- `idnumber`
- `email`

### Remote Matching Field

Setting:

```text
local_userdatasync/remotematchingfield
```

Default:

```text
student_id
```

This external column is used to find the matching Moodle user.

## Field Mappings

Supported mapping settings:

```text
map_firstname
map_lastname
map_email
map_phone1
map_phone2
map_department
map_institution
map_profile_field_faculty
map_profile_field_major
map_profile_field_degree
```

Leave a mapping blank to disable synchronization for that field.

Example:

```text
localmatchingfield: idnumber
remotematchingfield: STUDENT_ID
map_firstname: FIRST_NAME
map_lastname: LAST_NAME
map_email: EMAIL_ADDRESS
map_phone1: MOBILE_PHONE
map_department: COLLEGE
```

## Sync Behavior Settings

### Skip Empty Values

Setting:

```text
local_userdatasync/skipemptyvalues
```

When enabled, empty external values do not overwrite Moodle values.

### Update Only if Changed

Setting:

```text
local_userdatasync/updateonlyifchanged
```

When enabled, unchanged values are skipped.

### Validate Email

Setting:

```text
local_userdatasync/validateemail
```

When enabled, mapped email values must pass Moodle email validation.

### Validate Mobile Phone

Setting:

```text
local_userdatasync/validatemobilephone
```

When enabled, `phone1` and `phone2` values are checked with the plugin's basic phone validator.

### Allow Overwrite

Setting:

```text
local_userdatasync/allowoverwrite
```

When disabled, existing non-empty Moodle values are not overwritten.

### Dry-run Mode

Setting:

```text
local_userdatasync/dryrun
```

When enabled, intended updates are logged but not written.

### Batch Size

Setting:

```text
local_userdatasync/batchsize
```

Valid range:

```text
1 to 10000
```

### Maximum Runtime

Setting:

```text
local_userdatasync/maxruntime
```

Valid range:

```text
0 to 86400 seconds
```

### Enable Pagination

Setting:

```text
local_userdatasync/enablepagination
```

When enabled, external records are read through ADOdb `SelectLimit()` using increasing offsets.

### Log Retention

Setting:

```text
local_userdatasync/retentiondays
```

Set to `0` to disable cleanup.

### Log Level

Setting:

```text
local_userdatasync/loglevel
```

Supported values:

- `error`
- `warning`
- `info`
- `debug`

## Related Documentation

- [Field mapping](field_mapping.md)
- [Scheduled tasks](scheduled_tasks.md)
- [Security](security.md)
