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

namespace local_userdatasync;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

use stdClass;

/**
 * Synchronization service.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_service {
    /** @var logger */
    private $logger;

    /** @var array<string, int> */
    private $stats = [
        'processed' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
    ];

    /**
     * Run synchronization.
     *
     * @return void
     */
    public function run(): void {
        $config = get_config('local_userdatasync');
        $this->logger = new logger((string)($config->loglevel ?? 'info'));
        $runstarted = microtime(true);

        if (empty($config->enabled)) {
            $this->logger->log(null, null, null, null, null, 'skipped_empty',
                get_string('log_plugin_disabled', 'local_userdatasync'), 'info');
            $this->persist_run_stats();
            return;
        }

        try {
            $mappings = $this->get_field_mappings($config);
            $columns = [$config->remotematchingfield];
            foreach ($mappings as $mapping) {
                $columns[] = $mapping['column'];
            }

            $client = new external_db_client($config);
            $batchsize = $this->get_batch_size($config);
            $maxruntime = $this->get_max_runtime($config);
            $enablepagination = $this->is_pagination_enabled($config);

            if (class_exists('\core_php_time_limit')) {
                \core_php_time_limit::raise($maxruntime > 0 ? $maxruntime + 60 : 0);
            }

            mtrace(get_string('progress_sync_started', 'local_userdatasync', (object)[
                'batchsize' => $batchsize,
                'maxruntime' => $maxruntime,
                'enablepagination' => (int)$enablepagination,
            ]));

            $client->fetch_records_in_batches(
                $columns,
                $batchsize,
                function(stdClass $record, int $batchnumber, int $offset) use ($config, $mappings): void {
                    $this->stats['processed']++;

                    try {
                        $this->process_record($config, $record, $mappings);
                    } catch (\Throwable $e) {
                        $this->stats['errors']++;
                        $this->logger->log(null, null, null, null, null, 'error',
                            get_string('log_single_record_failed', 'local_userdatasync', (object)[
                                'batch' => $batchnumber,
                                'offset' => $offset,
                                'errortype' => get_class($e),
                            ]),
                            'error');
                    }
                },
                $maxruntime,
                $enablepagination,
                function(array $progress) use ($runstarted): void {
                    $this->log_batch_progress($progress, microtime(true) - $runstarted);
                }
            );

            mtrace(get_string('progress_sync_completed', 'local_userdatasync', (object)[
                'processed' => $this->stats['processed'],
                'skipped' => $this->stats['skipped'],
                'errors' => $this->stats['errors'],
                'updated' => $this->stats['updated'],
                'elapsed' => $this->format_seconds(microtime(true) - $runstarted),
                'memory' => $this->format_bytes(memory_get_usage(true)),
            ]));
        } catch (\Throwable $e) {
            $this->stats['errors']++;
            $this->logger->log(null, null, null, null, null, 'error',
                get_string('log_sync_failed', 'local_userdatasync', $e->getMessage()), 'error');
        }

        $this->persist_run_stats();
    }

    /**
     * Process one external record.
     *
     * @param stdClass $config
     * @param stdClass $record
     * @param array $mappings
     * @return void
     */
    private function process_record(stdClass $config, stdClass $record, array $mappings): void {
        global $DB;

        $remotematchingfield = (string)$config->remotematchingfield;
        $localmatchingfield = (string)$config->localmatchingfield;
        $fields = get_object_vars($record);
        $remotevalue = '';
        $matchingfieldexists = false;

        $candidates = [
            strtoupper($remotematchingfield),
            $remotematchingfield,
            strtolower($remotematchingfield),
        ];

        foreach ($candidates as $candidate) {
            if (!array_key_exists($candidate, $fields)) {
                continue;
            }

            $matchingfieldexists = true;
            $candidatevalue = $fields[$candidate];

            if ($candidatevalue === false || $candidatevalue === null) {
                continue;
            }

            $candidatevalue = trim((string)$candidatevalue);

            if ($candidatevalue !== '') {
                $remotevalue = $candidatevalue;
                break;
            }
        }

        if (!$matchingfieldexists) {
            foreach ($fields as $key => $value) {
                if (strcasecmp((string)$key, $remotematchingfield) !== 0) {
                    continue;
                }
                $matchingfieldexists = true;
                if ($value !== false && $value !== null && trim((string)$value) !== '') {
                    $remotevalue = trim((string)$value);
                }
                break;
            }
        }

        if ($remotevalue === '') {
            $this->stats['skipped']++;
            $message = $matchingfieldexists ? get_string('log_remote_matching_empty', 'local_userdatasync') :
                get_string('log_remote_matching_missing', 'local_userdatasync');
            $this->logger->log(null, null, $localmatchingfield, null, null, 'skipped_empty',
                $message, 'warning');
            return;
        }

        $user = $DB->get_record('user', [$localmatchingfield => $remotevalue, 'deleted' => 0], '*', IGNORE_MULTIPLE);
        if (!$user) {
            $this->stats['skipped']++;
            $this->logger->log(null, null, $localmatchingfield, null, null, 'user_not_found',
                get_string('log_user_not_found', 'local_userdatasync'), 'warning');
            return;
        }

        profile_load_custom_fields($user);

        $standardupdates = clone $user;
        $customupdates = new stdClass();
        $customupdates->id = $user->id;

        $hasstandardchanges = false;
        $hascustomchanges = false;
        $dryrun = !empty($config->dryrun);
        $skipempty = !empty($config->skipemptyvalues);
        $updateonlyifchanged = !empty($config->updateonlyifchanged);
        $allowoverwrite = !empty($config->allowoverwrite);

        foreach ($mappings as $mapping) {
            $fieldname = $mapping['field'];
            $column = $mapping['column'];
            $rawvalue = trim($this->get_record_value($record, $column));

            if ($rawvalue === '' && $skipempty) {
                $this->stats['skipped']++;
                $this->logger->log($user->id, null, $fieldname, null, null, 'skipped_empty',
                    get_string('log_external_value_empty', 'local_userdatasync', $fieldname), 'debug');
                continue;
            }

            if (!$this->validate_value($config, $fieldname, $rawvalue, $user)) {
                continue;
            }

            $currentvalue = $this->get_current_value($user, $fieldname);
            if ($updateonlyifchanged && (string)$currentvalue === $rawvalue) {
                $this->stats['skipped']++;
                $this->logger->log($user->id, null, $fieldname, null, null,
                    'skipped_same', get_string('log_value_unchanged', 'local_userdatasync', $fieldname), 'debug');
                continue;
            }

            if (!$allowoverwrite && trim((string)$currentvalue) !== '') {
                $this->stats['skipped']++;
                $this->logger->log($user->id, null, $fieldname, null, null,
                    'skipped_overwrite_disabled',
                    get_string('log_overwrite_disabled', 'local_userdatasync', $fieldname), 'info');
                continue;
            }

            if ($dryrun) {
                $this->stats['skipped']++;
                $this->logger->log($user->id, null, $fieldname, null, null,
                    'dry_run', get_string('log_dry_run_update', 'local_userdatasync', $fieldname), 'info');
                continue;
            }

            if ($mapping['custom']) {
                $customupdates->{'profile_field_' . $mapping['shortname']} = $rawvalue;
                $hascustomchanges = true;
            } else {
                $standardupdates->{$fieldname} = $rawvalue;
                $hasstandardchanges = true;
            }

            $this->stats['updated']++;
            $this->logger->log($user->id, null, $fieldname, null, null,
                'updated', get_string('log_prepared_update', 'local_userdatasync', $fieldname), 'info');
        }

        if ($hasstandardchanges) {
            user_update_user($standardupdates, false, false);
        }

        if ($hascustomchanges) {
            profile_save_data($customupdates);
        }
    }

    /**
     * Build configured field mappings.
     *
     * @param stdClass $config
     * @return array
     */
    private function get_field_mappings(stdClass $config): array {
        $supported = [
            'firstname',
            'lastname',
            'email',
            'phone1',
            'phone2',
            'department',
            'institution',
            'profile_field_faculty',
            'profile_field_major',
            'profile_field_degree',
        ];

        $mappings = [];
        foreach ($supported as $fieldname) {
            $column = trim((string)($config->{'map_' . $fieldname} ?? ''));
            if ($column === '') {
                continue;
            }

            $custom = $this->starts_with($fieldname, 'profile_field_');
            $shortname = $custom ? substr($fieldname, 14) : '';
            $mappings[] = [
                'field' => $fieldname,
                'column' => $column,
                'custom' => $custom,
                'shortname' => $shortname,
            ];
        }

        return $mappings;
    }

    /**
     * Read an external record value case-insensitively.
     *
     * @param stdClass $record
     * @param string $fieldname
     * @return string
     */
    private function get_record_value(stdClass $record, string $fieldname): string {
        $fieldname = trim($fieldname);
        $candidates = array_unique([
            $fieldname,
            strtolower($fieldname),
            strtoupper($fieldname),
        ]);

        foreach ($candidates as $candidate) {
            if ($candidate !== '' && property_exists($record, $candidate)) {
                $value = $record->{$candidate};
                if ($value === false || $value === null) {
                    return '';
                }
                return (string)$value;
            }
        }

        foreach (get_object_vars($record) as $key => $value) {
            if (strcasecmp((string)$key, $fieldname) !== 0) {
                continue;
            }
            if ($value === false || $value === null) {
                return '';
            }
            return (string)$value;
        }

        return '';
    }

    /**
     * Validate field value according to settings.
     *
     * @param stdClass $config
     * @param string $fieldname
     * @param string $value
     * @param stdClass $user
     * @return bool
     */
    private function validate_value(stdClass $config, string $fieldname, string $value, stdClass $user): bool {
        if ($fieldname === 'email' && !empty($config->validateemail) && !validate_email($value)) {
            $this->stats['errors']++;
            $this->logger->log($user->id, null, $fieldname, null, null, 'invalid_email',
                get_string('log_invalid_email', 'local_userdatasync', $fieldname), 'warning');
            return false;
        }

        if (in_array($fieldname, ['phone1', 'phone2'], true) &&
                !empty($config->validatemobilephone) &&
                !$this->is_valid_phone($value)) {
            $this->stats['errors']++;
            $this->logger->log($user->id, null, $fieldname, null, null, 'invalid_phone',
                get_string('log_invalid_phone', 'local_userdatasync', $fieldname), 'warning');
            return false;
        }

        return true;
    }

    /**
     * Return validated batch size.
     *
     * @param stdClass $config
     * @return int
     */
    private function get_batch_size(stdClass $config): int {
        $batchsize = (int)($config->batchsize ?? 100);
        return min(10000, max(1, $batchsize));
    }

    /**
     * Return validated maximum runtime.
     *
     * A zero value leaves the scheduled task bounded by Moodle/PHP runtime
     * controls only. Positive values are checked between batches so one large
     * Oracle page is allowed to complete and the next cron run can resume a
     * fresh full scan without corrupting the current batch.
     *
     * @param stdClass $config
     * @return int
     */
    private function get_max_runtime(stdClass $config): int {
        $maxruntime = (int)($config->maxruntime ?? 0);
        return min(86400, max(0, $maxruntime));
    }

    /**
     * Return whether offset pagination is enabled.
     *
     * @param stdClass $config
     * @return bool
     */
    private function is_pagination_enabled(stdClass $config): bool {
        return !isset($config->enablepagination) || !empty($config->enablepagination);
    }

    /**
     * Emit Moodle-safe progress for one completed external batch.
     *
     * @param array $progress
     * @param float $elapsed
     * @return void
     */
    private function log_batch_progress(array $progress, float $elapsed): void {
        mtrace(get_string('progress_batch_completed', 'local_userdatasync', (object)[
            'batch' => (int)$progress['batch'],
            'offset' => (int)$progress['offset'],
            'rows' => (int)$progress['rows'],
            'processed' => $this->stats['processed'],
            'skipped' => $this->stats['skipped'],
            'errors' => $this->stats['errors'],
            'batchtime' => $this->format_seconds((float)$progress['elapsed']),
            'elapsed' => $this->format_seconds($elapsed),
            'memory' => $this->format_bytes((int)$progress['memory']),
            'memorypeak' => $this->format_bytes((int)$progress['memorypeak']),
        ]));
    }

    /**
     * Format seconds for CLI progress output.
     *
     * @param float $seconds
     * @return string
     */
    private function format_seconds(float $seconds): string {
        return number_format($seconds, 2, '.', '') . 's';
    }

    /**
     * Format bytes for CLI progress output.
     *
     * @param int $bytes
     * @return string
     */
    private function format_bytes(int $bytes): string {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2, '.', '') . 'GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, '.', '') . 'MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2, '.', '') . 'KB';
        }

        return $bytes . 'B';
    }

    /**
     * Get current Moodle value for a field.
     *
     * @param stdClass $user
     * @param string $fieldname
     * @return string
     */
    private function get_current_value(stdClass $user, string $fieldname): string {
        if ($this->starts_with($fieldname, 'profile_field_')) {
            return (string)($user->{$fieldname} ?? '');
        }

        return (string)($user->{$fieldname} ?? '');
    }

    /**
     * Check whether a string starts with a prefix.
     *
     * Kept local instead of the PHP 8 prefix helper so the plugin remains compatible
     * with Moodle versions that may run on PHP 7.4.
     *
     * @param string $value
     * @param string $prefix
     * @return bool
     */
    private function starts_with(string $value, string $prefix): bool {
        return substr($value, 0, strlen($prefix)) === $prefix;
    }

    /**
     * Basic phone validation.
     *
     * @param string $value
     * @return bool
     */
    private function is_valid_phone(string $value): bool {
        $compact = preg_replace('/[\s\-\(\)\.]/', '', $value);
        if ($compact === null || $compact === '') {
            return false;
        }
        return (bool)preg_match('/^\+?[0-9]{7,20}$/', $compact);
    }

    /**
     * Persist stats from the last run.
     *
     * @return void
     */
    private function persist_run_stats(): void {
        set_config('lastsynctime', time(), 'local_userdatasync');
        set_config('lastprocessed', $this->stats['processed'], 'local_userdatasync');
        set_config('lastupdated', $this->stats['updated'], 'local_userdatasync');
        set_config('lastskipped', $this->stats['skipped'], 'local_userdatasync');
        set_config('lasterrors', $this->stats['errors'], 'local_userdatasync');
    }
}
