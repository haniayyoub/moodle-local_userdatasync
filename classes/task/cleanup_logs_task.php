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

namespace local_userdatasync\task;

defined('MOODLE_INTERNAL') || die();

use core\task\scheduled_task;

/**
 * Scheduled task for pruning old synchronization logs.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class cleanup_logs_task extends scheduled_task {
    /**
     * Return task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_cleanup_logs', 'local_userdatasync');
    }

    /**
     * Execute the scheduled task.
     *
     * @return void
     */
    public function execute(): void {
        global $DB;

        $retentiondays = (int)get_config('local_userdatasync', 'retentiondays');
        if ($retentiondays <= 0) {
            mtrace(get_string('task_cleanup_disabled', 'local_userdatasync'));
            return;
        }

        $cutoff = time() - ($retentiondays * DAYSECS);
        $params = ['cutoff' => $cutoff];
        $count = $DB->count_records_select('local_userdatasync_log', 'timecreated < :cutoff', $params);
        $DB->delete_records_select('local_userdatasync_log', 'timecreated < :cutoff', $params);
        mtrace(get_string('task_cleanup_completed', 'local_userdatasync', (int)$count));
    }
}
