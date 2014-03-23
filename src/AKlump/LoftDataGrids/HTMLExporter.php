<?php
namespace AKlump\LoftDataGrids;

/**
 * Class HTMLExporter
 */
class HTMLExporter extends CSVExporter implements ExporterInterface {
  protected $extension = '.html';
  public $format;

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'HTML Format',
      'shortname' => 'HTML', 
      'description' => 'Export data in the .html file format.',
    ) + $info;

    return $info;
  }  

  /**
   * Constructor
   */
  public function __construct(ExportData $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new \stdClass;
    $this->format->bol      = "<tr>";
    $this->format->cr       = "\n";
    $this->format->eol      = "</tr>" . $this->format->cr;
    $this->format->sep      = '';
    $this->format->escape   = '';
    $this->format->html     = TRUE;
    $this->format->snippet  = TRUE;
    $this->format->html     = '<!DOCTYPE html>

  <html>
  <head>
      <title></title>
      <style type="text/css"></style>
  </head>

  <body></body>
  </html>';

    $this->format->css = 'body {
  font-family: Helvetica, arial, sans-serif;
  font-size: 14px;
  line-height: 1.6;
  padding-top: 10px;
  padding-bottom: 10px;
  background-color: white;
  padding: 30px; }
caption {
  text-align: left;
  color: #999999;
  font-size: 18px; }
table {
  padding: 0;
  margin-bottom: 30px;
  border-collapse: collapse; }
  table tr {
    border-top: 1px solid #cccccc;
    background-color: white;
    margin: 0;
    padding: 0; }
    table tr:nth-child(2n) {
      background-color: #f8f8f8; }
    table tr th {
      font-weight: bold;
      border: 1px solid #cccccc;
      margin: 0;
      padding: 6px 13px; }
    table tr td {
      border: 1px solid #cccccc;
      margin: 0;
      padding: 6px 13px; }
    table tr th :first-child, table tr td :first-child {
      margin-top: 0; }
    table tr th :last-child, table tr td :last-child {
      margin-bottom: 0; }';
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
      $tables[] = '<table>' . $page_title . $this->format->cr . $this->output . '</table>' . $this->format->cr;
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
