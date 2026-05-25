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
 * External DB connection test page.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_userdatasync\external_db_client;

$context = context_system::instance();
require_login();
admin_externalpage_setup('local_userdatasync_testconnection');
require_capability('moodle/site:config', $context);
require_capability('local/userdatasync:managesettings', $context);

$url = new moodle_url('/local/userdatasync/testconnection.php');

$config = get_config('local_userdatasync');
$client = new external_db_client($config);
$result = null;
$error = null;

$data = data_submitted();
if ($data !== false && !empty($data->testconnection)) {
    if (!confirm_sesskey()) {
        throw new moodle_exception('invalidsesskey');
    }
    try {
        $result = $client->test_connection();
    } catch (Throwable $e) {
        $error = (string)$e->getMessage();
        $dbpass = (string)($config->dbpass ?? '');
        if ($dbpass !== '') {
            $error = str_replace($dbpass, '********', $error);
        }
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('testconnectionpage', 'local_userdatasync'));
echo html_writer::tag('p', get_string('testconnectiondesc', 'local_userdatasync'));

$buttonurl = new moodle_url('/local/userdatasync/testconnection.php', [
    'testconnection' => 1,
    'sesskey' => sesskey(),
]);
echo $OUTPUT->single_button($buttonurl, get_string('testconnection', 'local_userdatasync'), 'post');

$details = new html_table();
$details->head = [
    get_string('connectiontestdetails', 'local_userdatasync'),
    get_string('value'),
];
$details->data[] = [get_string('configureddbtype', 'local_userdatasync'), s((string)($config->dbtype ?? ''))];
$details->data[] = [get_string('normalizeddbtype', 'local_userdatasync'), s($client->get_normalized_dbtype())];
$details->data[] = [get_string('connectionmethod', 'local_userdatasync'), s($client->get_connection_method())];
$details->data[] = [get_string('configureddbhost', 'local_userdatasync'), s((string)($config->dbhost ?? ''))];
$details->data[] = [get_string('configureddbname', 'local_userdatasync'), s((string)($config->dbname ?? ''))];
$details->data[] = [get_string('configuredtable', 'local_userdatasync'), s((string)($config->dbtable ?? ''))];

if ($result !== null) {
    echo $OUTPUT->notification(get_string('connectiontestsuccess', 'local_userdatasync'), 'notifysuccess');
    $details->data[] = [
        get_string('recordcountchecked', 'local_userdatasync'),
        get_string('recordcountchecked_desc', 'local_userdatasync') . ' ' . get_string('yes'),
    ];
    $details->data[] = [
        get_string('externalrecordcount', 'local_userdatasync'),
        s((string)$result['recordcount']),
    ];
} else if ($error !== null) {
    echo $OUTPUT->notification(get_string('connectiontestfailed', 'local_userdatasync') . ' ' . s($error), 'notifyproblem');
}

echo html_writer::table($details);
echo $OUTPUT->footer();
