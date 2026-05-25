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
 * Sync report page.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/userdatasync:viewreport', $context);

$status = optional_param('status', '', PARAM_ALPHANUMEXT);
$fieldname = optional_param('fieldname', '', PARAM_RAW_TRIMMED);
$datefromraw = optional_param('datefrom', '', PARAM_RAW_TRIMMED);
$datefrom = 0;
if ($datefromraw !== '') {
    $timestamp = strtotime($datefromraw . ' 00:00:00');
    if ($timestamp !== false) {
        $datefrom = $timestamp;
    }
}

$url = new moodle_url('/local/userdatasync/index.php', [
    'status' => $status,
    'fieldname' => $fieldname,
    'datefrom' => $datefromraw,
]);

$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title(get_string('reportpage', 'local_userdatasync'));
$PAGE->set_heading(get_string('reportpage', 'local_userdatasync'));

$config = get_config('local_userdatasync');
$where = [];
$params = [];

if ($status !== '') {
    $where[] = 'status = :status';
    $params['status'] = $status;
}

if ($fieldname !== '') {
    $where[] = $DB->sql_like('fieldname', ':fieldname', false, false);
    $params['fieldname'] = '%' . $fieldname . '%';
}

if (!empty($datefrom)) {
    $where[] = 'timecreated >= :datefrom';
    $params['datefrom'] = $datefrom;
}

$wheresql = '';
if ($where) {
    $wheresql = 'WHERE ' . implode(' AND ', $where);
}

$logs = $DB->get_records_sql(
    "SELECT *
       FROM {local_userdatasync_log}
        $wheresql
   ORDER BY timecreated DESC",
    $params,
    0,
    100
);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('reportpage', 'local_userdatasync'));

$lastsync = !empty($config->lastsynctime) ? userdate((int)$config->lastsynctime) : get_string('notyetrun', 'local_userdatasync');

echo html_writer::start_div('local-userdatasync-summary');
echo html_writer::tag('p', get_string('lastsynctime', 'local_userdatasync') . ': ' . s($lastsync));
echo html_writer::tag('p', get_string('totalprocessed', 'local_userdatasync') . ': ' . (int)($config->lastprocessed ?? 0));
echo html_writer::tag('p', get_string('updatedrecords', 'local_userdatasync') . ': ' . (int)($config->lastupdated ?? 0));
echo html_writer::tag('p', get_string('skippedrecords', 'local_userdatasync') . ': ' . (int)($config->lastskipped ?? 0));
echo html_writer::tag('p', get_string('errorrecords', 'local_userdatasync') . ': ' . (int)($config->lasterrors ?? 0));
echo html_writer::end_div();

echo html_writer::start_tag('form', [
    'method' => 'get',
    'action' => $url->out(false),
]);
echo html_writer::start_div();
echo html_writer::label(get_string('statusfilter', 'local_userdatasync'), 'id_status');
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'status',
    'id' => 'id_status',
    'value' => $status,
]);
echo html_writer::end_div();
echo html_writer::start_div();
echo html_writer::label(get_string('fieldnamefilter', 'local_userdatasync'), 'id_fieldname');
echo html_writer::empty_tag('input', [
    'type' => 'text',
    'name' => 'fieldname',
    'id' => 'id_fieldname',
    'value' => $fieldname,
]);
echo html_writer::end_div();
echo html_writer::start_div();
echo html_writer::label(get_string('datefromfilter', 'local_userdatasync'), 'id_datefrom');
echo html_writer::empty_tag('input', [
    'type' => 'date',
    'name' => 'datefrom',
    'id' => 'id_datefrom',
    'value' => $datefromraw,
]);
echo html_writer::end_div();
echo html_writer::empty_tag('input', [
    'type' => 'submit',
    'value' => get_string('applyfilters', 'local_userdatasync'),
]);
echo html_writer::link(new moodle_url('/local/userdatasync/index.php'), get_string('resetsyncfilters', 'local_userdatasync'));
echo html_writer::end_tag('form');

echo $OUTPUT->heading(get_string('recentsynclogs', 'local_userdatasync'), 3);

if (!$logs) {
    echo $OUTPUT->notification(get_string('nosynclogsfound', 'local_userdatasync'), 'info');
    echo $OUTPUT->footer();
    exit;
}

$table = new html_table();
$table->head = [
    get_string('log_time', 'local_userdatasync'),
    get_string('log_userid', 'local_userdatasync'),
    get_string('log_fieldname', 'local_userdatasync'),
    get_string('log_status', 'local_userdatasync'),
    get_string('log_message', 'local_userdatasync'),
];

foreach ($logs as $log) {
    $table->data[] = [
        s(userdate((int)$log->timecreated)),
        (int)$log->userid,
        s((string)$log->fieldname),
        s((string)$log->status),
        s((string)$log->message),
    ];
}

echo html_writer::table($table);
echo $OUTPUT->footer();
