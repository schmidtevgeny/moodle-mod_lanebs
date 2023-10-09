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

    $id = $DB->insert_record('lanebs', $moduleinstance);

    return $id;
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
    $code = '$(document).ready(function () {$(".modtype_lanebs").find("img.activityicon").closest("div").css("background-color", "#fff");})';
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
        //$info->content = '<div class="container">';
        foreach ($videos as $video) {
            $info->content .= '<p><img style="width:100px;" class="img-responsive atto_image_button_text-bottom" src="https://img.youtube.com/vi/'.$video->video_id.'/0.jpg" alt="'.$video->name.'">'.$video->name.'</p>';
        }
    }

    //$info->content = '</div>';
    return $info;
}

/**
 * Added user, token, etc.
 *
 */
function install_requirements()
{
    global $DB;
    $service = $DB->get_record('external_services', array('name' => 'lan_constructor_service'));
    if ($service) {
        $user = $DB->get_record('user', array('email' => 'lan@lanbook.ru'));
        if (!$user) {
            $user = new \stdClass();
            $user->auth = 'manual';
            $user->confirmed = 1;
            $user->policyagreed = 0;
            $user->deleted = 0;
            $user->suspended = 0;
            $user->mnethostid = 1;
            $user->username = 'lanconstructor_service';
            $user->password = 'lanconstructor_service';
            $user->idnumber = '';
            $user->firstname = 'Lan';
            $user->lastname = 'Service';
            $user->email = 'lan@lanbook.ru';
            $user->emailstop = 0;
            $user->icq = '';
            $user->skype = '';
            $user->yahoo = '';
            $user->aim = '';
            $user->msn = '';
            $user->phone1 = '';
            $user->phone2 = '';
            $user->institution = '';
            $user->department = '';
            $user->address = '';
            $user->city = '';
            $user->country = 'RU';
            $user->lang = 'ru';
            $user->calendartype = 'gregorian';
            $user->theme = '';
            $user->timezone = 99;
            $user->firstaccess = time();
            $user->lastaccess = time();
            $user->lastlogin = 0;
            $user->lastip = '';
            $user->secret = '';
            $user->currentlogin = time();
            $user->picture = 0;
            $user->url = '';
            $user->descriptionformat = 1;
            $user->mailformat = 1;
            $user->maildigest = 0;
            $user->maildisplay = 2;
            $user->autosubscribe = 0;
            $user->trackforums = 0;
            $user->timecreacted = time();
            $user->timemodified = time();
            $user->trustbitmask = 0;
            $user->id = $DB->insert_record('user', $user);
        }
        // назначение роли user
        $userid = $user->id;
        $userRole = $DB->get_record('role', array('shortname' => 'user'));
        if (!$userRole) {
            $userRole = $DB->get_record('role', array('shortname' => 'admin'));
        }
        $roleAssignment = $DB->get_record('role_assignments', array('userid' => $userid, 'roleid' => $userRole->id));
        if (!$roleAssignment) {
            $roleAssignment = new \stdClass();
            $roleAssignment->roleid = $userRole->id;
            $roleAssignment->contextid = 1; // мб и что-то другое, потестить
            $roleAssignment->userid = $userid;
            $roleAssignment->timemodified = time();
            $roleAssignment->modifierid = $userid;
            $roleAssignment->component = '';
            $roleAssignment->itemid = 0;
            $roleAssignment->sortorder = 0;
            $DB->insert_record('role_assignments', $roleAssignment);
        }
        $serviceUser = $DB->get_record('external_services_users', array('externalserviceid' => $service->id, 'userid' => $userid));
        if (!$serviceUser) {
            $serviceUser = new \stdClass();
            $serviceUser->externalserviceid = $service->id;
            $serviceUser->userid = $userid;
            $serviceUser->timecreated = time();
            $DB->insert_record('external_services_users', $serviceUser);
        }
        $token = $DB->get_record('external_tokens', array('externalserviceid' => $service->id, 'userid' => $userid));
        if (!$token) {
            $token = new \stdClass();
            $token->token = md5(uniqid(mt_rand(), 1));
            $token->privatetoken = random_string(64);
            $token->tokentype = 0;
            $token->userid = $userid;
            $token->externalserviceid = $service->id;
            $token->contextid = 1;
            $token->creatorid = $userid;
            $token->validuntil = 0;
            $token->timecreated = time();
            $token->lastaccess = time();
            $DB->insert_record('external_tokens', $token);
        }
    }
}
