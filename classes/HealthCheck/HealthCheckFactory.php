<?php
/**
 *
 * @package     mod_lanebs
 * @category    HealthCheck
 * @copyright   2024 Mazitov Artem
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_lanebs\HealthCheck;

use curl;

/** @global \stdClass $CFG */
require_once $CFG->dirroot . '/lib/filelib.php';

final class HealthCheckFactory
{
    public static function create(): HealthCheckInterface
    {
        return new HealthCheck(new curl(['ignoresecurity' => true]));
    }
}