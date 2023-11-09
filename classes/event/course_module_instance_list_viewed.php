<?php

/**
 * The mod_lanebs instance list viewed event.
 *
 * @package    mod_lanebs
 * @copyright  2022 Senin Yurii
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_lanebs\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_lanebs instance list viewed event class.
 *
 * @package    mod_lanebs
 * @since      Moodle 3.7
 * @copyright  2022 onwards Senin Yurii
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_module_instance_list_viewed extends \core\event\course_module_instance_list_viewed {


    /**
     * Create the event from course record.
     *
     * @param \stdClass $course
     * @return course_module_instance_list_viewed
     * @throws \coding_exception
     */
    public static function create_from_course(\stdClass $course) {
        $params = array(
            'context' => \context_course::instance($course->id)
        );
        $event = self::create($params);
        $event->add_record_snapshot('course', $course);
        return $event;
    }}

