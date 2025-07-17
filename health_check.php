<?php
/**
 *
 * @package     mod_lanebs
 * @category    health_check
 * @copyright   2024 Mazitov Artem
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

use mod_lanebs\HealthCheck\HealthCheckFactory;
use mod_lanebs\output\health_check_renderer;


/**
 * @global \stdClass $CFG
 * @global \moodle_page $PAGE
 */
include __DIR__ . '/../../config.php';
require_once $CFG->libdir . '/adminlib.php';

require_login();
admin_externalpage_setup('mod_lanebs_health_check');

$health = HealthCheckFactory::create();
/** @var health_check_renderer $renderer */
$renderer = $PAGE->get_renderer('mod_lanebs', 'health_check');
$list = [
    'https://c.lanbook.com',
    'https://security.lanbook.com',
    'https://moodle-api.e.lanbook.com',
    'http://212.41.20.23:8080',
    'https://reader.lanbook.com',
    'https://profile.e.lanbook.com',
];
echo $renderer->index($health->checkAllSystem($list));
