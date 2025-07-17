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
 * Plugin administration pages are defined here.
 *
 * @package     mod_lanebs
 * @category    admin
 * @copyright   2020 Senin Yurii <katorsi@mail.ru>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once __DIR__ . '/lib.php';

/**
 * @var admin_root $ADMIN
 * @var admin_settingpage $settings
 * @var string $section -- modsettinglanebs
 */

$ADMIN->add(
    'modsettings',
    new admin_category('modlanebsfolder', new lang_string('pluginname', 'mod_lanebs'))
);


$settings = new admin_settingpage(
    $section,
    get_string('connection_parameters', 'mod_lanebs'),
    'moodle/site:config'
);

if ($ADMIN->fulltree) {
    global $PAGE;
   // TODO: Define the plugin settings page.
   // https://docs.moodle.org/dev/Admin_settings
    $settings->add(new admin_setting_configtext('lanebs/token',
        get_string('lanebs:token', 'mod_lanebs'),
        get_string('lanebs:token_desc', 'mod_lanebs'), ''));

    $token = get_config('lanebs', 'token');
    if ($token) {
        $data = array(
            'type' => 'install'
        );
        send_stat($data);
    }

    // -----------------------------------------------------------------------------------------------------------------
    $url = new moodle_url('/mod/lanebs/health_check.php');
    $link = html_writer::link(
        $url,
        get_string('checking_plugin', 'mod_lanebs'),
        [
            'class' => 'btn btn-secondary',
            'target' => '_blank'
        ]
    );
    $html = html_writer::div($link, 'col text-center');
    $settings->add(new admin_setting_heading('lanebs/heath_check', $html, ''));
    // -----------------------------------------------------------------------------------------------------------------
}
$ADMIN->add('modlanebsfolder', $settings);

$settings = null;

$page = new admin_externalpage(
    'mod_lanebs_health_check',
    get_string('checking_plugin', 'mod_lanebs'),
    new moodle_url('/mod/lanebs/health_check.php')
);

$ADMIN->add('modlanebsfolder', $page);
