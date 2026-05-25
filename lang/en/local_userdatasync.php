<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings for local_userdatasync.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'User Data Synchronization (External Source)';
$string['task_sync_user_data'] = 'Synchronize user profile data from external source';
$string['task_cleanup_logs'] = 'Clean up old user data synchronization logs';
$string['task_sync_started'] = '[local_userdatasync][task] Scheduled synchronization task started.';
$string['task_sync_finished'] = '[local_userdatasync][task] Scheduled synchronization task finished.';
$string['task_cleanup_disabled'] = '[local_userdatasync][cleanup] Log retention disabled.';
$string['task_cleanup_completed'] = '[local_userdatasync][cleanup] Old log cleanup completed. Deleted records: {$a}';
$string['reportpage'] = 'User data synchronization report';
$string['testconnectionpage'] = 'Test external database connection';
$string['userdatasync:viewreport'] = 'View user data synchronization report';
$string['userdatasync:managesettings'] = 'Manage user data synchronization settings';
$string['local_userdatasync:viewreport'] = 'View user data synchronization report';
$string['local_userdatasync:managesettings'] = 'Manage user data synchronization settings';

$string['generalsettings'] = 'General settings';
$string['externaldbsettings'] = 'External database settings';
$string['matchingandmapping'] = 'Matching and field mapping';
$string['behavioursettings'] = 'Behaviour settings';

$string['enabled'] = 'Enable plugin';
$string['enabled_desc'] = 'If enabled, scheduled user profile synchronization can run.';
$string['dbtype'] = 'External database type';
$string['dbtype_desc'] = 'Database driver type for the external source.';
$string['dbhost'] = 'External database host';
$string['dbhost_desc'] = 'Hostname or IP address of the external database server.';
$string['dbname'] = 'External database name';
$string['dbname_desc'] = 'Database name for the external source.';
$string['dbuser'] = 'External database user';
$string['dbuser_desc'] = 'Username used to connect to the external database.';
$string['dbpass'] = 'External database password';
$string['dbpass_desc'] = 'Password used to connect to the external database. This is never written to logs.';
$string['dbencoding'] = 'External database encoding';
$string['dbencoding_desc'] = 'Character encoding used by the external database connection.';
$string['dbtable'] = 'External database table';
$string['dbtable_desc'] = 'Name of the external table that contains user profile data.';
$string['localmatchingfield'] = 'Local Moodle matching field';
$string['localmatchingfield_desc'] = 'Moodle user field used to match external records.';
$string['remotematchingfield'] = 'Remote matching field';
$string['remotematchingfield_desc'] = 'Column name in the external table used to match local Moodle users.';
$string['mapfield'] = 'Map external column for {$a}';
$string['mapfield_desc'] = 'Enter the external table column name used to update {$a}. Leave empty to disable syncing for this field.';
$string['skipemptyvalues'] = 'Skip empty values';
$string['skipemptyvalues_desc'] = 'If enabled, empty external values are ignored and do not overwrite Moodle data.';
$string['updateonlyifchanged'] = 'Update only if changed';
$string['updateonlyifchanged_desc'] = 'If enabled, updates are skipped when the external value matches the current Moodle value.';
$string['validateemail'] = 'Validate email';
$string['validateemail_desc'] = 'Validate external email values before updating Moodle.';
$string['validatemobilephone'] = 'Validate mobile phone';
$string['validatemobilephone_desc'] = 'Validate phone1 and phone2 values before updating Moodle.';
$string['allowoverwrite'] = 'Allow overwrite';
$string['allowoverwrite_desc'] = 'If enabled, non-empty external values may overwrite Moodle values when they differ.';
$string['dryrun'] = 'Dry-run mode';
$string['dryrun_desc'] = 'If enabled, log intended changes without writing updates to Moodle.';
$string['batchsize'] = 'Batch size';
$string['batchsize_desc'] = 'Number of external records fetched per paginated batch. Valid range: 1 to 10000.';
$string['batchsize_invalid'] = 'Batch size must be a whole number between 1 and 10000.';
$string['maxruntime'] = 'Maximum runtime';
$string['maxruntime_desc'] = 'Maximum synchronization runtime in seconds. Set to 0 to let Moodle and PHP scheduled task limits apply. Valid range: 0 to 86400.';
$string['maxruntime_invalid'] = 'Maximum runtime must be a whole number between 0 and 86400 seconds.';
$string['enablepagination'] = 'Enable paginated synchronization';
$string['enablepagination_desc'] = 'If enabled, the task repeatedly reads external records using ADOdb SelectLimit with an increasing offset until no rows remain.';
$string['retentiondays'] = 'Log retention days';
$string['retentiondays_desc'] = 'Number of days to keep synchronization logs. Set to 0 to disable cleanup.';
$string['retentiondays_invalid'] = 'Log retention days must be zero or a positive whole number.';
$string['loglevel'] = 'Log level';
$string['loglevel_desc'] = 'Controls the verbosity of task output written with mtrace.';
$string['loglevel_error'] = 'Error';
$string['loglevel_warning'] = 'Warning';
$string['loglevel_info'] = 'Info';
$string['loglevel_debug'] = 'Debug';

$string['statusfilter'] = 'Status';
$string['usernamefilter'] = 'Username';
$string['fieldnamefilter'] = 'Field name';
$string['datefromfilter'] = 'Date from';
$string['applyfilters'] = 'Apply filters';
$string['resetsyncfilters'] = 'Reset filters';
$string['testconnection'] = 'Test connection';
$string['testconnectiondesc'] = 'Verify that Moodle can connect to the configured external database and read the configured external table.';
$string['testconnectionlink_desc'] = 'Open the external database connection test page.';
$string['connectiontestsuccess'] = 'Connection test succeeded.';
$string['connectiontestfailed'] = 'Connection test failed.';
$string['connectiontestdetails'] = 'Connection test details';
$string['connectionmethod'] = 'Connection method';
$string['configuredtable'] = 'Configured external table';
$string['configureddbtype'] = 'Configured external DB type';
$string['normalizeddbtype'] = 'Normalized external DB type';
$string['configureddbhost'] = 'Configured external DB host';
$string['configureddbname'] = 'Configured external DB name';
$string['externaldbconnectfailed'] = 'External database connection failed: {$a}';
$string['externaldbconnectfailed_detail'] = 'Could not connect to the configured external database.';
$string['external_table'] = 'external table';
$string['external_column'] = 'external column';
$string['externalrecordcount'] = 'External table record count';
$string['invalidexternalidentifier'] = 'Invalid {$a}. Use a simple external identifier, quoted identifier, or dot-qualified identifier. Allowed characters are letters, numbers, underscores, dollar signs, and hash signs; identifiers must start with a letter unless quoted.';
$string['invalidexternalidentifier_setting'] = 'Use a simple external identifier, quoted identifier, or dot-qualified identifier. Allowed characters are letters, numbers, underscores, dollar signs, and hash signs; identifiers must start with a letter unless quoted.';
$string['invaliddbtype'] = 'Invalid external database type: {$a}. Supported values are oracle, oci8, oci8po, oci, mysqli, mariadb, mysql, pgsql, postgres, postgresql, sqlsrv, and mssql.';
$string['missingdbextensiondetail'] = 'The PHP extension "{$a->extension}" required for dbtype "{$a->dbtype}" is not loaded.';
$string['missingdbdriver'] = 'External database driver is unavailable: {$a}';
$string['missingdbdriverdetail'] = 'Moodle could not load an ADOdb database driver for dbtype "{$a}". Check the configured dbtype and the required PHP database extension.';
$string['oraclemoodlelibmissing'] = 'Oracle Moodle support package is missing: {$a}';
$string['oraclemoodlelibmissing_detail'] = 'Oracle PL/SQL Moodle support package MOODLELIB is not installed. Ask the database administrator to execute /lib/dml/oci_native_moodle_package.sql on the Oracle database used by this connection.';
$string['recordcountchecked'] = 'External table COUNT check';
$string['recordcountchecked_desc'] = 'The plugin ran SELECT COUNT(*) against the configured external table.';
$string['recordsamplechecked'] = 'External table access check';
$string['recordsamplechecked_desc'] = 'The plugin attempted to read up to one record from the configured external table.';
$string['missingexternaldbconfig'] = 'Missing required external database configuration.';
$string['missingexternaltable'] = 'External table name is required.';
$string['missingexternalcolumns'] = 'No external columns configured for synchronization.';
$string['log_plugin_disabled'] = 'Plugin is disabled. Synchronization skipped.';
$string['log_single_record_failed'] = 'Single external record failed during batch {$a->batch} at offset {$a->offset}. Processing continued. Error type: {$a->errortype}';
$string['log_sync_failed'] = 'Synchronization failed: {$a}';
$string['log_remote_matching_empty'] = 'Remote matching field exists but value is empty.';
$string['log_remote_matching_missing'] = 'Remote matching field was not found in the external record.';
$string['log_user_not_found'] = 'No Moodle user found for the configured matching field.';
$string['log_external_value_empty'] = 'External value is empty for field {$a}.';
$string['log_value_unchanged'] = 'Value unchanged for field {$a}.';
$string['log_overwrite_disabled'] = 'Overwrite disabled for non-empty field {$a}.';
$string['log_dry_run_update'] = 'Dry run: would update field {$a}.';
$string['log_prepared_update'] = 'Prepared update for field {$a}.';
$string['log_invalid_email'] = 'Invalid email value for field {$a}.';
$string['log_invalid_phone'] = 'Invalid phone value for field {$a}.';
$string['log_mtrace_entry'] = '[local_userdatasync][{$a->status}] {$a->message}';
$string['progress_sync_started'] = '[local_userdatasync][progress] Synchronization started. batchsize={$a->batchsize} maxruntime={$a->maxruntime} enablepagination={$a->enablepagination}';
$string['progress_sync_completed'] = '[local_userdatasync][progress] Synchronization completed. processed={$a->processed} skipped={$a->skipped} errors={$a->errors} updated={$a->updated} elapsed={$a->elapsed} memory={$a->memory}';
$string['progress_batch_completed'] = '[local_userdatasync][progress] Completed external batch. batch={$a->batch} offset={$a->offset} rows={$a->rows} processed={$a->processed} skipped={$a->skipped} errors={$a->errors} batchtime={$a->batchtime} elapsed={$a->elapsed} memory={$a->memory} memorypeak={$a->memorypeak}';
$string['progress_max_runtime_reached'] = '[local_userdatasync][progress] Max runtime reached before next batch. offset={$a->offset} batch={$a->batch} processed={$a->processed}';
$string['progress_fetching_batch'] = '[local_userdatasync][progress] Fetching external batch. offset={$a->offset} limit={$a->limit} batch={$a->batch}';
$string['progress_pagination_overflow'] = '[local_userdatasync][progress] Pagination stopped before integer offset overflow.';
$string['lastsynctime'] = 'Last sync time';
$string['totalprocessed'] = 'Total records processed';
$string['updatedrecords'] = 'Updated records';
$string['skippedrecords'] = 'Skipped records';
$string['errorrecords'] = 'Error records';
$string['recentsynclogs'] = 'Recent sync logs';
$string['nosynclogsfound'] = 'No sync logs found.';
$string['notyetrun'] = 'Not yet run';
$string['log_time'] = 'Time';
$string['log_userid'] = 'User ID';
$string['log_fieldname'] = 'Field';
$string['log_status'] = 'Status';
$string['log_message'] = 'Message';

$string['status_updated'] = 'updated';
$string['status_skipped_empty'] = 'skipped_empty';
$string['status_skipped_same'] = 'skipped_same';
$string['status_invalid_email'] = 'invalid_email';
$string['status_invalid_phone'] = 'invalid_phone';
$string['status_user_not_found'] = 'user_not_found';
$string['status_error'] = 'error';
$string['status_dry_run'] = 'dry_run';
$string['status_skipped_overwrite_disabled'] = 'skipped_overwrite_disabled';

$string['privacy:metadata'] = 'The local_userdatasync plugin reads configured user profile fields from an external database, writes selected mapped values to Moodle user profiles, and stores synchronization logs for audit and troubleshooting.';
$string['privacy:metadata:external_database'] = 'Moodle reads and processes configured user profile fields from an external database table or view. The plugin does not export Moodle user data to this external source.';
$string['privacy:metadata:external_database:username'] = 'The username or configured matching value read from the external source to identify a Moodle user where this field is mapped.';
$string['privacy:metadata:external_database:firstname'] = 'The first name value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:lastname'] = 'The last name value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:email'] = 'The email address value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:phone1'] = 'The primary phone value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:phone2'] = 'The secondary phone value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:department'] = 'The department value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:institution'] = 'The institution value read from the external source where this field is mapped.';
$string['privacy:metadata:external_database:profile_field'] = 'Configured mapped custom profile field values read from the external source, such as faculty, major, degree, or other mapped profile fields where applicable.';
$string['privacy:metadata:log'] = 'Synchronization log records used to audit external profile synchronization. Current logs avoid storing personal values and normally contain user id, field name, status, safe message text, and timestamps.';
$string['privacy:metadata:log:userid'] = 'The Moodle user ID associated with the sync event.';
$string['privacy:metadata:log:username'] = 'Legacy username field retained for upgrade compatibility; current logging does not populate it.';
$string['privacy:metadata:log:fieldname'] = 'The Moodle field or custom profile field evaluated.';
$string['privacy:metadata:log:oldvalue'] = 'Legacy previous-value field retained for upgrade compatibility; current logging does not populate it.';
$string['privacy:metadata:log:newvalue'] = 'Legacy new-value field retained for upgrade compatibility; current logging does not populate it.';
$string['privacy:metadata:log:status'] = 'The sync result status.';
$string['privacy:metadata:log:message'] = 'Additional information about the sync result.';
$string['privacy:metadata:log:timecreated'] = 'The time when the log entry was created.';
$string['privacy:path:logs'] = 'Synchronization logs';
