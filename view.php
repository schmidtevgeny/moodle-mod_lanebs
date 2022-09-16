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
 * Prints an instance of mod_lanebs.
 *
 * @package     mod_lanebs
 * @copyright   2020 Senin Yurii <katorsi@mail.ru>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');

// Course_module ID, or
$id = optional_param('id', 0, PARAM_INT);

// ... module instance id.
$l  = optional_param('l', 0, PARAM_INT);

if ($id) {
    $cm             = get_coursemodule_from_id('lanebs', $id, 0, false, MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $moduleinstance = $DB->get_record('lanebs', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($l) {
    $moduleinstance = $DB->get_record('lanebs', array('id' => $n), '*', MUST_EXIST);
    $course         = $DB->get_record('course', array('id' => $moduleinstance->course), '*', MUST_EXIST);
    $cm             = get_coursemodule_from_instance('lanebs', $moduleinstance->id, $course->id, false, MUST_EXIST);
} else {
    print_error(get_string('missingidandcmid', 'mod_lanebs'));
}

require_login($course, true, $cm);

$modulecontext = context_module::instance($cm->id);

$event = \mod_lanebs\event\course_module_viewed::create(array(
    'objectid' => $moduleinstance->id,
    'context' => $modulecontext
));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('lanebs', $moduleinstance);
$event->trigger();

$settings = get_config("lanebs");
if (isset($settings->token) && !empty($settings->token)) {
    $_SESSION['mod_lanebs_subscriberToken'] = $settings->token;
}
else if (isset($USER->profile['mod_lanebs_token']) && !empty($USER->profile['mod_lanebs_token'])) {
    $_SESSION['mod_lanebs_subscriberToken'] = $USER->profile['mod_lanebs_token'];
}

$PAGE->requires->css('/mod/lanebs/css/modal_book.css');
$PAGE->requires->js_call_amd('mod_lanebs/modal_search_handle', 'init');

$PAGE->requires->js_call_amd('mod_lanebs/view_button', 'init', array('title' => get_string('lanebs_view', 'mod_lanebs')));

$PAGE->requires->js_call_amd('mod_lanebs/player_button', 'init');

$PAGE->set_url('/mod/lanebs/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($modulecontext);

$videos = $moduleinstance->videos;
$videos = json_decode($videos);
$videosBlock = '';
if (!empty($videos)) {
    $videosBlock = '<div class="row"><h3>'.get_string('video_materials', 'mod_lanebs').'</h3></div>';
    foreach ($videos as $video) {
        $videosBlock .= '<div class="video row"><p data-action="player_modal" style="color:#4285f4;cursor:pointer;" data-id="'.$video->video_id.'"><u>'.$video->name.'</u></p></div>';
    }
}

echo $OUTPUT->header();

echo
        '<div class="item-container d-flex">'.
            '<div style="flex:0.2">'.
                '<div class="row d-flex justify-content-center">'.
                    '<img src="'.format_string($moduleinstance->cover).'" alt="'.get_string('lanebs_cover', 'mod_lanebs').'" style="width:70%">'.
                '</div>'.
                '<div class="row d-flex justify-content-center mt-3">'.
                    '<button style="color:#174c8d;background-color:white;border-color:#4285f4;" class="btn btn-info" data-action="book_modal">'.get_string('lanebs_read', 'mod_lanebs').' '.format_string($moduleinstance->page_number).' '.get_string('lanebs_read_page', 'mod_lanebs').'</button>'.
                '</div>'.
            '</div>'.
            '<div class="item container mt-5 ml-4" style="flex:0.8;" data-id="'.format_string($moduleinstance->content).'" data-page="'.format_string($moduleinstance->page_number).'">'.
                '<div class="row">'.
                    '<div class="biblio_record"><span>'.format_string($moduleinstance->biblio_record).'</span></div>'.
                    '<div class="intro mt-4"><span>'.format_string($moduleinstance->intro).'</span></div>'.
                '</div>'.
                $videosBlock.
            '</div>'.
         '</div>';

echo $OUTPUT->footer();
