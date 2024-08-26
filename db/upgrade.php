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
 * Book module upgrade code
 *
 * @package    mod_lanebs
 * @copyright  2020 Senin Yurii {katorsi@mail.ru}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Lanebs module upgrade task
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool always true
 */
function xmldb_lanebs_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2021102128) {

        $table = new xmldb_table('lanebs');
        $field = new xmldb_field('page_number', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, false, 1, 'content_name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('cover', XMLDB_TYPE_TEXT, null, null, false, false, null, 'page_number');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('biblio_record', XMLDB_TYPE_TEXT, null, null, false, null, null, 'cover');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2021102129, 'mod', 'lanebs');
    }

    if ($oldversion < 2022090535) {

        $table = new xmldb_table('lanebs');
        $field = new xmldb_field('videos', XMLDB_TYPE_TEXT, null, null, false, null, null, 'biblio_record');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true,  2022090534, 'mod', 'lanebs');
    }

    if ($oldversion <= 2022090624) {
        $table = new xmldb_table('lanebs');
        $field = new xmldb_field('type', XMLDB_TYPE_TEXT, null, null, false, null, null, 'videos');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true,  2022090625, 'mod', 'lanebs');
    }

    if ($oldversion <= 2023111122) {
        $wsName = 'ws-lanconstructor-role';
        $wsroleId = $DB->get_record('role', array('shortname' => $wsName));
        if (!empty($wsroleId)) {
            //$webserviceUser = core_user::get_user_by_email('lan@lanbook.com');
            $systemContext = context_system::instance();
            assign_capability('moodle/question:add', CAP_ALLOW, $wsroleId->id, $systemContext->id, true);
            assign_capability('moodle/question:managecategory', CAP_ALLOW, $wsroleId->id, $systemContext->id, true);
            //role_assign($wsroleId->id, $webserviceUser->id, $systemContext->id);
        }
    }

    return true;
}
