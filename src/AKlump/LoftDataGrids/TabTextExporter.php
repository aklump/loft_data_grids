<?php
namespace AKlump\LoftDataGrids;

/**
 * Class TabTextExporter
 */
class TabTextExporter extends CSVExporter implements ExporterInterface {
  protected $extension = '.txt';

  /**
   * Constructor
   */
  public function __construct(ExportDataInterface $data = NULL, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new \stdClass;
    $this->format->bol    = '';
    $this->format->eol    = "\r\n";
    $this->format->left   = '';
    $this->format->right  = '';
    $this->format->sep    = "\t";
    $this->format->escape = '\\';
    $this->format->html   = FALSE;
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Tab-delimited Text Format',
      'shortname' => 'Tabbed Text', 
      'description' => 'Export data in the .txt file format.  Fields separated with tabs.  Lines are separated by \r\n',
    ) + $info;

    return $info;
  }
}