# Field Mapping

Field mapping controls which external source columns update which Moodle user profile fields.

## Supported Moodle User Fields

The synchronization service supports these standard Moodle user fields:

- `firstname`
- `lastname`
- `email`
- `phone1`
- `phone2`
- `department`
- `institution`

## Supported Custom Profile Fields

The implemented mappings include:

- `profile_field_faculty`
- `profile_field_major`
- `profile_field_degree`

Only configured mappings are processed.

## Matching Logic

For each external record:

1. The remote matching field value is read from the external record.
2. The plugin checks uppercase, configured-case, lowercase, and case-insensitive variants.
3. Empty remote matching values are skipped.
4. Moodle searches the `user` table using the configured local matching field and `deleted = 0`.
5. If no Moodle user is found, the record is skipped and logged.
6. If a user is found, mapped fields are evaluated.

Supported local matching fields:

- `id`
- `username`
- `idnumber`
- `email`

## Validation Rules

### Email

When `validateemail` is enabled, mapped `email` values must pass Moodle email validation.

### Phone

When `validatemobilephone` is enabled, mapped `phone1` and `phone2` values are checked with a basic international phone pattern.

Allowed phone format after removing spaces, hyphens, parentheses, and periods:

```text
optional + followed by 7 to 20 digits
```

## Empty-value Handling

When `skipemptyvalues` is enabled, empty external values are skipped and do not overwrite Moodle values.

If disabled, an empty external value can be processed according to the remaining update settings.

## Change Handling

When `updateonlyifchanged` is enabled, a mapped field is skipped if the external value matches the current Moodle value.

When `allowoverwrite` is disabled, existing non-empty Moodle values are not overwritten.

When `dryrun` is enabled, intended updates are logged but not written.

## Example Configuration

External source columns:

```text
STUDENT_ID
FIRST_NAME
LAST_NAME
EMAIL_ADDRESS
MOBILE_PHONE
COLLEGE
MAJOR
```

Moodle settings:

```text
localmatchingfield: idnumber
remotematchingfield: STUDENT_ID

map_firstname: FIRST_NAME
map_lastname: LAST_NAME
map_email: EMAIL_ADDRESS
map_phone1: MOBILE_PHONE
map_department: COLLEGE
map_profile_field_major: MAJOR
```

Recommended first-run behavior:

```text
dryrun: enabled
skipemptyvalues: enabled
updateonlyifchanged: enabled
validateemail: enabled
validatemobilephone: enabled
```

## Related Documentation

- [Configuration](configuration.md)
- [Scheduled tasks](scheduled_tasks.md)
- [Troubleshooting](troubleshooting.md)
