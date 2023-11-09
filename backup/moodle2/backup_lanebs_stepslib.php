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
 * @package   mod_lanebs
 * @category  backup
 * @copyright 2022 Yurii Senin
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Define the complete page structure for backup, with file and id annotations
 */
class backup_lanebs_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure()
    {
        $userinfo = $this->get_setting_value('userinfo');
        $lanebs = new backup_nested_element('lanebs', array('id'), array(
            'name', 'timecreated', 'timemodified', 'intro',
            'introformat', 'content', 'content_name', 'page_number',
            'cover', 'biblio_record', 'videos'));

        // Define sources
        $lanebs->set_source_table('lanebs', array('id' => backup::VAR_ACTIVITYID));

        $lanebs->annotate_files('mod_lanebs', 'intro', null); // This file areas haven't itemid

        return $this->prepare_activity_structure($lanebs);
    }
}
