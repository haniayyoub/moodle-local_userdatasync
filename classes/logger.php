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

/**
 * Sync logger.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class logger {
    /** @var array<string, int> */
    private const LEVELS = [
        'error' => 1,
        'warning' => 2,
        'info' => 3,
        'debug' => 4,
    ];

    /** @var string */
    private $loglevel;

    /**
     * Constructor.
     *
     * @param string $loglevel
     */
    public function __construct(string $loglevel = 'info') {
        $this->loglevel = array_key_exists($loglevel, self::LEVELS) ? $loglevel : 'info';
    }

    /**
     * Insert a sync log record and optionally emit mtrace output.
     *
     * Usernames and values are deliberately not persisted or emitted. Older
     * installations may still have these columns populated by previous plugin
     * versions, so the table shape is preserved for backward compatibility.
     *
     * @param int|null $userid
     * @param string|null $username
     * @param string|null $fieldname
     * @param string|null $oldvalue
     * @param string|null $newvalue
     * @param string $status
     * @param string $message
     * @param string $level
     * @return void
     */
    public function log(
        ?int $userid,
        ?string $username,
        ?string $fieldname,
        ?string $oldvalue,
        ?string $newvalue,
        string $status,
        string $message,
        string $level = 'info'
    ): void {
        global $DB;

        $record = (object)[
            'userid' => $userid ?? 0,
            'username' => null,
            'fieldname' => $fieldname,
            'oldvalue' => null,
            'newvalue' => null,
            'status' => $status,
            'message' => $this->safe_message($message),
            'timecreated' => time(),
        ];

        $DB->insert_record('local_userdatasync_log', $record);

        if ($this->should_emit($level)) {
            mtrace(get_string('log_mtrace_entry', 'local_userdatasync', (object)[
                'status' => $status,
                'message' => $record->message,
            ]));
        }
    }

    /**
     * Return a log message safe for task output and persisted audit logs.
     *
     * @param string $message
     * @return string
     */
    private function safe_message(string $message): string {
        $message = trim($message);
        $message = preg_replace('/[\r\n\t]+/', ' ', $message);
        return $message === null ? '' : $message;
    }

    /**
     * Determine whether a message should be emitted.
     *
     * @param string $level
     * @return bool
     */
    private function should_emit(string $level): bool {
        $levelweight = self::LEVELS[$level] ?? self::LEVELS['info'];
        $configuredweight = self::LEVELS[$this->loglevel] ?? self::LEVELS['info'];
        return $levelweight <= $configuredweight;
    }
}
