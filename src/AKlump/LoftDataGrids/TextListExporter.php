<?php
namespace AKlump\LoftDataGrids;

/**
 * Class TextListExporter
 */
class TextListExporter extends Exporter implements ExporterInterface {
  protected $extension = '.txt';

  public $line_break  = '-';
  public $separator   = '  ';
  public $pad_char    = ' ';

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Plaintext List',
      'shortname' => 'List', 
      'description' => 'Export data in plaintext list file format.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    $this->output = '';
    $output  = '';
    $longest_key = $longest_value = 0;

    // Determine spacing
    foreach ($pages as $page_id => $page) {
      foreach ($page as $record) {
        foreach ($record as $key => $value) {
          $longest_key    = max($longest_key, strlen($key));
          $longest_value  = max($longest_value, strlen($value));
        }
      }
    }

    // Apply spacing and build output
    foreach ($pages as $page_id => $page) {
      foreach ($page as $record) {
        $output .= "<hr />\n";
        foreach ($record as $key => $value) {
          $output .= str_pad($key, $longest_key, $this->pad_char) . $this->separator . $value . "\n";
        }
        $output .= "\n";
      }
      $output .= "\n";
    }

    $line_break = str_repeat($this->line_break, $longest_key + strlen($this->separator) + $longest_value + 2);
    $output = str_replace('<hr />', $line_break, $output);

    $this->output = $output;
  }
}