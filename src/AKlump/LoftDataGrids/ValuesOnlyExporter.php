<?php
namespace AKlump\LoftDataGrids;

/**
 * Represents a values only exporter with no record dividers.
 */
class ValuesOnlyExporter extends Exporter implements ExporterInterface {

  public $extension = '.txt';
  public $format;

  public function __construct(ExportDataInterface $data = NULL, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new \stdClass;
    $this->format->eol    = "\r\n";
    $this->format->sep    = "\\";
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Values-only List',
      'shortname' => 'Values List', 
      'description' => 'Plaintext list of values',
    ) + $info;

    return $info;
  }  
  
  public function compile($page_id = NULL) {
    if (isset($page_id)) {
      $pages = array($page_id => $this->getData()->getPage($page_id));
    }
    else {
      $pages = $this->getData()->get();
    }

    $values = array();
    foreach ($pages as $records) {
      foreach ($records as $record) {
        $line = array();
        foreach ($record as $value) {
          $line[] = $value;
        }
        $values[] = implode($this->format->sep, $line);
      }
    }
    $this->output = implode($this->format->eol, $values);
  }
}