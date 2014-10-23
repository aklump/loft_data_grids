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
  public function __construct(ExportDataInterface $data = NULL, $filename = '') {
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
    $this->hidePageIds();
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
      if ($this->getShowPageIds()) {
        $this->output .= '-- ' . strtoupper($page_id) . ' --' . $this->format->cr;
      }      
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

        // A cell cannot contain line breaks so we replace them
        $cell = preg_replace('/\r\n|\r|\n/', '; ', $cell);

        $output[] = $this->format->left . $cell . $this->format->right;
      }
    }
    $output = $this->format->bol . implode($this->format->sep, $output) . $this->format->eol;

    return $output;
  }  
}