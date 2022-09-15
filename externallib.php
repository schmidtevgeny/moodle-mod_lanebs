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
 * Webservices for lanebs.
 *
 * @package    mod_lanebs
 * @copyright  2020 Yurii Senin (katorsi@mail.ru)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . "/externallib.php");
require_once($CFG->dirroot . "/webservice/lib.php");
require_once($CFG->libdir . "/filelib.php");

class mod_lanebs_external extends external_api
{

    private static $subscribeToken = false;
    private static $readerToken = false;
    private static $authUrl = 'https://security.lanbook.com';//'https://security.test.lanbook.com';
    private static $baseUrl = 'https://moodle-api.e.lanbook.com';//'https://moodle-api.test.lanbook.com';
    private static $readerUrl = 'https://reader.lanbook.com';
    private static $mobileReaderUrl = 'https://reader.lanbook.com';
    private const SORT_CREATE_DESC = 'create_time';

    /**
     * Return category_tree webservice parameters.
     *
     * @return \external_function_parameters
     */
    public static function search_books_parameters()
    {
        return new external_function_parameters(
            array(
                'searchParam' => new external_single_structure(
                    array(
                        'searchString'  => new external_value(PARAM_TEXT, 'Search string'),
                        'bookFilter' => new external_value(PARAM_TEXT, 'Search detail'),
                        'eduFilter' => new external_value(PARAM_TEXT, 'Filter by education category'),
                    )
                ),
                'page'        => new external_value(PARAM_INT, 'search page'),
                'limit'       => new external_value(PARAM_INT, 'element count by page'),
                'catId'       => new external_value(PARAM_RAW, 'category ID'),
            )
        );
    }

    /**
     * @param $searchParam
     * @param $page
     * @param $limit
     * @param $catId
     * @return array
     * @throws coding_exception
     */
    public static function search_books($searchParam, $page, $limit, $catId)
    {
        if (isset($_SESSION['mod_lanebs_readerToken']) && !empty($_SESSION['mod_lanebs_readerToken'])) {
            self::$readerToken = $_SESSION['mod_lanebs_readerToken'];
        }
        if (isset($_SESSION['mod_lanebs_subscriberToken']) && !empty($_SESSION['mod_lanebs_subscriberToken'])) {
            self::$subscribeToken = $_SESSION['mod_lanebs_subscriberToken'];
        }
        $params = array('page' => $page, 'limit' => $limit);
        $url = self::$baseUrl.'/api/search/book';
        if (isset($searchParam['searchString']) && !empty($searchParam['searchString'])) {
            $params['query_id_isbn_title'] = $searchParam['searchString'];
        } else {
            $url = self::$baseUrl.'/api/categories/books';
        }
        if (isset($searchParam['eduFilter']) && !empty($searchParam['eduFilter'])) {
            $params[$searchParam['eduFilter']] = 1;
        }
        if (isset($searchParam['bookFilter'])) {
            $params['type'] = $searchParam['bookFilter'];
        }
        if (isset($catId) && !empty($catId)) {
            $params['category_id'] = (int)$catId;
        }
        $params['sort'] = self::SORT_CREATE_DESC;
        $curl = new curl();
        $options = array(
            'CURLOPT_SSL_VERIFYPEER' => true,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_VERBOSE' => true);
        $curl->setopt($options);
        $curl->setopt(['CURLOPT_HTTPHEADER' =>
                ['x-auth-token-subscriber: '.self::$subscribeToken,
                    'Authorization: Bearer '.self::$readerToken]
            ]
        );
        $data = $curl->get($url, $params, $options);
        return array(
            'body' => $data
        );
    }

    /**
     *
     * @return \external_single_structure
     */
    public static function search_books_returns() {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Search string'),
            )
        );
    }

    public static function book_content($id, $mobile)
    {
        if (isset($_SESSION['mod_lanebs_readerToken']) && !empty($_SESSION['mod_lanebs_readerToken'])) {
            self::$readerToken = $_SESSION['mod_lanebs_readerToken'];
        }
        if (isset($_SESSION['mod_lanebs_subscriberToken']) && !empty($_SESSION['mod_lanebs_subscriberToken'])) {
            self::$subscribeToken = $_SESSION['mod_lanebs_subscriberToken'];
        }
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt([ 'CURLOPT_HTTPHEADER' =>
                ['x-auth-token-subscriber: '.self::$subscribeToken,
                'Authorization: Bearer '.self::$readerToken]
            ]
        );
        if ($mobile) {
            $readerUrl = self::$mobileReaderUrl . '/old/book/'. $id . '?moodle=1';
        } else {
            $readerUrl = self::$readerUrl . '/old/book/'. $id . '?moodle=1';
        }
        //$data = $curl->get($readerUrl, null, $options);
        //$result = self::regexReplace($data);
        return array(
            'body' => self::$readerToken//$result
        );
    }

    public static function book_content_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_TEXT, 'book ID'),
                'mobile' => new external_value(PARAM_BOOL, 'Do you need a mobile reader')
            )
        );
    }

    public static function book_content_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'raw HTML for reader'),
            )
        );
    }

    public static function category_tree_parameters()
    {
        return new external_function_parameters(
            array(
                'categoryId' => new external_single_structure(array(new external_value(PARAM_TEXT, 'book ID'))
                )
            )
        );
    }

    public static function category_tree($categoryId)
    {
        global $DB, $USER;
        if (isset($_SESSION['mod_lanebs_readerToken']) && !empty($_SESSION['mod_lanebs_readerToken'])) {
            self::$readerToken = $_SESSION['mod_lanebs_readerToken'];
        }
        if (isset($_SESSION['mod_lanebs_subscriberToken']) && !empty($_SESSION['mod_lanebs_subscriberToken'])) {
            self::$subscribeToken = $_SESSION['mod_lanebs_subscriberToken'];
        }
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt([ 'CURLOPT_HTTPHEADER' =>
                ['x-auth-token-subscriber: '.self::$subscribeToken,
                    'Authorization: Bearer '.self::$readerToken]
            ]
        );
        $categoryId = $categoryId[0];
        if (empty($categoryId) || !isset($categoryId) || ($categoryId === 'null')) {
            $url = self::$baseUrl . '/api/categories';
        }
        else {
            $url = self::$baseUrl . '/api/category/'.$categoryId;
        }
        $data = $curl->get($url, null, $options);
        return array(
            'body' => $data
        );
    }

    public static function category_tree_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'upper category'),
            )
        );
    }

    public static function auth_parameters()
    {
        return new external_function_parameters([]);
    }

    public static function auth()
    {
        if (isset($_SESSION['mod_lanebs_subscriberToken']) && !empty($_SESSION['mod_lanebs_subscriberToken'])) {
            self::$subscribeToken = $_SESSION['mod_lanebs_subscriberToken'];
        }
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt([ 'CURLOPT_HTTPHEADER' => ['x-auth-token-subscriber: '.self::$subscribeToken]]);
        $data = $curl->get(self::$authUrl . '/api/sign_in/moodle', null, $options);
        $token = (json_decode($data))->jwt->access_token;
        if ($token) {
            $_SESSION['mod_lanebs_readerToken'] = $token;
        }
        else {
            $_SESSION['mod_lanebs_readerToken'] = false;
        }
        return array('body' => $data);
    }

    public static function auth_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'reader token')
            )
        );
    }

    public static function lanebs_info_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'lanebs module ID')
                )
        );
    }

    public function lanebs_info($id)
    {
        global $DB;
        $instance = $DB->get_record('course_modules', array('id' => $id));
        if ($instance) {
            $context = context_course::instance($instance->instance);
            try {
                require_capability('mod/lanebs:get_tree', $context);
            } catch (Exception $e) {
                return array('body' => 'Permission denied: '.$e->getMessage());
            }
            $info = $DB->get_record('lanebs', array('id' => $instance->instance));
            $info->visible = $instance->visible;
            $info->idnumber = $instance->idnumber;
            $info->availability = $instance->availability;
            $info->completition = $instance->completition;
            $info->completitionexpected = $instance->completitionexpected;
            try {
                return array('body' => json_encode($info, JSON_THROW_ON_ERROR));
            } catch (JsonException $e) {
                return array('body' => 'Error json_encode: ' . $e->getMessage());
            }
        }
    }

    public static function lanebs_info_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'lanebs module object from database'),
            )
        );
    }

    public static function toc_name_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'book ID'),
                'page' => new external_value(PARAM_INT, 'page number'),
            )
        );
    }

    public static function toc_name($id, $page)
    {
        if (isset($_SESSION['mod_lanebs_readerToken']) && !empty($_SESSION['mod_lanebs_readerToken'])) {
            self::$readerToken = $_SESSION['mod_lanebs_readerToken'];
        }
        if (isset($_SESSION['mod_lanebs_subscriberToken']) && !empty($_SESSION['mod_lanebs_subscriberToken'])) {
            self::$subscribeToken = $_SESSION['mod_lanebs_subscriberToken'];
        }
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt([ 'CURLOPT_HTTPHEADER' =>
                ['x-auth-token-subscriber: '.self::$subscribeToken,
                    'Authorization: Bearer '.self::$readerToken]
            ]
        );
        $readerUrl = self::$baseUrl . '/api/book/'. $id . '/toc';
        $data = $curl->get($readerUrl, null, $options);
        $items = json_decode($data);
        $tocName = array();
        if ($page !== 0) {
            $tocs = $items->body->items;
            usort($tocs, function ($toc1, $toc2) {
                return $toc1->page <=> $toc2->page;
            });
            foreach ($tocs as $tocId => $item) {
                if ($page === (int)$item->page) {
                    $tocName = $item->title;
                    break;
                }

                if ($page > (int)$item->page && (isset($tocs[$tocId+1]) && ($tocs[$tocId+1]->page > $page))) {
                    $tocName = $item->title;
                    break;
                }

                if (!isset($tocs[$tocId+1])) {
                    $tocName = $item->title;
                    break;
                }
            }
        } else {
            $tocName = $items->body->items;
        }
        return array(
            'body' => json_encode($tocName)
        );
    }

    public static function toc_name_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Needed TOC name')
            )
        );
    }

    public static function toc_videos_parameters()
    {
        return new external_function_parameters(
            array(
                'id' => new external_value(PARAM_INT, 'book ID'),
            )
        );
    }

    public static function toc_videos($id)
    {
        if (isset($_SESSION['mod_lanebs_readerToken']) && !empty($_SESSION['mod_lanebs_readerToken'])) {
            self::$readerToken = $_SESSION['mod_lanebs_readerToken'];
        }
        if (isset($_SESSION['mod_lanebs_subscriberToken']) && !empty($_SESSION['mod_lanebs_subscriberToken'])) {
            self::$subscribeToken = $_SESSION['mod_lanebs_subscriberToken'];
        }
        $curl = new curl();
        $options = array(
            'CURLOPT_POST'              => false,
            'CURLOPT_SSL_VERIFYPEER'    => true,
            'CURLOPT_RETURNTRANSFER'    => true,
            'CURLOPT_USERAGENT'         => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36');
        $curl->setopt($options);
        $curl->setopt([ 'CURLOPT_HTTPHEADER' =>
                ['x-auth-token-subscriber: '.self::$subscribeToken,
                    'Authorization: Bearer '.self::$readerToken]
            ]
        );
        $readerUrl = self::$baseUrl . '/api/book/'. $id . '/seealso';
        $data = $curl->get($readerUrl, null, $options);
        $items = json_decode($data);
        $formatItems = array();
        foreach ($items->body->items as $index => $item) {
            $formatItems[] = array(
                'book_id' => (string)$item->book_id,
                'start_page' => (string)$item->start_page,
                'link_name' => $item->link_name,
                'link_url' => $item->link_url,
                'unique_id' => ++$index
            );
        }
        return array(
            'body' => json_encode($formatItems)
        );
    }

    public static function toc_videos_returns()
    {
        return new external_single_structure(
            array(
                'body' => new external_value(PARAM_RAW, 'Needed TOC videos')
            )
        );
    }

    public static function regexReplace($data)
    {
        $patternHref = '/href=((\'|")(\/))/';
        $replaceHref = 'href="'.self::$readerUrl.'/';
        $patternSrc = '/src=((\'|")(\/))/';
        $replaceSrc = 'src="'.self::$readerUrl.'/';
        $patternImg = '/(<img)/';
        $replaceImg = '<img referrerpolicy="unsafe-url"';
        $data = preg_replace($patternImg, $replaceImg, $data);
        $data = preg_replace($patternHref, $replaceHref, $data);
        $data = preg_replace($patternSrc, $replaceSrc, $data);
        return $data;
    }
}
