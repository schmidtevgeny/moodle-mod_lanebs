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
 * Plugin version and other meta-data are defined here.
 *
 * @package     mod_lanebs
 * @copyright   2020 Senin Yurii <katorsi@mail.ru>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_lanebs';
$plugin->release = '1.4.7';
$plugin->version = 2024012824;
$plugin->requires = 2023100900;
$plugin->maturity = MATURITY_STABLE;
$plugin->base_url = 'https://c.lanbook.com';
$plugin->auth_url = 'https://security.lanbook.com';
$plugin->moodle_api = 'https://moodle-api.e.lanbook.com';
$plugin->search_api = 'http://212.41.20.23:8080';
$plugin->reader_url = 'https://reader.lanbook.com';
$plugin->profile_url = 'https://profile.e.lanbook.com';
