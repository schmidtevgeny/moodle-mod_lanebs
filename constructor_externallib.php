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
 * Webservices for constructor.
 *
 * @package    local_constructor
 * @copyright  2020 Yurii Senin (katorsi@mail.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/webservice/lib.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/course/modlib.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->dirroot . '/mod/quiz/locallib.php');
require_once(__DIR__ . '/lib.php');

class mod_lanebs_constructor_external extends \core_external\external_api
{

    const SERVICE_NAME = 'lan_constructor_service';
    const LANEBS_MODULE = 'lanebs';
    const QUIZ_MODULE = 'quiz';
    const DIR_MODULE = 'label';
    const AUTH_URL = '/api/sign_in/moodle';
    const SCRIPT_URL = '/api/1.0/misc/scripts';
    const LOG_URL = '/api/1.0/moodle/log';
    const SERVICE_USER = 'lan@lanbook.com';
    const BOOK_TYPE = 'book';
    const JOURNAL_TYPE = 'journalArticle';
    const VIDEO_TYPE = 'video';
    public static $bookFolder = 'Литература по теме';
    public static $journalFolder = 'Статьи по теме';
    public static $videoFolder = 'Видеоматериалы по теме';
    public static $testingFolder = 'Тестирование по теме';
    const ALLOWED_TYPES = array(
        self::BOOK_TYPE,
        self::JOURNAL_TYPE,
        self::VIDEO_TYPE
    );

    public static function create_mod_lanebs_parameters()
    {
        return new \core_external\external_function_parameters(
            array(
                'courseData' => new \core_external\external_single_structure(
                    array(
                        'course' => new \core_external\external_value(PARAM_INT, 'Course id'),
                        'section' => new \core_external\external_value(PARAM_INT, 'Section number'),
                        'search' => new \core_external\external_value(PARAM_TEXT, 'String of search', VALUE_OPTIONAL),
                    )
                ),
                'resourceData' => new \core_external\external_single_structure(
                    array(
                        'book' => new \core_external\external_multiple_structure(
                            new \core_external\external_single_structure(
                                array(
                                    'resourceId' => new \core_external\external_value(PARAM_INT, 'Resource id in EBS'),
                                    'resourceName' => new \core_external\external_value(PARAM_TEXT, 'Resource name'),
                                    'authorName' => new \core_external\external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                                    'cover' => new \core_external\external_value(PARAM_TEXT, 'Book cover url', VALUE_OPTIONAL),
                                    'biblioRecord' => new \core_external\external_value(PARAM_TEXT, 'Bibliographical record', VALUE_OPTIONAL),
                                    'tocName' => new \core_external\external_value(PARAM_TEXT, 'Name of the toc', VALUE_OPTIONAL),
                                    'pageStart' => new \core_external\external_value(PARAM_INT, 'Reader page start', VALUE_DEFAULT, 1),
                                )
                            ), 'books data', VALUE_OPTIONAL
                        ),
                        'journalArticle' => new \core_external\external_multiple_structure(
                            new \core_external\external_single_structure(
                                array(
                                    'resourceId' => new \core_external\external_value(PARAM_INT, 'Resource id in EBS'),
                                    'resourceName' => new \core_external\external_value(PARAM_TEXT, 'Resource name'),
                                    'authorName' => new \core_external\external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                                    'cover' => new \core_external\external_value(PARAM_TEXT, 'Book cover url', VALUE_OPTIONAL),
                                    'biblioRecord' => new \core_external\external_value(PARAM_TEXT, 'Bibliographical record', VALUE_OPTIONAL),
                                    'tocName' => new \core_external\external_value(PARAM_TEXT, 'Name of the toc', VALUE_OPTIONAL),
                                    'pageStart' => new \core_external\external_value(PARAM_INT, 'Reader page start', VALUE_DEFAULT, 1),
                                )
                            ), 'journals data', VALUE_OPTIONAL
                        ),
                        'video' => new \core_external\external_single_structure(
                            array(
                                'resourceId' => new \core_external\external_value(PARAM_INT, 'Resource id in EBS'),
                                'resourceName' => new \core_external\external_value(PARAM_TEXT, 'Resource name'),
                                'authorName' => new \core_external\external_value(PARAM_TEXT, 'Author name', VALUE_OPTIONAL),
                                'cover' => new \core_external\external_value(PARAM_TEXT, 'Book cover url', VALUE_OPTIONAL),
                                'biblioRecord' => new \core_external\external_value(PARAM_TEXT, 'Bibliographical record', VALUE_OPTIONAL),
                                'tocName' => new \core_external\external_value(PARAM_TEXT, 'Name of the toc', VALUE_OPTIONAL),
                                'pageStart' => new \core_external\external_value(PARAM_INT, 'Reader page start', VALUE_DEFAULT, 1),
                                'videosData' => new \core_external\external_multiple_structure(
                                    new \core_external\external_single_structure(
                                        array(
                                            'linkName' => new \core_external\external_value(PARAM_TEXT, 'Name of the video'),
                                            'linkUrl' => new \core_external\external_value(PARAM_TEXT, 'Url of the video'),
                                            'id' => new \core_external\external_value(PARAM_INT, 'Id of the video'),
                                            'bookId' => new \core_external\external_value(PARAM_INT, 'Id of the book'),
                                        )
                                    )
                                ),
                            ),
                            'video data', VALUE_OPTIONAL),
                    )
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
            self::$bookFolder .= ' "' . $courseData['search'] . '"';
            self::$journalFolder .= ' "' . $courseData['search'] . '"';
            self::$videoFolder .= ' "' . $courseData['search'] . '"';
        }
        if (!empty($resourceData[self::BOOK_TYPE])) {
            // создание книг
            $books = $resourceData[self::BOOK_TYPE];
            if (!empty($books)) {
                $folder = createFolder(self::$bookFolder, $dirModule, $courseData['section']);
                try {
                    $data = add_moduleinfo($folder, $course);
                } catch (dml_exception $e) {
                    $data = array('code' => 500, 'message' => $e->getMessage());
                    return array(
                        'body' => json_encode($data, JSON_THROW_ON_ERROR),
                    );
                }
                foreach ($books as $book) {
                    $lanebs = getLanebs($course->id, $courseData['section'], $lanModule->id);
                    $lanebs->type = self::BOOK_TYPE;
                    $book['authorName'] = $book['authorName'] ?? '';
                    $lanebs->name = $book['authorName'] . ' - ' . $book['resourceName'] . ', стр. ' . $book['pageStart'];
                    if (isset($book['tocName']) && !empty($book['tocName'])) {
                        $lanebs->name .= ', ' . $book['tocName'];
                    }
                    if (mb_strlen($lanebs->name) >= 255) {
                        $lanebs->name = substr($lanebs->name, 0, 251) . '...';
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
                $folder = createFolder(self::$journalFolder, $dirModule, $courseData['section']);
                try {
                    $data = add_moduleinfo($folder, $course);
                } catch (dml_exception $e) {
                    $data = array('code' => 500, 'message' => $e->getMessage());
                    return array(
                        'body' => json_encode($data, JSON_THROW_ON_ERROR),
                    );
                }
                foreach ($journals as $journal) {
                    $lanebs = getLanebs($course->id, $courseData['section'], $lanModule->id);
                    $lanebs->type = self::JOURNAL_TYPE;
                    $journal['authorName'] = $journal['authorName'] ?? '';
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
            if (!empty($video)) {
                $folder = createFolder(self::$videoFolder, $dirModule, $courseData['section']);
                try {
                    $data = add_moduleinfo($folder, $course);
                } catch (dml_exception $e) {
                    $data = array('code' => 500, 'message' => $e->getMessage());
                    return array(
                        'body' => json_encode($data, JSON_THROW_ON_ERROR),
                    );
                }
            }
            $lanebs = getLanebs($course->id, $courseData['section'], $lanModule->id);
            $lanebs->type = self::VIDEO_TYPE;
            $links = $video['videosData'];
            $lanebs->content = $video['bookId'] ?? ''; // здесь этой инфы нет
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
                    'video_id' => $videoId,
                    'book_id' => $link['bookId']
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
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with creating result')
            ),
        );
    }

    public static function create_mod_quiz_parameters()
    {
        return new \core_external\external_function_parameters(array(
            'courseData' => new \core_external\external_single_structure(
                array(
                    'course' => new \core_external\external_value(PARAM_INT, 'Course id'),
                    'section' => new \core_external\external_value(PARAM_INT, 'Section number'),
                    'search' => new \core_external\external_value(PARAM_TEXT, 'String of search', VALUE_OPTIONAL)
                )
            ),
            'testData' => new \core_external\external_multiple_structure(
                new \core_external\external_single_structure(
                    array(
                        'name' => new \core_external\external_value(PARAM_TEXT, 'Quiz name'),
                        'description' => new \core_external\external_value(PARAM_TEXT, 'Quiz description', VALUE_OPTIONAL),
                        'random' => new \core_external\external_value(PARAM_INT, 'Feature of random questions'),
                        'questions' => new \core_external\external_value(PARAM_RAW, 'Quiz questions'),
                        'timeLimit' => new \core_external\external_value(PARAM_TEXT, 'time limit of test'),
                        'difficulty' => new \core_external\external_value(PARAM_TEXT, 'difficulty of test'),
                        'qCount' => new \core_external\external_value(PARAM_INT, 'Questions count'),
                    )
                )
            )
        ));
    }

    public static function create_mod_quiz($courseData, $testData)
    {
        global $DB;
        $course = $DB->get_record('course', array('id' => $courseData['course']));
        if (!$course) {
            $data = array('code' => 404, 'message' => 'Course not found');
            return array(
                'body' => json_encode($data, JSON_THROW_ON_ERROR)
            );
        }
        $quizmodule = $DB->get_record('modules', array('name' => self::QUIZ_MODULE));
        $result = array();
        $dirModule = $DB->get_record('modules', array('name' => self::DIR_MODULE));
        if (isset($courseData['search'])) {
            self::$testingFolder .= ' "' . $courseData['search'] . '"';
        }
        $folder = createFolder(self::$testingFolder, $dirModule, $courseData['section']);
        try {
            $data = add_moduleinfo($folder, $course);
        } catch (dml_exception $e) {
            $data = array('code' => 500, 'message' => $e->getMessage());
            return array(
                'body' => json_encode($data, JSON_THROW_ON_ERROR),
            );
        }
        foreach ($testData as $test) {
            $questions = $test['questions'];
            $quiz = getQuiz($course, $test, $quizmodule, $courseData['section']);
            try {
                $newQuiz = add_moduleinfo($quiz, $course);
                $quizContext = $DB->get_record('context', array('instanceid' => $newQuiz->coursemodule));
                $quizContext = context::instance_by_id($quizContext->id);
                $category = question_make_default_categories([$quizContext]);
                $result[] = $newQuiz->id;
                $filename = random_int(PHP_INT_MIN, PHP_INT_MAX) . $test['name'] . '.xml';
                file_put_contents($filename, $questions);
                $qIds = importQuestions($filename, $category, $course);
                if (!$test['random']) {
                    foreach ($qIds as $id) {
                        quiz_add_quiz_question($id, $newQuiz);
                    }
                } else {
                    $randomCount = (int)$test['random'];
                    quiz_add_random_questions($newQuiz, 1, $category->id, $randomCount, false);
                }
            } catch (dml_exception $e) {
                $data = array('code' => 500, 'message' => $e->getMessage() . '; ' . $e->getTraceAsString() . '; ' . $e->debuginfo);
                return array(
                    'body' => json_encode($data, JSON_THROW_ON_ERROR),
                );
            }
            unlink($filename);
        }
        return array(
            'body' => json_encode($result, JSON_THROW_ON_ERROR),
        );
    }

    public static function create_mod_lanebs_returns()
    {
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_service_token_parameters()
    {
        return new \core_external\external_function_parameters(array());
    }

    public static function get_service_token()
    {
        global $DB;
        $data = array('error' => false, 'code' => 200, 'token' => '');
        $service = $DB->get_record('external_services', array('name' => self::SERVICE_NAME));
        if ($service) {
            $tokenUser = $DB->get_record('user', array('email' => self::SERVICE_USER));
            if (!$tokenUser) {
                $token = install_requirements();
            } else {
                $token = ($DB->get_record('external_tokens', array('externalserviceid' => $service->id, 'userid' => $tokenUser->id)));
                if ($token) {
                    $token = $token->token;
                } else {
                    $token = install_requirements();
                }
            }
            if ($token) {
                $data['service_token'] = $token;
            } else {
                $data['error'] = true;
                $data['code'] = 404;
                $data['message'] = 'Token for service lan_constructor_service is empty. Reload the page';
            }
        } else {
            $data['error'] = true;
            $data['code'] = 404;
            $data['message'] = 'Service lan_constructor_service is gone';
        }
        install_permissions();
        return array('body' => json_encode($data, JSON_THROW_ON_ERROR));
    }

    public static function get_service_token_returns()
    {
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_subscriber_token_parameters()
    {
        return new \core_external\external_function_parameters(array());
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
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_reader_token_parameters()
    {
        return new \core_external\external_function_parameters(
            array(
                'subscriber_token' => new \core_external\external_value(PARAM_TEXT, 'subscriber token from lanebs'),
            ),
        );
    }

    public static function get_reader_token($subscriberToken)
    {
        $curl = new curl();
        $options = array(
            'CURLOPT_POST' => false,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERAGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt(['CURLOPT_HTTPHEADER' => ['x-auth-token-subscriber: ' . $subscriberToken]]);
        $authUrl = get_lanebs_config('auth_url').self::AUTH_URL;
        $data = $curl->get($authUrl, null, $options);
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
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function get_script_names_parameters()
    {
        return new \core_external\external_function_parameters(array());
    }

    public static function get_script_names()
    {
        $curl = new curl();
        $options = array(
            'CURLOPT_POST' => false,
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_USERAGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $scriptUrl = get_lanebs_config('base_url') . self::SCRIPT_URL;
        $params = array(
            'version' => get_lanebs_config('release'),
            'token' => get_config('lanebs', 'token')
        );
        $data = $curl->get($scriptUrl, $params, $options);
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
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }

    public static function send_log_parameters()
    {
        return new \core_external\external_function_parameters(array(
            'data' => new \core_external\external_single_structure(
                array(
                    'resourceid' => new \core_external\external_value(PARAM_TEXT, 'Identifier of content type'),
                    'type' => new \core_external\external_value(PARAM_TEXT, 'Type of log'),
                    'coursename' => new \core_external\external_value(PARAM_TEXT, 'Course shortname'),
                    'email' => new \core_external\external_value(PARAM_TEXT, 'Course owner email'),
                    'fio' => new \core_external\external_value(PARAM_TEXT, 'Course owner fio'),
                    'trigger' => new \core_external\external_value(PARAM_TEXT, 'Email a person who triggered log sending'),
                    'course_date' => new \core_external\external_value(PARAM_TEXT, 'Date of course creating')
                )
            )
        ));
    }

    public static function send_log($data)
    {
        $data = send_logs($data);
        return array('body' => json_encode($data));
    }

    public static function send_log_returns()
    {
        return new \core_external\external_single_structure(
            array(
                'body' => new \core_external\external_value(PARAM_RAW, 'Json with data and errors')
            ),
        );
    }
}