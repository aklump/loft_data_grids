<?php
namespace AKlump\LoftDataGrids;

/**
 * Class HtmlListExporter
 */
class HtmlListExporter extends Exporter implements ExporterInterface {

    protected $extension = '.html';

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'HTML List',
                'shortname'   => 'HTML List',
                'description' => 'Export data in HTML list format.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $pages = $this->getData()->get();
        $this->output = array();

        // Apply spacing and build output
        foreach ($pages as $page_id => $page) {
            if ($this->getShowPageIds()) {
                $tag = $this->getSettings()->pageTag;
                $this->output[] = "<{$tag}>$page_id</{$tag}>";
            }
            foreach ($page as $record) {
                $this->output[] = "<hr />";
                $class = $this->cssSafe($page_id);
                $data = str_replacE('"', '\"', $page_id);
                $this->output[] = "<table class=\"page {$class}\" data-page=\"{$data}\">";
                $this->output[] = "<tbody>";
                $odd = true;
                foreach ($record as $key => $value) {
                    $zebra = $odd ? 'odd' : 'even';
                    $odd = !$odd;
                    $this->output[] = "<tr class=\"$zebra\"><td class=\"key\">$key</td><td class=\"value\">$value</td></tr>";
                }
                $this->output[] = "</tbody>";
                $this->output[] = "</table>";
            }
        }

        $this->output = implode(PHP_EOL, $this->output);

        return $this;
    }

    protected function setSettingsDefault()
    {
        parent::setSettingsDefault();
        $this->settings->pageTag = 'h2';

        return $this;
    }
}
