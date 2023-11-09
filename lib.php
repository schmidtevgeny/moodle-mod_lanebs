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
 * lanebs of interface functions and constants.
 *
 * @package     mod_lanebs
 * @copyright   2020 Senin Yurii <katorsi@mail.ru>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/format/xml/format.php');
require_once($CFG->dirroot . '/user/externallib.php');
require_once($CFG->dirroot . '/webservice/lib.php');


/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function lanebs_supports($feature) {
    switch ($feature) {
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_lanebs into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $moduleinstance An object from the form.
 * @param mod_lanebs_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function lanebs_add_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timecreated = time();

    return $DB->insert_record('lanebs', $moduleinstance);
}

/**
 * Updates an instance of the mod_lanebs in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $moduleinstance An object from the form in mod_form.php.
 * @param mod_lanebs_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function lanebs_update_instance($moduleinstance, $mform = null) {
    global $DB;

    $moduleinstance->timemodified = time();
    $moduleinstance->id = $moduleinstance->instance;

    return $DB->update_record('lanebs', $moduleinstance);
}

/**
 * Removes an instance of the mod_lanebs from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function lanebs_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('lanebs', array('id' => $id));
    if (!$exists) {
        return false;
    }

    $DB->delete_records('lanebs', array('id' => $id));

    return true;
}

/**
 * Given a course_module object, this function returns any
 * "extra" information that may be needed when printing
 * this activity in a course listing.
 *
 * See {@link get_array_of_activities()} in course/lib.php
 *
 * @param object $coursemodule
 * @return cached_cm_info info
 */
function lanebs_get_coursemodule_info($coursemodule) {
    global $DB, $PAGE;
    $code = 'require("jquery", function($) {$(".modtype_lanebs").find("img.activityicon").closest("div").css("background-color", "#fff");});';
    $PAGE->requires->js_amd_inline($code);
    if (!$lanebs = $DB->get_record('lanebs', array('id'=>$coursemodule->instance),
        'id, course, name, content, content_name, page_number, cover, videos, type')) {
        return NULL;
    }

    $info = new cached_cm_info();
    $info->name = $lanebs->name;
    $info->icon = null;
    $videos = json_decode($lanebs->videos);

    if ($videos) {
        foreach ($videos as $video) {
            $info->content .= '<p><img style="width:100px;" class="img-responsive atto_image_button_text-bottom" src="https://img.youtube.com/vi/'.$video->video_id.'/0.jpg" alt="'.$video->name.'">'.$video->name.'</p>';
        }
    }
    return $info;
}




/**
 * Added user, token, etc.
 *
 */
function install_requirements()
{
    global $DB, $PAGE;
    $systemContext = context_system::instance();
    $PAGE->set_context($systemContext);
    $enabledprotocols = get_config('core', 'webserviceprotocols');
    if (stripos($enabledprotocols, 'rest') === false) {
        set_config('webserviceprotocols', $enabledprotocols . ',rest');
    }
    //$issetUsers = core_user_external::get_users(array(['key' => 'email', 'value' => 'lan@lanbook.com']))['users'];
    $webserviceUser = core_user::get_user_by_email('lan@lanbook.com');
    if (!$webserviceUser) {
        $user = array(
            'username' => 'ws-lanebs-constructor',
            'firstname' => 'Lan Constructor',
            'lastname' => 'User',
            'email' => 'lan@lanbook.com',
            'createpassword' => true,
        );
        $webserviceUser = (object)core_user_external::create_users(array($user))[0];
    }
    $wsName = 'ws-lanconstructor-role';
    $wsroleId = $DB->get_record('role', array('shortname' => $wsName));
    if (!empty($wsroleId)) {
        $wsroleId = $wsroleId->id;
    } else {
        $wsroleId = create_role(get_string('lanebs_role', 'mod_lanebs'), $wsName, '');
    }
    set_role_contextlevels($wsroleId, [CONTEXT_SYSTEM]);
    assign_capability('webservice/rest:use', CAP_ALLOW, $wsroleId, $systemContext->id, true);
    assign_capability('mod/lanebs:get_constructor', CAP_ALLOW, $wsroleId, $systemContext->id, true);
    role_assign($wsroleId, $webserviceUser->id, $systemContext->id);

    $webserviceManager = new webservice();
    $service = $webserviceManager->get_external_service_by_shortname('Constructor');
    if (!$service) {
        $service = $webserviceManager->get_external_service_by_shortname('LanConstructor');
    } else {
        $service->shortname = 'LanConstructor';
    }
    $service->enabled = true;
    $webserviceManager->update_external_service($service);
    $authUser = $webserviceManager->get_ws_authorised_user($service->id, $webserviceUser->id);
    if (!$authUser) {
        $webserviceManager->add_ws_authorised_user((object)['externalserviceid' => $service->id, 'userid' => $webserviceUser->id]);
    }
    $token = $webserviceManager->get_user_ws_tokens($webserviceUser->id);
    if (empty($token)) {
        $token = external_generate_token(EXTERNAL_TOKEN_PERMANENT, $service, $webserviceUser->id, $systemContext);
    }
    return $token;
}

function getLanebs($course, $section, $module)
{
    $lanebs = new stdClass();
    $lanebs->course = $course;
    $lanebs->timecreated = time();
    $lanebs->timemodified = time();
    $lanebs->introeditor = array('text' => '</p></p>', 'format' => 1, 'itemid' => 0);
    $lanebs->itemid = 0;
    $lanebs->visible = 1;
    $lanebs->visibleoncoursepage = 1;
    $lanebs->cmidnumber = '';
    $lanebs->availabilityconditionsjson = '{"op":"&","c":[],"showc":[]}';
    $lanebs->completionunlocked = 1;
    $lanebs->completion = 1;
    $lanebs->completionexpected = 0;
    $lanebs->coursemodule = 0;
    $lanebs->section = $section;
    $lanebs->module = $module;
    $lanebs->modulename = 'lanebs';
    $lanebs->instance = 0;
    $lanebs->add = 'lanebs';
    $lanebs->update = 0;
    $lanebs->return = 0;
    $lanebs->sr = 0;
    $lanebs->competencies = array();
    $lanebs->competency_rule = '0';
    $lanebs->submitbutton = 'Сохранить и показать';
    return $lanebs;
}

function getQuiz($course, $quizData)
{
    $quiz = new stdClass();
    $quiz->coursemodule = $course->id;
    $quiz->name = $quizData['name'];
    $quiz->intro = $quizData['intro'];
    $quiz->introformat = 1;
    $quiz->timeopen = 0;
    $quiz->timeclose = 0;
    $quiz->timelimit = 0;
    $quiz->overduehandling = 'autosubmit';
    $quiz->graceperiod = 0;
    $quiz->preferredbehaviour = 'deferredfeedback';
    $quiz->canredoquestions = 0;
    $quiz->attempts = 0;
    $quiz->attempttonlast = 0;
    $quiz->grademethod = 1;
    $quiz->decimalpoints = 2;
    $quiz->questiondecimalpoints = -1;
    $quiz->reviewattempt = 69888;
    $quiz->reviewcorrectness = 4352;
    $quiz->reviewmarks = 4352;
    $quiz->reviewspecificfeedback = 4352;
    $quiz->reviewgeneralfeedback = 4352;
    $quiz->reviewrightanswer = 4352;
    $quiz->reviewoverallfeedback = 4352;
    $quiz->questionsperpage = 1;
    $quiz->navmethod = 'free';
    $quiz->shuffleanswers = 1;
    $quiz->timecreated = time();
    $quiz->timemodified = time();
    $quiz->browsersecurity = '-';
    $quiz->delay1 = 0;
    $quiz->delay2 = 0;
    $quiz->showuserpicture = 0;
    $quiz->showblocks = 0;
    $quiz->completionattemptsexhausted = 0;
    $quiz->completionminattempts = 0;
    $quiz->allowofflineattempts = 0;
    return $quiz;
}

function importQuestions($filename)
{
    $qformat = new qformat_xml();
    $qformat->setFilename($filename);
    $qformat->importprocess();
    return $qformat->questionids;
}

function createFolder($name, $module, $section)
{
    $folder = new \stdClass();
    $folder->modulename = $module->name;
    $folder->module = $module->id;
    $folder->name = $name;
    $folder->intro = '<h5 style="color:#1177d1">'.$name.'</h5>';
    $folder->section = $section;
    $folder->idnumber = '';
    $folder->added = time();
    $folder->score = 0;
    $folder->indent = 0;
    $folder->visible = 1;
    $folder->visibleoncoursepage = 1;
    $folder->visibleold = 1;
    $folder->groupmode = 0;
    $folder->groupingid = 0;
    $folder->completion = 1;
    $folder->completiongradeitemnumber = '';
    $folder->completionview = 0;
    $folder->completionexpected = 0;
    $folder->showdescription = 0;
    $folder->availability = '';
    $folder->deletioninprogress = 0;
    $folder->introformat = 1;
    $folder->revision = 1;
    $folder->timemodified = time();
    $folder->display = 0;
    $folder->showexpanded = 1;
    $folder->showdownloadfolder = 1;
    $folder->files = '';
    return $folder;
}