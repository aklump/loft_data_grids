<?php
namespace AKlump\LoftDataGrids;

/**
 * Class CSVExporter
 */
class CSVExporter extends Exporter implements ExporterInterface {
  protected $extension = '.csv';
  protected $format;
  protected $page = NULL;

  /**
   * Constructor
   */
  public function __construct(ExportDataInterface $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new \stdClass;
    $this->format->bol    = '';
    $this->format->eol    = "\r\n";
    $this->format->left   = '"';
    $this->format->right  = '"';
    $this->format->sep    = ',';
    $this->format->escape = '"';
    $this->format->html   = FALSE;
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Comma-separated Values Format',
      'shortname' => 'CSV', 
      'description' => 'Export data in the .csv file format.  Fields are wrapped with double quotes, separated by commas.  Lines are separated by \r\n',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    if (($page_id === NULL
        && count($pages) > 1)
        || !array_key_exists($page_id, $pages)) {
      reset($pages);
      $page_id = key($pages);
    }
    $data = $this->getData()->getPage($page_id);
    $this->output = '';
    $this->output .= $this->collapseRow($this->getHeader($page_id));
    // Format the rows:
    foreach ($data as $row) {
      $this->output .= $this->collapseRow($row);
    }
  }

  /**
   * Collapse a row
   */
  protected function collapseRow($row) {
    $output = '';

    // Check if we're dealing with a simple or complex row
    if (isset($row['data'])) {
      foreach ($row as $key => $value) {
        if ($key == 'data') {
          $cells = $value;
        }
      }
    }
    else {
      $cells = $row;
    }
    $output = array();
    if (count($cells)) {
      foreach ($cells as $cell) {
        //compress a complex cell
        if (is_array($cell)) {
          $cell = isset($cell['data']) ? $cell['data'] : '';
        }

        if (!$this->format->html) {
          $cell = strip_tags($cell);
        }

        // Escape chars that conflice with delimiters
        if (!empty($this->format->escape)) {
          $escapeables = array($this->format->left, $this->format->right);
          $escapeables = array_filter(array_unique($escapeables));
          foreach ($escapeables as $find) {
            $cell = str_replace($find, $this->format->escape . $find, $cell);
          }
        }
        $output[] = $this->format->left . $cell . $this->format->right;
      }
    }
    $output = $this->format->bol . implode($this->format->sep, $output) . $this->format->eol;
    return $output;
  }
}