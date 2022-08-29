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
 * The main mod_lanebs configuration form.
 *
 * @package     mod_lanebs
 * @copyright   2020 Senin Yurii <katorsi@mail.ru>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_lanebs
 * @copyright  2020 Senin Yurii <katorsi@mail.ru>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_lanebs_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $PAGE, $USER;

        // Course_module ID
        $id = optional_param('update', 0, PARAM_INT);

        $mform = $this->_form;
        $settings = get_config("lanebs");
        if (isset($settings->token) && !empty($settings->token)) {
            $_SESSION['mod_lanebs_subscriberToken'] = $settings->token;
        }
        else if (isset($USER->profile['mod_lanebs_token']) && !empty($USER->profile['mod_lanebs_token'])) {
            $_SESSION['mod_lanebs_subscriberToken'] = $USER->profile['mod_lanebs_token'];
        }
        $PAGE->requires->css('/mod/lanebs/css/modal_video.css');
        $PAGE->requires->js_call_amd('mod_lanebs/modal_search_handle', 'init');
        $PAGE->requires->js_call_amd('mod_lanebs/modal_video_handle', 'init');

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('lanebsname', 'mod_lanebs'), array('size' => '64'));

        $mform->addElement('hidden', 'content', '');
        //$mform->addRule('content', get_string('content_error', 'mod_lanebs'), 'required', null, 'client');
        $mform->setType('content', PARAM_TEXT);

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        $mform->addHelpButton('name', 'lanebsname', 'mod_lanebs');

        // Adding the standard "intro" and "introformat" fields.
        if ($CFG->branch >= 29) {
            $this->standard_intro_elements();
        } else {
            $this->add_intro_editor();
        }

        $mform->addElement('button', 'modal_show_button', get_string('button_desc', 'mod_lanebs'));

        $mform->addElement('button', 'modal_video_button', get_string('video_button_desc', 'mod_lanebs'), array('data-action' => 'video_modal', 'class' => 'hidden'));
        $mform->addElement('html', '<div class="video_preview_container row"></div>');

        $mform->addElement('text', 'content_name', get_string('choosen_resourse', 'mod_lanebs'), ['style' => 'width:100%']);
        $mform->addRule('content_name', null, 'required', null, 'client');
        $mform->setType('content_name', PARAM_TEXT);
        $mform->addHelpButton('modal_show_button', 'lanebsbutton', 'mod_lanebs');

        $mform->addElement('text', 'page_number', get_string('page_number', 'mod_lanebs'), ['style' => 'width:50%']);
        $mform->addRule('page_number', null, 'required', null, 'client');
        $mform->setType('page_number', PARAM_INT);
        $mform->setDefault('page_number', 1);

        $mform->addElement('hidden', 'cover', get_string('lanebs_cover', 'mod_lanebs'));
        $mform->setType('cover', PARAM_TEXT);

        $mform->addElement('hidden', 'biblio_record', get_string('biblio_record', 'mod_lanebs'));
        $mform->setType('biblio_record', PARAM_TEXT);

        $mform->addElement('hidden', 'videos', get_string('video', 'mod_lanebs'));
        $mform->setType('videos', PARAM_TEXT);

        $mform->addElement('header', 'copy-paste_mod', get_string('copy_paste', 'mod_lanebs'));
        if ($id) {
            $mform->addElement('button', 'copy_mod', get_string('copy_settings', 'mod_lanebs'));
        }
        $mform->addElement('button', 'paste_mod', get_string('paste_settings', 'mod_lanebs'));

        $PAGE->requires->js_call_amd('mod_lanebs/copy_paste', 'init');

        // Adding the rest of mod_lanebs settings, spreading all them into this fieldset
        // ... or adding more fieldsets ('header' elements) if needed for better logic.
        /*$mform->addElement('static', 'label1', 'lanebssettings', get_string('lanebssettings', 'mod_lanebs'));
        $mform->addElement('header', 'lanebsfieldset', get_string('lanebsfieldset', 'mod_lanebs'));*/

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }


    public function validation($data, $files) {
        return parent::validation($data, $files);
    }
}
