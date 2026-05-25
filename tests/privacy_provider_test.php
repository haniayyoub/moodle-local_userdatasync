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

use context_system;
use core_privacy\local\metadata\collection;
use local_userdatasync\privacy\provider;

/**
 * Tests for privacy metadata declarations.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \local_userdatasync\privacy\provider
 */
final class privacy_provider_test extends \advanced_testcase {
    /**
     * Provider returns Moodle privacy metadata.
     *
     * @return void
     */
    public function test_provider_declares_metadata_collection(): void {
        $collection = provider::get_metadata(new collection('local_userdatasync'));

        $this->assertInstanceOf(collection::class, $collection);
        $this->assertNotEmpty($collection->get_collection());
    }

    /**
     * Users with sync logs are reported in the system context.
     *
     * @return void
     */
    public function test_get_contexts_for_userid_reports_system_context_for_logs(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $context = context_system::instance();

        $DB->insert_record('local_userdatasync_log', (object)[
            'userid' => $user->id,
            'fieldname' => 'email',
            'status' => 'updated',
            'message' => 'test',
            'timecreated' => time(),
        ]);

        $contextlist = provider::get_contexts_for_userid($user->id);

        $this->assertContains($context->id, $contextlist->get_contextids());
    }

    /**
     * System-context deletion removes stored sync logs.
     *
     * @return void
     */
    public function test_delete_data_for_all_users_in_context_removes_logs(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();

        $DB->insert_record('local_userdatasync_log', (object)[
            'userid' => $user->id,
            'fieldname' => 'department',
            'status' => 'updated',
            'message' => 'test',
            'timecreated' => time(),
        ]);

        provider::delete_data_for_all_users_in_context(context_system::instance());

        $this->assertFalse($DB->record_exists('local_userdatasync_log', ['userid' => $user->id]));
    }
}
