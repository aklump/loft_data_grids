<?php
namespace AKlump\LoftDataGrids;

/**
 * Class FlatTextExporter
 */
class MarkdownTableExporter extends FlatTextExporter implements ExporterInterface {
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
    $this->showPageIds();
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Advanced Markdown Table',
      'shortname' => 'Markdown Table', 
      'description' => 'Export data in markdown table format.',
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
        $this->output .= '## ' . $page_id . $this->format->cr;
      }
      $header = $this->getHeader($page_id);
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
      $hrule = array();
      foreach ($data as $row_key => $row) {
        foreach ($row as $key => $value) {
          $data[$row_key][$key] = str_pad($value, $columns[$key], ' ');
          if (empty($hrule[$key])) {
            $hrule[$key] = $this->format->vline . str_pad('', 2 + $columns[$key], $this->format->hline);
          }
        }
      }

      $hrule = implode($hrule) . $this->format->vline;

      // Build the output
      $header = FALSE;
      foreach ($data as $row) {
        $this->output .= $this->collapseRow($row);
        if (!$header) {
          $this->output .= $hrule . $this->format->cr;
          $header = TRUE;
        }
      }

      $this->output .= $this->format->cr;
    }
  }
}
