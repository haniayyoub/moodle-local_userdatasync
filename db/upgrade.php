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
 * Upgrade steps for local_userdatasync.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Execute plugin upgrade steps.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_userdatasync_upgrade($oldversion) {
    global $DB;

    if ($oldversion < 2026051611) {
        if (get_config('local_userdatasync', 'retentiondays') === false) {
            set_config('retentiondays', 365, 'local_userdatasync');
        }

        upgrade_plugin_savepoint(true, 2026051611, 'local', 'userdatasync');
    }

    if ($oldversion < 2026051800) {
        if (get_config('local_userdatasync', 'maxruntime') === false) {
            set_config('maxruntime', 0, 'local_userdatasync');
        }

        if (get_config('local_userdatasync', 'enablepagination') === false) {
            set_config('enablepagination', 1, 'local_userdatasync');
        }

        upgrade_plugin_savepoint(true, 2026051800, 'local', 'userdatasync');
    }

    if ($oldversion < 2026052500) {
        $dbman = $DB->get_manager();
        $table = new xmldb_table('local_userdatasync_log');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');

        if ($dbman->table_exists($table)) {
            $DB->execute('UPDATE {local_userdatasync_log} SET userid = 0 WHERE userid IS NULL');
            $dbman->change_field_notnull($table, $field);
            $dbman->change_field_default($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026052500, 'local', 'userdatasync');
    }

    return true;
}
