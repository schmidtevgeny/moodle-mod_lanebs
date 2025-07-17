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

interface HealthCheckInterface
{
    public function checkAllSystem(array $list = []): HealthCheckDto;

    public function getOsType(): string;

    public function getMoodleVersion(): string;

    public function checkHostList(array $list): array;

    public function getToken(): ?string;

    public function rawAuth(): ?string;

    public function checkService(): array;
}