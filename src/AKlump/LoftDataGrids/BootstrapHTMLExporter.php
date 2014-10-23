<?php
namespace AKlump\LoftDataGrids;

/**
 * Class HTMLExporter
 */
class BootstrapHTMLExporter extends HTMLExporter implements ExporterInterface {
  protected $extension = '.html';
  public $format;

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'HTML for Bootstrap Format',
      'shortname' => 'HTML', 
      'description' => 'Export data in the .html file format using Bootstrap markup.',
    ) + $info;

    return $info;
  }  

  /**
   * Constructor
   */
  public function __construct(ExportData $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format->bol      = "<tr>";
    $this->format->eol      = "</tr>" . $this->format->cr;
    $this->format->html     = '<!DOCTYPE html>

  <html>
  <head>
      <title></title>
      <style type="text/css"></style>
  </head>

  <body></body>
  </html>';

    $this->format->css = NULL;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    $tables = array();
    foreach ($pages as $page_id => $data) {
      $this->output = '';
      $this->output .= '<thead>' . $this->format->cr;
      $this->format->left = '<th>';
      $this->format->right = '</th>';
      $this->output .= $this->collapseRow($this->getHeader($page_id));
      $this->output .= '</thead>' . $this->format->cr;

      // Format the rows:
      $this->format->left = '<td>';
      $this->format->right = '</td>';
      $this->output .= '<tbody>' . $this->format->cr;
      foreach ($data as $row) {
        $this->output .= $this->collapseRow($row);
      }
      $this->output .= '</tbody>' . $this->format->cr;

      $page_title = '';
      if (count($pages) > 1 && $this->getShowPageIds()) {
        $page_title = '<caption>' . $page_id . '</caption>';
      }
      $tables[] = '<table class="table">' . $page_title . $this->format->cr . $this->output . '</table>' . $this->format->cr;
    }
    $this->output = implode($this->format->cr, $tables);

    // Now decide if this is a snippet or full document
    if (!$this->format->snippet) {
      $snippet = $this->output;
      $this->output = $this->format->html;
      $this->output = str_replace('<style type="text/css"></style>', '<style type="text/css">' . $this->format->css . '</style>', $this->output);
      $this->output = str_replace('<body></body>', '<body>' . $snippet . '</body>', $this->output);
      $this->output = str_replace('<title></title>', '<title>' . $this->title . '</title>', $this->output);
    }
  }
}
