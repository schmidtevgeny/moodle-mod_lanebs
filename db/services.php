<?php

$services = array(
    'lab_ebsservice' => array(
        'functions' => array('mod_lanebs_search_books', 'mod_lanebs_book_content', 'mod_lanebs_category_tree', 'mod_lanebs_auth', 'mod_lanebs_lanebs_info', 'mod_lanebs_toc_name', 'mod_lanebs_toc_videos'),
        'requiredcapability' => ['mod_lanebs:get_tree'],
        'restrictedusers' => 1,
        'enabled' => 1,
        'shortname' => 'LanEbsIntegration',
        'downloadfiles' => 0,
        'uploadfiles' => 0,
    ),
);

$functions = array(
    'mod_lanebs_search_books' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'search_books',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Get book list',
        'type' => 'read',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree')
    ),
    'mod_lanebs_book_content' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'book_content',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Get reader with book content',
        'type' => 'read',
        'ajax' => true,
        'service' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree')
    ),
    'mod_lanebs_category_tree' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'category_tree',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Get category tree',
        'type' => 'read',
        'ajax' => true,
        'service' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree'),
    ),
    'mod_lanebs_auth' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'auth',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Authorized in ELS',
        'type'  => 'read',
        'ajax'  => true,
        'service' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree'),
    ),
    'mod_lanebs_lanebs_info' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'lanebs_info',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Get information about lanebs module',
        'type'  => 'read',
        'ajax'  => true,
        'service' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree'),
    ),
    'mod_lanebs_toc_name' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'toc_name',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Get TOC by page number',
        'type' => 'read',
        'ajax' => true,
        'service' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree'),
    ),
    'mod_lanebs_toc_videos' => array(
        'classname' => 'mod_lanebs_external',
        'methodname' => 'toc_videos',
        'classpath' => 'mod/lanebs/externallib.php',
        'description' => 'Get TOC videos by book ID',
        'type' => 'read',
        'ajax' => true,
        'service' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
        'capabilities' => array('lanebs:get_tree'),
    ),
);