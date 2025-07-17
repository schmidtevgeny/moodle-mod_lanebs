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

use core_plugin_manager;
use curl;

/** @global $CFG */
require $CFG->dirroot . '/mod/lanebs/externallib.php';

final class HealthCheck implements HealthCheckInterface
{
    /**
     * @var curl
     */
    protected $client;

    public function __construct(curl $client)
    {
        $this->client = $client;
    }

    public function getPluginVersion(): string
    {
        $allPlugins = core_plugin_manager::instance()
            ->get_present_plugins('mod');
        $plugin = $allPlugins['lanebs'];

        return $plugin->release . ' (' . $plugin->version . ')';
    }

    public function checkAllSystem(array $list = []): HealthCheckDto
    {
        $version = $this->getPluginVersion();
        return new HealthCheckDto(
            $version,
            PHP_VERSION,
            $this->getMoodleVersion(),
            $this->getOsType(),
            $this->checkHostList($list),
            $this->checkService(),
            $this->getToken(),
            $this->rawAuth()
        );

    }

    public function getOsType(): string
    {
        global $CFG;
        return $CFG->ostype;
    }

    public function getMoodleVersion(): string
    {
        global $CFG;
        return $CFG->release;
    }

    public function checkHostList(array $list): array
    {
        $result = [];
        foreach ($list as $item) {
            $result[] = $this->checkServer($item);
        }

        return $result;
    }

    protected function checkServer(string $item): array
    {
        $this->client->get($item);
        /** @var array $info */
        $info = $this->client->get_info();
        $code = $info['http_code'] ?? 0;
        $cmd = "curl --insecure -vvI $item 2>&1 | grep \"^HTTP\|start date:\|expire date:\"";
        exec($cmd, $output);
        return [
            $item,
            $code,
            $output
        ];
    }

    public function getToken(): ?string
    {
        $tmp = get_config('lanebs', 'token');
        return $tmp ?: null;
    }

    public function rawAuth(): ?string
    {
        $_SESSION['mod_lanebs_subscriberToken'] = $this->getToken();
        return json_encode(\mod_lanebs_external::auth());
    }

    public function checkService(): array
    {
        global $DB;
        $result = [];
        $list = $this->getServiceList();
        foreach ($list as $function) {
            $result[] = [
                $function,
                $DB->record_exists('external_functions', ['name' => $function])
            ];
        }

        return $result;
    }

    protected function getServiceList(): array
    {
        global $CFG;
        require $CFG->dirroot . '/mod/lanebs/db/services.php';
        /** @var array $functions */
        return array_keys($functions);
    }
}