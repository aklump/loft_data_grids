<?php
namespace AKlump\LoftDataGrids;

/**
 * Class FlatTextExporter
 */
class FlatTextExporter extends CSVExporter implements ExporterInterface {
  protected $extension = '.txt';
  protected $format;

  /**
   * Constructor
   */
  public function __construct(ExportDataInterface $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new \stdClass;
    $this->format->cr     = "\n";
    $this->format->hline  = "-";
    $this->format->vline  = "|";
    $this->format->bol    = $this->format->vline;
    $this->format->eol    = $this->format->vline . $this->format->cr;
    $this->format->left   = ' ';
    $this->format->right  = ' ';
    $this->format->sep    = $this->format->vline;
    $this->format->escape = '';
    $this->format->html   = TRUE;
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Monospace Flatfile Text',
      'shortname' => 'Flat Text', 
      'description' => 'Export data in a plain-text format.  Columns and rows are drawn with text pipes and hyphens.  Best results when using monospaced fonts.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $this->output = '';
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    foreach ($pages as $page_id => $data) {
      $header = $this->getHeader($page_id);
      foreach ($header as $key => $title) {
        $header[$key] = strtoupper($title);
      }
      $header = array_combine(array_keys(reset($data)), $header);
      array_unshift($data, $header);

      // Scan the data to determine the total width of each column
      $columns = array();
      foreach ($data as $row) {
        foreach ($row as $key => $value) {
          if (empty($columns[$key])) {
            $columns[$key] = 0;
          }
          $columns[$key] = max($columns[$key], strlen($value));
        }
      }

      // Pad all the cells based on our determination from above
      foreach ($data as $row_key => $row) {
        foreach ($row as $key => $value) {
          $data[$row_key][$key] = str_pad($value, $columns[$key], ' ');
        }
      }

      // Determine the width of a single row in chars
      $row_width  = 0;
      $row_width += array_sum($columns);
      $row_width += strlen($this->format->bol);
      $row_width += strlen($this->format->left) * count($columns);
      $row_width += strlen($this->format->sep) * count($columns) - 2;
      $row_width += strlen($this->format->right) * count($columns);
      $row_width += strlen($this->format->eol);
      $hrule = str_repeat($this->format->hline, $row_width);

      // Build the output
      $this->output .= $hrule . $this->format->cr;
      foreach ($data as $row) {
        $this->output .= $this->collapseRow($row);
        $this->output .= $hrule . $this->format->cr;
      }
    }
  }
}