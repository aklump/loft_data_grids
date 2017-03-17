<?php
namespace AKlump\LoftDataGrids;

/**
 * Class TextListExporter
 */
class TextListExporter extends Exporter implements ExporterInterface {

    public $line_break = '-';
    public $separator = '  ';
    public $pad_char = ' ';
    protected $extension = '.txt';

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'Plaintext List',
                'shortname'   => 'List',
                'description' => 'Export data in plaintext list file format.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $pages = $this->getData()->get();
        $this->output = '';
        $output = '';
        $longest_key = $longest_value = 0;

        // Determine spacing
        foreach ($pages as $page_id => $page) {
            foreach ($page as $record) {
                foreach ($record as $key => $value) {
                    $longest_key = max($longest_key, strlen($key));
                    $longest_value = max($longest_value, strlen($value));
                }
            }
        }

        // Apply spacing and build output
        foreach ($pages as $page_id => $page) {
            if ($this->getShowPageIds()) {
                $output .= $page_id . PHP_EOL;
            }
            foreach ($page as $record) {
                $output .= "<hr />\n";
                foreach ($record as $key => $value) {
                    $output .= str_pad($key, $longest_key, $this->pad_char) . $this->separator . $value . PHP_EOL;
                }
                $output .= "\n";
            }
            $output .= "\n";
        }

        $line_break = str_repeat($this->line_break, $longest_key + strlen($this->separator) + $longest_value + 2);
        $output = str_replace('<hr />', $line_break, $output);

        $this->output = $output;

        return $this;
    }
}
