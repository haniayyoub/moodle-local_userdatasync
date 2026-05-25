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
 * Tests for the external database client.
 *
 * @package   local_userdatasync
 * @copyright 2026 Hani Ayyoub
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers    \local_userdatasync\external_db_client
 */
final class external_db_client_test extends \advanced_testcase {
    /**
     * Valid identifiers include common Oracle and SQL qualified names.
     *
     * @dataProvider valid_identifier_provider
     * @param string $identifier
     * @return void
     */
    public function test_safe_identifier_accepts_supported_names(string $identifier): void {
        $client = new external_db_client((object)[]);
        $method = new \ReflectionMethod($client, 'get_safe_identifier');
        $method->setAccessible(true);

        $this->assertSame($identifier, $method->invoke($client, $identifier, 'external column'));
    }

    /**
     * Invalid identifiers are rejected before SQL construction.
     *
     * @dataProvider invalid_identifier_provider
     * @param string $identifier
     * @return void
     */
    public function test_safe_identifier_rejects_unsafe_names(string $identifier): void {
        $client = new external_db_client((object)[]);
        $method = new \ReflectionMethod($client, 'get_safe_identifier');
        $method->setAccessible(true);

        $this->expectException(\moodle_exception::class);
        $method->invoke($client, $identifier, 'external column');
    }

    /**
     * DB type aliases are normalized to Moodle ADOdb driver names.
     *
     * @dataProvider dbtype_alias_provider
     * @param string $configured
     * @param string $expected
     * @return void
     */
    public function test_dbtype_aliases_are_normalized(string $configured, string $expected): void {
        $client = new external_db_client((object)['dbtype' => $configured]);

        $this->assertSame($expected, $client->get_normalized_dbtype());
    }

    /**
     * Runtime batch sizes are clamped to supported bounds.
     *
     * @dataProvider batch_size_provider
     * @param int $configured
     * @param int $expected
     * @return void
     */
    public function test_batch_size_is_clamped(int $configured, int $expected): void {
        $client = new external_db_client((object)[]);
        $method = new \ReflectionMethod($client, 'validate_batch_size');
        $method->setAccessible(true);

        $this->assertSame($expected, $method->invoke($client, $configured));
    }

    /**
     * Valid identifier examples.
     *
     * @return array[]
     */
    public static function valid_identifier_provider(): array {
        return [
            ['USERS'],
            ['student_id'],
            ['schema.USERS'],
            ['"USERS"'],
            ['USER$DATA'],
            ['USER#DATA'],
        ];
    }

    /**
     * Invalid identifier examples.
     *
     * @return array[]
     */
    public static function invalid_identifier_provider(): array {
        return [
            [''],
            ['1USERS'],
            ['USERS;DELETE'],
            ['USERS WHERE 1=1'],
            ['schema..USERS'],
            ['"USERS;DELETE"'],
        ];
    }

    /**
     * DB type alias examples.
     *
     * @return array[]
     */
    public static function dbtype_alias_provider(): array {
        return [
            ['oracle', 'oci8'],
            ['oci', 'oci8'],
            ['mysql', 'mysqli'],
            ['mariadb', 'mysqli'],
            ['pgsql', 'postgres'],
            ['postgresql', 'postgres'],
            ['mssql', 'mssqlnative'],
            ['unknown', ''],
        ];
    }

    /**
     * Batch size examples.
     *
     * @return array[]
     */
    public static function batch_size_provider(): array {
        return [
            [0, 1],
            [1, 1],
            [500, 500],
            [10001, 10000],
        ];
    }
}
