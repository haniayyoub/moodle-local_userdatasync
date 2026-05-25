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

namespace local_userdatasync\privacy;

defined('MOODLE_INTERNAL') || die();

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\plugin\provider as plugin_provider;
use core_privacy\local\request\transform;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

/**
 * Privacy provider for local_userdatasync.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        plugin_provider,
        core_userlist_provider {

    /**
     * Describe stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_external_location_link(
            'external_database',
            [
                'username' => 'privacy:metadata:external_database:username',
                'firstname' => 'privacy:metadata:external_database:firstname',
                'lastname' => 'privacy:metadata:external_database:lastname',
                'email' => 'privacy:metadata:external_database:email',
                'phone1' => 'privacy:metadata:external_database:phone1',
                'phone2' => 'privacy:metadata:external_database:phone2',
                'department' => 'privacy:metadata:external_database:department',
                'institution' => 'privacy:metadata:external_database:institution',
                'profile_field' => 'privacy:metadata:external_database:profile_field',
            ],
            'privacy:metadata:external_database'
        );

        $collection->add_database_table(
            'local_userdatasync_log',
            [
                'userid' => 'privacy:metadata:log:userid',
                'username' => 'privacy:metadata:log:username',
                'fieldname' => 'privacy:metadata:log:fieldname',
                'oldvalue' => 'privacy:metadata:log:oldvalue',
                'newvalue' => 'privacy:metadata:log:newvalue',
                'status' => 'privacy:metadata:log:status',
                'message' => 'privacy:metadata:log:message',
                'timecreated' => 'privacy:metadata:log:timecreated',
            ],
            'privacy:metadata:log'
        );

        return $collection;
    }

    /**
     * Get contexts containing user data.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        global $DB;

        $contextlist = new contextlist();
        if ($DB->record_exists('local_userdatasync_log', ['userid' => $userid])) {
            $contextlist->add_system_context();
        }

        return $contextlist;
    }

    /**
     * Export user data.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if (!$context instanceof context_system) {
                continue;
            }

            $records = $DB->get_records('local_userdatasync_log', ['userid' => $userid], 'timecreated ASC');
            $logs = [];
            foreach ($records as $record) {
                $logs[] = (object)[
                    'fieldname' => $record->fieldname,
                    'status' => $record->status,
                    'message' => $record->message,
                    'oldvalue' => $record->oldvalue,
                    'newvalue' => $record->newvalue,
                    'timecreated' => transform::datetime($record->timecreated),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('privacy:path:logs', 'local_userdatasync')],
                (object)['logs' => $logs]
            );
        }
    }

    /**
     * Delete all plugin data in a context.
     *
     * @param context $context
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context instanceof context_system) {
            $DB->delete_records('local_userdatasync_log');
        }
    }

    /**
     * Delete plugin data for one user.
     *
     * @param approved_contextlist $contextlist
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context instanceof context_system) {
                $DB->delete_records('local_userdatasync_log', ['userid' => $contextlist->get_user()->id]);
            }
        }
    }

    /**
     * Add users with data in the supplied context.
     *
     * @param userlist $userlist
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        if (!$userlist->get_context() instanceof context_system) {
            return;
        }

        $userlist->add_from_sql(
            'userid',
            'SELECT DISTINCT userid
               FROM {local_userdatasync_log}
              WHERE userid <> 0',
            []
        );
    }

    /**
     * Delete plugin data for a list of users.
     *
     * @param approved_userlist $userlist
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        if (!$userlist->get_context() instanceof context_system) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('local_userdatasync_log', "userid {$insql}", $params);
    }
}
