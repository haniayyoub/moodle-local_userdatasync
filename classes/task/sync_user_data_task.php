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
use local_userdatasync\sync_service;

/**
 * Scheduled task for user data synchronization.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_user_data_task extends scheduled_task {
    /**
     * Return task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('task_sync_user_data', 'local_userdatasync');
    }

    /**
     * Execute the scheduled task.
     *
     * @return void
     */
    public function execute(): void {
        if (class_exists('\core_php_time_limit')) {
            \core_php_time_limit::raise();
        }

        mtrace(get_string('task_sync_started', 'local_userdatasync'));
        $service = new sync_service();
        $service->run();
        mtrace(get_string('task_sync_finished', 'local_userdatasync'));
    }
}
