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
 * Tests for the connection test page request handling.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class testconnection_security_test extends \advanced_testcase {
    /**
     * Moodle's single_button renders the connection action as a POST form.
     *
     * @return void
     */
    public function test_single_button_renders_post_form_for_connection_test(): void {
        global $OUTPUT;

        $buttonurl = new \moodle_url('/local/userdatasync/testconnection.php', [
            'testconnection' => 1,
            'sesskey' => sesskey(),
        ]);
        $html = $OUTPUT->single_button($buttonurl, get_string('testconnection', 'local_userdatasync'), 'post');

        $this->assertStringContainsString('method="post"', $html);
        $this->assertStringContainsString('name="testconnection"', $html);
        $this->assertStringContainsString('name="sesskey"', $html);
    }

    /**
     * The legacy GET trigger is not used by the page.
     *
     * @return void
     */
    public function test_legacy_get_trigger_is_not_present(): void {
        $source = file_get_contents(__DIR__ . '/../testconnection.php');

        $this->assertStringNotContainsString("optional_param('test'", $source);
    }
}
