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
 * Plugin settings.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (!class_exists('local_userdatasync_admin_setting_batchsize')) {
    /**
     * Batch size setting with bounded integer validation.
     */
    class local_userdatasync_admin_setting_batchsize extends admin_setting_configtext {
        /**
         * Validate setting value.
         *
         * @param string $data
         * @return true|string
         */
        public function validate($data) {
            $validated = parent::validate($data);
            if ($validated !== true) {
                return $validated;
            }

            $value = (int)$data;
            if ((string)$value !== trim((string)$data) || $value < 1 || $value > 10000) {
                return get_string('batchsize_invalid', 'local_userdatasync');
            }

            return true;
        }
    }
}

if (!class_exists('local_userdatasync_admin_setting_retentiondays')) {
    /**
     * Retention setting with non-negative integer validation.
     */
    class local_userdatasync_admin_setting_retentiondays extends admin_setting_configtext {
        /**
         * Validate setting value.
         *
         * @param string $data
         * @return true|string
         */
        public function validate($data) {
            $validated = parent::validate($data);
            if ($validated !== true) {
                return $validated;
            }

            $value = (int)$data;
            if ((string)$value !== trim((string)$data) || $value < 0) {
                return get_string('retentiondays_invalid', 'local_userdatasync');
            }

            return true;
        }
    }
}

if (!class_exists('local_userdatasync_admin_setting_maxruntime')) {
    /**
     * Maximum runtime setting with bounded integer validation.
     */
    class local_userdatasync_admin_setting_maxruntime extends admin_setting_configtext {
        /**
         * Validate setting value.
         *
         * @param string $data
         * @return true|string
         */
        public function validate($data) {
            $validated = parent::validate($data);
            if ($validated !== true) {
                return $validated;
            }

            $value = (int)$data;
            if ((string)$value !== trim((string)$data) || $value < 0 || $value > 86400) {
                return get_string('maxruntime_invalid', 'local_userdatasync');
            }

            return true;
        }
    }
}

if (!class_exists('local_userdatasync_admin_setting_identifier')) {
    /**
     * External table and column identifier setting validation.
     */
    class local_userdatasync_admin_setting_identifier extends admin_setting_configtext {
        /**
         * Validate setting value.
         *
         * @param string $data
         * @return true|string
         */
        public function validate($data) {
            $validated = parent::validate($data);
            if ($validated !== true) {
                return $validated;
            }

            $value = trim((string)$data);
            if ($value === '') {
                return true;
            }

            $part = '(?:"[A-Za-z][A-Za-z0-9_$#]*"|[A-Za-z][A-Za-z0-9_$#]*)';
            if (!preg_match('/^' . $part . '(?:\.' . $part . ')*$/', $value)) {
                return get_string('invalidexternalidentifier_setting', 'local_userdatasync');
            }

            return true;
        }
    }
}

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_userdatasync',
        get_string('pluginname', 'local_userdatasync')
    );

    $settings->add(new admin_setting_heading(
        'local_userdatasync/generalheading',
        get_string('generalsettings', 'local_userdatasync'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/enabled',
        get_string('enabled', 'local_userdatasync'),
        get_string('enabled_desc', 'local_userdatasync'),
        0
    ));

    $dbtypes = [
        'mysqli' => 'mysqli',
        'mariadb' => 'mariadb',
        'mysql' => 'mysql',
        'pgsql' => 'pgsql',
        'postgres' => 'postgres',
        'postgresql' => 'postgresql',
        'sqlsrv' => 'sqlsrv',
        'mssql' => 'mssql',
        'oci' => 'oci',
        'oci8' => 'oci8',
        'oci8po' => 'oci8po',
        'oracle' => 'oracle',
    ];

    $settings->add(new admin_setting_heading(
        'local_userdatasync/dbheading',
        get_string('externaldbsettings', 'local_userdatasync'),
        ''
    ));

    $settings->add(new admin_setting_configselect(
        'local_userdatasync/dbtype',
        get_string('dbtype', 'local_userdatasync'),
        get_string('dbtype_desc', 'local_userdatasync'),
        'mysqli',
        $dbtypes
    ));

    $settings->add(new admin_setting_configtext(
        'local_userdatasync/dbhost',
        get_string('dbhost', 'local_userdatasync'),
        get_string('dbhost_desc', 'local_userdatasync'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_userdatasync/dbname',
        get_string('dbname', 'local_userdatasync'),
        get_string('dbname_desc', 'local_userdatasync'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_userdatasync/dbuser',
        get_string('dbuser', 'local_userdatasync'),
        get_string('dbuser_desc', 'local_userdatasync'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configpasswordunmask(
        'local_userdatasync/dbpass',
        get_string('dbpass', 'local_userdatasync'),
        get_string('dbpass_desc', 'local_userdatasync'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_userdatasync/dbencoding',
        get_string('dbencoding', 'local_userdatasync'),
        get_string('dbencoding_desc', 'local_userdatasync'),
        'utf8mb4',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new local_userdatasync_admin_setting_identifier(
        'local_userdatasync/dbtable',
        get_string('dbtable', 'local_userdatasync'),
        get_string('dbtable_desc', 'local_userdatasync'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_description(
        'local_userdatasync/testconnectionlink',
        get_string('testconnectionpage', 'local_userdatasync'),
        html_writer::link(
            new moodle_url('/local/userdatasync/testconnection.php'),
            get_string('testconnectionlink_desc', 'local_userdatasync')
        )
    ));

    $matchfields = [
        'id' => 'id',
        'username' => 'username',
        'idnumber' => 'idnumber',
        'email' => 'email',
    ];

    $settings->add(new admin_setting_heading(
        'local_userdatasync/matchingheading',
        get_string('matchingandmapping', 'local_userdatasync'),
        ''
    ));

    $settings->add(new admin_setting_configselect(
        'local_userdatasync/localmatchingfield',
        get_string('localmatchingfield', 'local_userdatasync'),
        get_string('localmatchingfield_desc', 'local_userdatasync'),
        'idnumber',
        $matchfields
    ));

    $settings->add(new local_userdatasync_admin_setting_identifier(
        'local_userdatasync/remotematchingfield',
        get_string('remotematchingfield', 'local_userdatasync'),
        get_string('remotematchingfield_desc', 'local_userdatasync'),
        'student_id',
        PARAM_TEXT
    ));

    $mappingfields = [
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

    foreach ($mappingfields as $fieldname) {
        $settings->add(new local_userdatasync_admin_setting_identifier(
            'local_userdatasync/map_' . $fieldname,
            get_string('mapfield', 'local_userdatasync', $fieldname),
            get_string('mapfield_desc', 'local_userdatasync', $fieldname),
            '',
            PARAM_TEXT
        ));
    }

    $settings->add(new admin_setting_heading(
        'local_userdatasync/behaviourheading',
        get_string('behavioursettings', 'local_userdatasync'),
        ''
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/skipemptyvalues',
        get_string('skipemptyvalues', 'local_userdatasync'),
        get_string('skipemptyvalues_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/updateonlyifchanged',
        get_string('updateonlyifchanged', 'local_userdatasync'),
        get_string('updateonlyifchanged_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/validateemail',
        get_string('validateemail', 'local_userdatasync'),
        get_string('validateemail_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/validatemobilephone',
        get_string('validatemobilephone', 'local_userdatasync'),
        get_string('validatemobilephone_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/allowoverwrite',
        get_string('allowoverwrite', 'local_userdatasync'),
        get_string('allowoverwrite_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/dryrun',
        get_string('dryrun', 'local_userdatasync'),
        get_string('dryrun_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new local_userdatasync_admin_setting_batchsize(
        'local_userdatasync/batchsize',
        get_string('batchsize', 'local_userdatasync'),
        get_string('batchsize_desc', 'local_userdatasync'),
        100,
        PARAM_INT
    ));

    $settings->add(new local_userdatasync_admin_setting_maxruntime(
        'local_userdatasync/maxruntime',
        get_string('maxruntime', 'local_userdatasync'),
        get_string('maxruntime_desc', 'local_userdatasync'),
        0,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_userdatasync/enablepagination',
        get_string('enablepagination', 'local_userdatasync'),
        get_string('enablepagination_desc', 'local_userdatasync'),
        1
    ));

    $settings->add(new local_userdatasync_admin_setting_retentiondays(
        'local_userdatasync/retentiondays',
        get_string('retentiondays', 'local_userdatasync'),
        get_string('retentiondays_desc', 'local_userdatasync'),
        365,
        PARAM_INT
    ));

    $loglevels = [
        'error' => get_string('loglevel_error', 'local_userdatasync'),
        'warning' => get_string('loglevel_warning', 'local_userdatasync'),
        'info' => get_string('loglevel_info', 'local_userdatasync'),
        'debug' => get_string('loglevel_debug', 'local_userdatasync'),
    ];

    $settings->add(new admin_setting_configselect(
        'local_userdatasync/loglevel',
        get_string('loglevel', 'local_userdatasync'),
        get_string('loglevel_desc', 'local_userdatasync'),
        'info',
        $loglevels
    ));

    $ADMIN->add('localplugins', $settings);

    $ADMIN->add('tools', new admin_externalpage(
        'local_userdatasync_report',
        get_string('reportpage', 'local_userdatasync'),
        new moodle_url('/local/userdatasync/index.php'),
        'local/userdatasync:viewreport'
    ));

    $ADMIN->add('tools', new admin_externalpage(
        'local_userdatasync_testconnection',
        get_string('testconnectionpage', 'local_userdatasync'),
        new moodle_url('/local/userdatasync/testconnection.php'),
        'local/userdatasync:managesettings'
    ));
}
