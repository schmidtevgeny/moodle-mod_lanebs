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
header('Access-Control-Allow-Origin: *');

/**
 * Webservices for constructor.
 *
 * @package    local_constructor
 * @copyright  2020 Yurii Senin (katorsi@mail.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/webservice/lib.php");
require_once($CFG->libdir . "/filelib.php");
require_once($CFG->dirroot . '/course/modlib.php');

class mod_lanebs_constructor_external extends external_api
{

    CONST SERVICE_NAME = 'lan_constructor_service';
    CONST LANEBS_MODULE = 'lanebs';
    CONST DIR_MODULE = 'label';
    CONST AUTH_URL = 'https://security.lanbook.com';
    CONST SCRIPT_URL = 'https://c.lanbook.com/api/1.0/misc/scripts';
    CONST SERVICE_USER = 'lan@lanbook.ru';
    CONST BOOK_TYPE = 'book';
    CONST JOURNAL_TYPE = 'journalArticle';
    CONST VIDEO_TYPE = 'video';
    public static $bookFolder = 'Литература по теме';
    public static $journalFolder = 'Статьи по теме';
    public static $videoFolder = 'Видеоматериалы по теме';
    CONST ALLOWED_TYPES = array(
        self::BOOK_TYPE,
        self::JOURNAL_TYPE,
        self::VIDEO_TYPE
    );

    public static function create_mod_lanebs_parameters()
    {
        return new external_function_parameters(
            array(
                'courseData' => new external_single_structure(
                    array(
                      'course' => new external_value(PARAM_INT, 'Course id'),
                      'section' => new external_value(PARAM_INT, 'Section number'),
                      'search' => new external_value(PARAM_TEXT, 'String of search', VALUE_OPTIONAL),
                    ),
                ),
                'resourceData' => new external_single_structure(
                    array(
                        'book' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'resourceId' => new external_value(PARAM_INT, 'Resource id in EBS'),
                                    'resourceName' => new external_value(PARAM_TEXT, 'Resource name'),
                                    'authorName' => new external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                                    'cover' => new external_value(PARAM_TEXT, 'Book cover url', VALUE_OPTIONAL),
                                    'biblioRecord' => new external_value(PARAM_TEXT, 'Bibliographical record', VALUE_OPTIONAL),
                                    'tocName' => new external_value(PARAM_TEXT, 'Name of the toc', VALUE_OPTIONAL),
                                    'pageStart' => new external_value(PARAM_INT, 'Reader page start', VALUE_DEFAULT, 1),
                                ),
                            ), 'books data', VALUE_OPTIONAL
                        ),
                        'journalArticle' => new external_multiple_structure(
                            new external_single_structure(
                                array(
                                    'resourceId' => new external_value(PARAM_INT, 'Resource id in EBS'),
                                    'resourceName' => new external_value(PARAM_TEXT, 'Resource name'),
                                    'authorName' => new external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                                    'cover' => new external_value(PARAM_TEXT, 'Book cover url', VALUE_OPTIONAL),
                                    'biblioRecord' => new external_value(PARAM_TEXT, 'Bibliographical record', VALUE_OPTIONAL),
                                    'tocName' => new external_value(PARAM_TEXT, 'Name of the toc', VALUE_OPTIONAL),
                                    'pageStart' => new external_value(PARAM_INT, 'Reader page start', VALUE_DEFAULT, 1),
                                ),
                            ), 'journals data', VALUE_OPTIONAL
                        ),
                        'video' => new external_single_structure(
                            array(
                                'resourceId' => new external_value(PARAM_INT, 'Resource id in EBS'),
                                'resourceName' => new external_value(PARAM_TEXT, 'Resource name'),
                                'authorName' => new external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                                'cover' => new external_value(PARAM_TEXT, 'Book cover url', VALUE_OPTIONAL),
                                'biblioRecord' => new external_value(PARAM_TEXT, 'Bibliographical record', VALUE_OPTIONAL),
                                'tocName' => new external_value(PARAM_TEXT, 'Name of the toc', VALUE_OPTIONAL),
                                'pageStart' => new external_value(PARAM_INT, 'Reader page start', VALUE_DEFAULT, 1),
                                'videosData' => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'linkName' => new external_value(PARAM_TEXT, 'Name of the video'),
                                            'linkUrl' => new external_value(PARAM_TEXT, 'Url of the video'),
                                            'id' => new external_value(PARAM_INT, 'Id of the video'),
                                            'bookId' => new external_value(PARAM_INT, 'Id of the book'),
                                        ),
                                    ),
                                ),
                            ),
                        'video data', VALUE_OPTIONAL),
                    ),
                ),
            )
        );
    }

    public static function create_mod_lanebs($courseData, $resourceData)
    {
        global $DB;
        $modId = false;
        $data = array('code' => 200, 'message' => 'Data created', 'moduleId' => $modId);
        $course = $DB->get_record('course', array('id' => $courseData['course']));
        if (!$course) {
            $data = array('code' => 404, 'message' => 'Course not found');
            return array(
                'body' => json_encode($data, JSON_THROW_ON_ERROR)
            );
        }
        $lanModule = $DB->get_record('modules', array('name' => self::LANEBS_MODULE));
        $dirModule = $DB->get_record('modules', array('name' => self::DIR_MODULE));
        if (!empty($courseData['search'])) {
            self::$bookFolder .= ' "'.$courseData['search'].'"';
            self::$journalFolder .= ' "'.$courseData['search'].'"';
            self::$videoFolder .= ' "'.$courseData['search'].'"';
        }
        if (!empty($resourceData[self::BOOK_TYPE])) {
            // создание книг
            $books = $resourceData[self::BOOK_TYPE];
            if (!empty($books)) {
                $folder = self::createFolder(self::$bookFolder, $dirModule, $courseData['section']);
                try {
                    $data = add_moduleinfo($folder, $course);
                } catch (dml_exception $e) {
                    $data = array('code' => 500, 'message' => $e->getMessage());
                    return array(
                        'body' => json_encode($data, JSON_THROW_ON_ERROR),
                    );
                }
                foreach ($books as $book) {
                    $lanebs = self::getLanebs($course->id, $courseData['section'], $lanModule->id);
                    $lanebs->type = self::BOOK_TYPE;
                    $lanebs->name = $book['authorName'] . ' - ' . $book['resourceName'] . ', стр. ' . $book['pageStart'];
                    if (isset($book['tocName']) && !empty($book['tocName'])) {
                        $lanebs->name .= ', ' . $book['tocName'];
                    }
                    $lanebs->content = $book['resourceId'];
                    $lanebs->content_name = $book['resourceName'];
                    $lanebs->page_number = $book['pageStart'] ?: 1;
                    $lanebs->cover = $book['cover'] ?: '';
                    $lanebs->biblio_record = $book['biblioRecord'] ?: '';
                    $lanebs->videos = '';
                    try {
                        $data = add_moduleinfo($lanebs, $course);
                    } catch (dml_exception $e) {
                        $data = array('code' => 500, 'message' => $e->getMessage());
                        return array(
                            'body' => json_encode($data, JSON_THROW_ON_ERROR),
                        );
                    }
                }
            }
        }
        // создание журналов
        if (!empty($resourceData[self::JOURNAL_TYPE])) {
            $journals = $resourceData[self::JOURNAL_TYPE];
            if (!empty($journals)) {
                $folder = self::createFolder(self::$journalFolder, $dirModule, $courseData['section']);
                try {
                    $data = add_moduleinfo($folder, $course);
                } catch (dml_exception $e) {
                    $data = array('code' => 500, 'message' => $e->getMessage());
                    return array(
                        'body' => json_encode($data, JSON_THROW_ON_ERROR),
                    );
                }
                foreach ($journals as $journal) {
                    $lanebs = self::getLanebs($course->id, $courseData['section'], $lanModule->id);
                    $lanebs->type = self::JOURNAL_TYPE;
                    $lanebs->name = $journal['authorName'] . ' - ' . $journal['resourceName'] . ', стр. ' . $journal['pageStart'];
                    if (isset($journal['tocName']) && !empty($journal['tocName'])) {
                        $lanebs->name .= ', ' . $journal['tocName'];
                    }
                    $lanebs->content = $journal['resourceId'];
                    $lanebs->content_name = $journal['resourceName'];
                    $lanebs->page_number = $journal['pageStart'] ?: 1;
                    $lanebs->cover = '/mod/lanebs/pix/journal.png';
                    $lanebs->biblio_record = $journal['biblioRecord'] ?: '';
                    $lanebs->videos = '';
                    try {
                        $data = add_moduleinfo($lanebs, $course);
                    } catch (dml_exception $e) {
                        $data = array('code' => 500, 'message' => $e->getMessage());
                        return array(
                            'body' => json_encode($data, JSON_THROW_ON_ERROR),
                        );
                    }
                }
            }
        }
        // создание видео
        if (!empty($resourceData[self::VIDEO_TYPE])) {
            $video = $resourceData[self::VIDEO_TYPE];
            $lanebs = self::getLanebs($course->id, $courseData['section'], $lanModule->id);
            $lanebs->type = self::VIDEO_TYPE;
            $links = $video['videosData'];
            $lanebs->content = $video['bookId'] ?? '';
            $lanebs->name = self::$videoFolder;
            $lanebs->content_name = $video['resourceName'];
            $lanebs->page_number = 1;
            $lanebs->biblio_record = $video['biblioRecord'] ?? '';
            $lanebs->cover = '';
            $lanVideo = array();
            foreach ($links as $link) {
                parse_str(parse_url($link['linkUrl'], PHP_URL_QUERY), $videoId);
                $videoId = $videoId['v'];
                $lanebs->cover = '/mod/lanebs/pix/video.png';
                $lanVideo[] = array(
                    'link' => $link['linkUrl'],
                    'name' => $link['linkName'],
                    'unique' => $link['id'],
                    'video_id' => $videoId
                );
            }
            $lanebs->videos = json_encode($lanVideo, JSON_THROW_ON_ERROR);
            try {
                $data = add_moduleinfo($lanebs, $course);
            } catch (dml_exception $e) {
                $data = array('code' => 500, 'message' => $e->getMessage());
                return array(
                    'body' => json_encode($data, JSON_THROW_ON_ERROR),
                );
            }
        }
        return array(
            'body' => json_encode($data, JSON_THROW_ON_ERROR)
        );
    }

    public static function create_mod_quiz_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Json with creating result')
            ),
        );
    }

    public static function create_mod_quiz_parameters()
    {
        return new external_function_parameters(array(

        ));
    }

    public static function create_mod_quiz()
    {

    }

    public static function create_mod_lanebs_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_service_token_parameters()
    {
        return new external_function_parameters(array());
    }

    public static function get_service_token()
    {
        global $DB;
        $data = array('error' => false, 'code' => 200, 'token' => '');
        self::install_service(); // костыль. Придумать установку данных один раз
        $service = $DB->get_record('external_services', array('name' => self::SERVICE_NAME));
        if ($service) {
            $tokenUser = $DB->get_record('user', array('email' => self::SERVICE_USER));
            if ($tokenUser) {
                $token = $DB->get_record('external_tokens', array('externalserviceid' => $service->id, 'userid' => $tokenUser->id));
                if ($token) {
                    $data['service_token'] = $token->token;
                } else {
                    $data['error'] = true;
                    $data['code'] = 404;
                    $data['message'] = 'Токен доступа для сервиса lan_constructor_service отсутствует';
                }
            } else {
                $data['error'] = true;
                $data['code'] = 404;
                $data['message'] = 'Системный пользователь сервиса lan_constructor_service отсутствует';
                return array('body' => json_encode($data, JSON_THROW_ON_ERROR));
            }
        } else {
            $data['error'] = true;
            $data['code'] = 404;
            $data['message'] = 'Сервис lan_constructor_service отсутствует';
        }
        return array('body' => json_encode($data, JSON_THROW_ON_ERROR));
    }

    public static function get_service_token_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_subscriber_token_parameters()
    {
        return new external_function_parameters(array());
    }

    public static function get_subscriber_token()
    {
        $settings = get_config("lanebs");
        if (isset($settings->token) && !empty($settings->token)) {
            $data = array('error' => false, 'code' => 200, 'subscriber_token' => $settings->token);
            return array('body' => json_encode($data, JSON_THROW_ON_ERROR));
        }
        $data = array('error' => true, 'code' => 404, 'message' => 'Token not found');
        return array('body' => json_encode($data, JSON_THROW_ON_ERROR));
    }

    public static function get_subscriber_token_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_reader_token_parameters()
    {
        return new external_function_parameters(
            array(
                'subscriber_token' => new external_value(PARAM_TEXT, 'subscriber token from lanebs'),
            ),
        );
    }

    public static function get_reader_token($subscriberToken)
    {
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt(['CURLOPT_HTTPHEADER' => ['x-auth-token-subscriber: '.$subscriberToken]]);
        $data = $curl->get(self::AUTH_URL . '/api/sign_in/moodle', null, $options);
        $data = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
        if (!$data['jwt'] || !$data['jwt']['access_token']) {
            $returns = array('error' => true, 'code' => 403, 'message' => 'Access Denied');
            return array(json_encode($returns, JSON_THROW_ON_ERROR));
        }
        $returns = array('error' => false, 'code' => 200, 'reader_token' => $data['jwt']['access_token']);
        return array('body' => json_encode($returns));
    }

    public static function get_reader_token_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_script_names_parameters()
    {
        return new external_function_parameters(array());
    }

    public static function get_script_names()
    {
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $data = $curl->get(self::SCRIPT_URL, null, $options);
        try {
            $scripts = json_decode($data, true, 512, JSON_THROW_ON_ERROR);
            $returns = array('error' => false, 'code' => 200, 'scripts' => $scripts, 'raw_data' => $data);
        } catch (\Exception $e) {
            $returns = array('error' => true, 'code' => 500, 'message' => $data);
        }
        return array('body' => json_encode($returns));
    }

    public static function get_script_names_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function install_service()
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
            $userid = $user->id;
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

    public static function getLanebs($course, $section, $module)
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
        $lanebs->modulename = self::LANEBS_MODULE;
        $lanebs->instance = 0;
        $lanebs->add = self::LANEBS_MODULE;
        $lanebs->update = 0;
        $lanebs->return = 0;
        $lanebs->sr = 0;
        $lanebs->competencies = array();
        $lanebs->competency_rule = '0';
        $lanebs->submitbutton = 'Сохранить и показать';

        return $lanebs;
    }

    public static function createFolder($name, $module, $section)
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
}