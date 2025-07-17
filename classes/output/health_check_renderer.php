<?php
/**
 *
 * @package     mod_lanebs
 * @category    HealthCheck
 * @copyright   2024 Mazitov Artem
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_lanebs\output;

use html_table;
use html_writer;
use mod_lanebs\HealthCheck\HealthCheckDto;
use plugin_renderer_base;

final class health_check_renderer extends plugin_renderer_base
{
    /**
     * @var string
     */
    protected $titleTag = 'h2';

    public function head(): string
    {
        return $this->header() . $this->heading($this->page->title);
    }

    public function index(HealthCheckDto $info): string
    {
        $html = $this->head();
        $html .= $this->info($info);
        $html .= $this->getHostTable($info->list);
        $html .= $this->displayAuth($info->token, $info->auth);
        $html .= $this->servicesList($info->listWsf);
        $html .= $this->footer();
        return $html;
    }

    public function info(HealthCheckDto $info): string
    {
        $table = new html_table();
        $table->data = [
            ['Plugin', $info->version],
            ['PHP', $info->phpVersion],
            ['Moodle', $info->moodleVersion],
            ['OS type', $info->osType],
        ];
        return html_writer::tag($this->titleTag, 'System info') . html_writer::table($table);
    }

    public function getHostTable(array $list): string
    {
        if (!$list) {
            return '';
        }

        $table = new html_table();
        $table->head = ['Host', 'code', 'raw curl code'];
        foreach ($list as $item) {
            [$name, $code, $result] = $item;
            $status = $code === 200 ? '✅' : '❌';
            $result = implode('<br>', $result);
            $table->data[] = [$name, $code . $status, $result];
        }

        return html_writer::tag($this->titleTag, 'Host check') . html_writer::table($table);
    }

    public function displayAuth(?string $token, ?string $auth): string
    {
        $html = html_writer::tag($this->titleTag, 'Token:');
        $attr = [
            'class' => 'w-100',
            'value' => $token
        ];
        $html .= html_writer::empty_tag('input', $attr);

        $html .= html_writer::tag($this->titleTag, 'Auth:');
        $attr = [
            'class' => 'w-100',
            'rows' => 10
        ];
        if (is_null($auth)) {
            $auth = 'null';
        }
        $html .= html_writer::tag('textarea', $auth, $attr);

        return $html;
    }

    public function servicesList(array $servicesList): string
    {
        $table = new html_table();
        foreach ($servicesList as $item) {
            [$name, $status] = $item;
            $status = $status ? '✅' : '❌';
            $table->data[] = [$name, $status];
        }

        return html_writer::tag('h2', 'Web service functions') . html_writer::table($table);
    }
}