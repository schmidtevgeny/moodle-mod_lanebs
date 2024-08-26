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
 * Code to be executed after the plugin has been uninstalled is defined here.
 *
 * @package     mod_lanebs
 * @category    uninstall
 * @copyright   2022 Senin Yurii <syi@landev.ru>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/../lib.php';

/**
 * Custom code to be run on uninstalling the plugin.
 */
function xmldb_lanebs_uninstall() {
    $data = array(
        'type' => 'uninstall'
    );
    send_stat($data);

    return true;
}
