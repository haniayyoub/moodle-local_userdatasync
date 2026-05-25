# Security

## Authentication

Admin-facing pages require Moodle login.

The connection test page explicitly calls:

```php
require_login();
```

The report page also requires login before access.

## Capability Checks

The plugin defines these capabilities:

- `local/userdatasync:viewreport`
- `local/userdatasync:managesettings`

The report page requires:

```text
local/userdatasync:viewreport
```

The connection test page requires:

```text
moodle/site:config
local/userdatasync:managesettings
```

## Sesskey Validation

The external database connection test action validates the Moodle session key before running.

The page uses:

```php
data_submitted()
confirm_sesskey()
```

## POST-only Admin Action

The connection test action is rendered with Moodle's `single_button()` using POST.

A simple GET request displays the page but does not run the external connection test.

## SQL Injection Prevention

Moodle database access uses Moodle DML APIs and placeholders where raw SQL is required.

External table and column names are not accepted as arbitrary SQL fragments. They are validated as safe identifiers before being used in external SQL.

## External Identifier Validation

External table and column settings are validated using a restricted identifier pattern. The pattern permits simple identifiers, quoted identifiers, and dot-qualified identifiers with allowed characters.

Invalid identifiers are rejected before SQL construction.

## Credential Storage

External database credentials are stored in Moodle plugin configuration.

Administrators should:

- Restrict access to plugin settings.
- Use a dedicated read-only external database user.
- Avoid screenshots or support tickets containing secrets.
- Rotate credentials according to institutional policy.

The plugin masks the configured database password from displayed connection errors where possible.

## Audit Logging

Synchronization logs are used for audit and troubleshooting.

Current logging avoids storing usernames, old values, new values, and external matching values during normal operation. Logs normally contain user id, field name, status, safe messages, and timestamps.

## Related Documentation

- [Privacy](privacy.md)
- [Database schema](database_schema.md)
- [Configuration](configuration.md)
