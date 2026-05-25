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

use moodle_exception;

/**
 * External database client for user data sync.
 *
 * Uses Moodle's bundled ADOdb layer, matching the working enrol_database
 * external connection style and avoiding Moodle native Oracle DML package
 * requirements for external Oracle sources.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external_db_client {
    /** Minimum supported page size for external reads. */
    private const MIN_BATCH_SIZE = 1;

    /** Maximum supported page size. Keep batches bounded for Moodle cron memory safety. */
    private const MAX_BATCH_SIZE = 10000;

    /** @var object|null ADOConnection instance */
    private $db = null;

    /** @var \stdClass */
    private $config;

    /**
     * Constructor.
     *
     * @param \stdClass $config
     */
    public function __construct(\stdClass $config) {
        $this->config = $config;
    }

    /**
     * Close open external database connection.
     */
    public function __destruct() {
        if ($this->db !== null && method_exists($this->db, 'Close')) {
            $this->db->Close();
        }
    }

    /**
     * Connect to the external database.
     *
     * @return void
     */
    public function connect(): void {
        global $CFG;

        if ($this->db !== null && method_exists($this->db, 'IsConnected') && $this->db->IsConnected()) {
            return;
        }

        $configureddbtype = trim((string)($this->config->dbtype ?? ''));
        $dbtype = $this->normalize_dbtype($configureddbtype);
        $dbhost = trim((string)($this->config->dbhost ?? ''));
        $dbname = trim((string)($this->config->dbname ?? ''));
        $dbuser = trim((string)($this->config->dbuser ?? ''));
        $dbpass = (string)($this->config->dbpass ?? '');

        if ($configureddbtype === '' || $dbhost === '' || $dbname === '' || $dbuser === '') {
            throw new moodle_exception('missingconfig', 'error', '', null,
                get_string('missingexternaldbconfig', 'local_userdatasync'));
        }

        if ($dbtype === '') {
            throw new moodle_exception('invaliddbtype', 'local_userdatasync', '', $configureddbtype, $configureddbtype);
        }

        require_once($CFG->libdir . '/adodb/adodb.inc.php');

        try {
            if ($dbtype === 'oci8' || $dbtype === 'oci8po') {
                putenv('NLS_LANG=AMERICAN_AMERICA.AL32UTF8');
            }

            $this->db = \ADONewConnection($dbtype);
        } catch (\Throwable $e) {
            $message = get_string('missingdbdriverdetail', 'local_userdatasync', $dbtype) . ' ' .
                $this->safe_error_message($e->getMessage());
            throw new moodle_exception('missingdbdriver', 'local_userdatasync', '', $message, $message);
        }

        if (empty($this->db)) {
            $message = get_string('missingdbdriverdetail', 'local_userdatasync', $dbtype);
            throw new moodle_exception('missingdbdriver', 'local_userdatasync', '', $message, $message);
        }

        try {
            if (method_exists($this->db, 'SetCharSet')) {
                $this->db->SetCharSet('utf8');
            }

            if (!$this->db->IsConnected()) {
                $connected = $this->db->Connect($dbhost, $dbuser, $dbpass, $dbname, true);
                if (!$connected) {
                    $this->throw_safe_connection_exception($this->db->ErrorMsg());
                }
            }

            if ($dbtype === 'oci8' || $dbtype === 'oci8po') {
                $this->db->Execute("ALTER SESSION SET NLS_LANGUAGE='AMERICAN' NLS_TERRITORY='AMERICA'");
            }

            $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
        } catch (\Throwable $e) {
            $this->throw_safe_connection_exception($e->getMessage());
        }
    }

    /**
     * Fetch external records.
     *
     * This compatibility helper intentionally returns an array and should only be
     * used for small diagnostic reads. Scheduled synchronisation uses
     * fetch_records_in_batches() so large Oracle sources are never loaded into
     * PHP memory at once.
     *
     * @param array $columns
     * @param int $limit
     * @return array
     */
    public function get_records(array $columns, int $limit): array {
        $records = [];
        $this->fetch_records_in_batches($columns, $limit, function(\stdClass $record) use (&$records): void {
            $records[] = $record;
        }, 0, false);

        return $records;
    }

    /**
     * Process external records directly from paginated ADOdb recordsets.
     *
     * @param array $columns
     * @param int $batchsize
     * @param callable $callback
     * @param int $maxruntime Maximum runtime in seconds. Zero means no plugin-level limit.
     * @param bool $enablepagination When disabled, process one bounded batch for legacy diagnostics.
     * @param callable|null $batchcallback Receives safe per-batch progress metadata.
     * @return void
     */
    public function fetch_records_in_batches(
        array $columns,
        int $batchsize,
        callable $callback,
        int $maxruntime = 0,
        bool $enablepagination = true,
        ?callable $batchcallback = null
    ): void {
        $this->connect();

        $tablename = $this->get_safe_table_name();
        $safecolumns = $this->get_safe_column_list($columns);
        // Offset pagination must be deterministic on Oracle. The service passes
        // the remote matching field first, so this orders each page by the
        // institutional key without introducing another admin setting.
        $sql = 'SELECT ' . implode(', ', $safecolumns) . ' FROM ' . $tablename .
            ' ORDER BY ' . $safecolumns[0];
        $batchsize = $this->validate_batch_size($batchsize);
        $maxruntime = max(0, $maxruntime);
        $offset = 0;
        $batchnumber = 0;
        $totalrows = 0;
        $started = time();

        while (true) {
            if ($maxruntime > 0 && (time() - $started) >= $maxruntime) {
                mtrace(get_string('progress_max_runtime_reached', 'local_userdatasync', (object)[
                    'offset' => $offset,
                    'batch' => $batchnumber + 1,
                    'processed' => $totalrows,
                ]));
                break;
            }

            $batchnumber++;
            $batchstarted = microtime(true);
            $rowsinbatch = 0;

            mtrace(get_string('progress_fetching_batch', 'local_userdatasync', (object)[
                'offset' => $offset,
                'limit' => $batchsize,
                'batch' => $batchnumber,
            ]));

            $rs = $this->db->SelectLimit($sql, $batchsize, $offset);
            if (!$rs || !is_object($rs)) {
                $this->throw_safe_connection_exception($this->db->ErrorMsg());
            }

            try {
                while (!$rs->EOF) {
                    $record = $this->record_from_fields($rs->fields);
                    $callback($record, $batchnumber, $offset);
                    $rowsinbatch++;
                    $totalrows++;
                    $rs->MoveNext();
                    unset($record);
                }
            } finally {
                if (is_object($rs) && method_exists($rs, 'Close')) {
                    $rs->Close();
                }
            }

            $batchtime = microtime(true) - $batchstarted;
            if ($batchcallback !== null) {
                $batchcallback([
                    'batch' => $batchnumber,
                    'offset' => $offset,
                    'limit' => $batchsize,
                    'rows' => $rowsinbatch,
                    'totalrows' => $totalrows,
                    'elapsed' => $batchtime,
                    'memory' => memory_get_usage(true),
                    'memorypeak' => memory_get_peak_usage(true),
                ]);
            }

            gc_collect_cycles();

            if ($rowsinbatch === 0 || !$enablepagination) {
                break;
            }

            if ($offset > PHP_INT_MAX - $batchsize) {
                mtrace(get_string('progress_pagination_overflow', 'local_userdatasync'));
                break;
            }

            $offset += $batchsize;
        }
    }

    /**
     * Backwards-compatible alias for older local code.
     *
     * @param array $columns
     * @param int $batchsize
     * @param callable $callback
     * @return void
     */
    public function process_records(array $columns, int $batchsize, callable $callback): void {
        $this->fetch_records_in_batches($columns, $batchsize, $callback);
    }

    /**
     * Test external DB connectivity and table readability.
     *
     * @return array
     */
    public function test_connection(): array {
        $this->connect();

        $tablename = $this->get_safe_table_name();
        $sql = 'SELECT COUNT(*) AS recordcount FROM ' . $tablename;
        $rs = $this->db->Execute($sql);
        if (!$rs) {
            $this->throw_safe_connection_exception($this->db->ErrorMsg());
        }

        $row = $rs->FetchRow();
        $rs->Close();
        $recordcount = 0;
        if (is_array($row)) {
            $row = array_change_key_case($row, CASE_LOWER);
            $recordcount = (int)($row['recordcount'] ?? reset($row));
        }

        return [
            'table' => $tablename,
            'countchecked' => true,
            'recordcount' => $recordcount,
            'connectionmethod' => $this->get_connection_method(),
            'normalizeddbtype' => $this->get_normalized_dbtype(),
        ];
    }

    /**
     * Get the normalized ADOdb DB type for diagnostics.
     *
     * @return string
     */
    public function get_normalized_dbtype(): string {
        return $this->normalize_dbtype((string)($this->config->dbtype ?? ''));
    }

    /**
     * Get external DB connection method label for diagnostics.
     *
     * @return string
     */
    public function get_connection_method(): string {
        return 'ADOdb';
    }

    /**
     * Normalize configured external DB type aliases to ADOdb driver names.
     *
     * @param string $dbtype
     * @return string Empty string means unsupported DB type.
     */
    private function normalize_dbtype(string $dbtype): string {
        $dbtype = strtolower(trim($dbtype));
        $aliases = [
            'oracle' => 'oci8',
            'oci8' => 'oci8',
            'oci' => 'oci8',
            'oci8po' => 'oci8po',
            'mysqli' => 'mysqli',
            'mariadb' => 'mysqli',
            'mysql' => 'mysqli',
            'pgsql' => 'postgres',
            'postgres' => 'postgres',
            'postgresql' => 'postgres',
            'sqlsrv' => 'mssqlnative',
            'mssql' => 'mssqlnative',
            'mssqlnative' => 'mssqlnative',
        ];

        return $aliases[$dbtype] ?? '';
    }

    /**
     * Validate and return configured external table name.
     *
     * @return string
     */
    private function get_safe_table_name(): string {
        $tablename = trim((string)($this->config->dbtable ?? ''));
        if ($tablename === '') {
            throw new moodle_exception('missingconfig', 'error', '', null,
                get_string('missingexternaltable', 'local_userdatasync'));
        }

        return $this->get_safe_identifier($tablename, get_string('external_table', 'local_userdatasync'));
    }

    /**
     * Validate and de-duplicate configured external column names.
     *
     * @param array $columns
     * @return array
     */
    private function get_safe_column_list(array $columns): array {
        $uniquecolumns = array_values(array_unique(array_filter($columns)));
        if (empty($uniquecolumns)) {
            throw new moodle_exception('missingconfig', 'error', '', null,
                get_string('missingexternalcolumns', 'local_userdatasync'));
        }

        return array_map(function(string $column): string {
            return $this->get_safe_identifier($column, get_string('external_column', 'local_userdatasync'));
        }, $uniquecolumns);
    }

    /**
     * Validate runtime batch size defensively for non-admin callers.
     *
     * @param int $batchsize
     * @return int
     */
    private function validate_batch_size(int $batchsize): int {
        return min(self::MAX_BATCH_SIZE, max(self::MIN_BATCH_SIZE, $batchsize));
    }

    /**
     * Validate configured table and column identifiers before placing them in SQL.
     *
     * @param string $identifier
     * @param string $label
     * @return string
     */
    private function get_safe_identifier(string $identifier, string $label): string {
        $identifier = trim($identifier);
        $part = '(?:"[A-Za-z][A-Za-z0-9_$#]*"|[A-Za-z][A-Za-z0-9_$#]*)';
        if ($identifier === '' || !preg_match('/^' . $part . '(?:\.' . $part . ')*$/', $identifier)) {
            throw new moodle_exception('invalidexternalidentifier', 'local_userdatasync', '', $label . ': ' . $identifier);
        }

        return $identifier;
    }

    /**
     * Build a record from ADOdb fields without logging personal values.
     *
     * @param mixed $fields
     * @return \stdClass
     */
    private function record_from_fields($fields): \stdClass {
        $record = new \stdClass();

        if (!is_array($fields)) {
            return $record;
        }

        foreach ($fields as $key => $value) {
            if (is_int($key)) {
                continue;
            }

            $field = trim((string)$key);
            if ($field === '') {
                continue;
            }

            if ($value === false || $value === null) {
                $value = '';
            } else if (is_scalar($value)) {
                $value = trim((string)$value);
            } else {
                $value = '';
            }

            $record->{$field} = $this->decode_value($value);

            $lowerfield = strtolower($field);
            if ($lowerfield !== $field && !property_exists($record, $lowerfield)) {
                $record->{$lowerfield} = $record->{$field};
            }
        }

        return $record;
    }

    /**
     * Convert one external DB value to UTF-8 according to configured dbencoding.
     *
     * @param string $value
     * @return string
     */
    private function decode_value(string $value): string {
        $dbencoding = trim((string)($this->config->dbencoding ?? ''));
        $normalized = strtolower(str_replace(['-', '_'], '', $dbencoding));
        if ($value !== '' && $dbencoding !== '' && $normalized !== 'utf8' && $normalized !== 'utf8mb4') {
            return \core_text::convert($value, $dbencoding, 'utf-8');
        }

        return $value;
    }

    /**
     * Throw a safe, administrator-readable connection exception.
     *
     * @param string $message
     * @return void
     */
    private function throw_safe_connection_exception(string $message): void {
        $safeerror = $this->safe_error_message($message);
        $message = get_string('externaldbconnectfailed_detail', 'local_userdatasync') . ' ' . $safeerror;
        throw new moodle_exception('externaldbconnectfailed', 'local_userdatasync', '', $message, $message);
    }

    /**
     * Remove configured password from any exception text before displaying or logging it.
     *
     * @param string $message
     * @return string
     */
    private function safe_error_message(string $message): string {
        $dbpass = (string)($this->config->dbpass ?? '');
        if ($dbpass !== '') {
            $message = str_replace($dbpass, '********', $message);
        }

        return $message;
    }
}
