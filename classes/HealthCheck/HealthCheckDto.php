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
 *
 * @package     mod_lanebs
 * @category    HealthCheck
 * @copyright   2024 Mazitov Artem
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_lanebs\HealthCheck;

final class HealthCheckDto
{
    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $phpVersion;

    /**
     * @var string
     */
    public $moodleVersion;

    /**
     * @var string
     */
    public $osType;

    /**
     * @var array
     */
    public $list;

    /**
     * @var array
     */
    public $listWsf;

    /**
     * @var string|null
     */
    public $token;

    /**
     * @var string|null
     */
    public $auth;

    public function __construct(
        string  $version,
        string  $phpVersion,
        string  $moodleVersion,
        string  $osType,
        array   $list,
        array   $listWsf,
        ?string $token,
        ?string $auth
    )
    {
        $this->version = $version;
        $this->phpVersion = $phpVersion;
        $this->moodleVersion = $moodleVersion;
        $this->osType = $osType;
        $this->list = $list;
        $this->listWsf = $listWsf;
        $this->token = $token;
        $this->auth = $auth;
    }
}