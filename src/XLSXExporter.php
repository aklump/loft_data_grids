<?php

namespace AKlump\LoftDataGrids;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use RuntimeException;

/**
 * Class XLSXExporter
 *
 * http://www.phpexcel.net
 */
class XLSXExporter extends Exporter implements ExporterInterface {

  protected $extension = '.xlsx';

  protected $sheets = array();

  /**
   * @var $worksheet
   * The \PhpOffice\PhpSpreadsheet\Spreadsheet object
   */
  protected $excel;

  /**
   * Constructor
   *
   * @param ExportDataInterface $data
   * @param string $filename
   *   (Optional) Defaults to ''.
   * @param array $properties
   */
  public function __construct(ExportDataInterface $data = NULL, $filename = '', $properties = array()) {
    parent::__construct($data, $filename);
    $this->output = FALSE;
    $this->excel = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    if ($properties) {
      $this->setProperties($properties);
    }
  }

  /**
   * Set properties
   *
   * @param array $properties
   *   - Title
   *   - Creator
   *   - LastModifiedBy
   *   - Description
   *   - Keywords
   *   - Category
   *
   * @return $this
   */
  public function setProperties($properties) {
    $obj = $this->excel->getProperties();
    foreach ($properties as $property_name => $value) {
      $method = "set$property_name";
      if (method_exists($obj, $method)) {
        $obj->{$method}($value);
      }
    }

    return $this;
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
        'name' => 'Excel Format',
        'shortname' => 'Excel',
        'description' => 'Export data in the .xlsx file format.',
      ) + $info;

    return $info;
  }

  public function setTitle($title) {
    parent::setTitle($title);
    $this->excel->getProperties()->setTitle($title);
  }

  /**
   * Returns the \PhpOffice\PhpSpreadsheet\Spreadsheet object
   *
   * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
   */
  public function export($page_id = NULL) {
    return $this->excel;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    $this->output = !empty($pages);

    foreach ($pages as $page_id => $data) {

      if (empty($this->sheets)) {
        $this->sheets[] = $page_id;
      }
      elseif (!in_array($page_id, $this->sheets)) {
        $this->sheets[] = $page_id;
        $this->excel->createSheet();
      }
      // Assure our active sheet is an integar
      $active_sheet = array_search($page_id, $this->sheets);
      $this->excel->setActiveSheetIndex($active_sheet);
      $worksheet = $this->excel->getActiveSheet();

      //@todo The column count is wrong when sheets differ in the column count.

      // Format the header row:
      $header = $this->getHeader($page_id);

      // Format the rows:
      $row_index = 1;
      $worksheet->fromArray($header, NULL, 'A' . $row_index++, TRUE);
      foreach ($data as $row) {
        $worksheet->fromArray($row, NULL, 'A' . $row_index, TRUE);
        $row_index++;
      }
    }

    return $this;
  }

  public function save($filename = '', $page_id = NULL) {
    // Make sure we have rendered the data
    if (empty($this->output)) {
      $this->compile($page_id);
    }

    // Assure the correct file extension
    if ($filename) {
      $this->setFilename($filename);
    }

    // Redirect output to a client’s web browser (Xlsx)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $this->getFilename() . '"');
    header('Cache-Control: max-age=0');

    $objWriter = IOFactory::createWriter($this->excel, 'Xlsx');
    $objWriter->save('php://output');
    exit;
  }

  public function saveFile($directory, $filename = '', $page_id = NULL) {
    // Go through the setter to ensure the file_extension.
    $filename = $filename ? $this->setFilename($filename) : $this->getFilename();
    if (!is_writable(($directory))) {
      throw new RuntimeException("$directory is not writable; cannot save $filename.");
    }
    // Make sure we have rendered the data
    if (empty($this->output)) {
      $this->compile($page_id);
    }
    $path = $directory . '/' . $filename;
    $objWriter = IOFactory::createWriter($this->excel, 'Xlsx');
    $objWriter->save($path);

    return $path;
  }

  private function getPhpSpreadsheetNumberFormat() {
    if (defined('\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_CURRENCY_USD_INTEGER')) {
      return NumberFormat::FORMAT_CURRENCY_USD_INTEGER;
    }

    return NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
  }

  /**
   * Format a single column with a number format
   *
   * You must do this after calling $this->compile!
   *
   * @param string $column
   * @param string $format_code You may use "USD" or any value from \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_*
   *
   * @return $this
   *
   * @see \PhpOffice\PhpSpreadsheet\Style\NumberFormat::setFormatCode
   */
  public function formatColumn($column, $format_code) {

    // This will be used to convert to USD for our parent call, see below...
    $phpexcel_usd_format = $this->getPhpSpreadsheetNumberFormat();

    // Normalize to Excel USD format.
    if (empty($format_code) || 'USD' === strtoupper($format_code)) {
      $format_code = $phpexcel_usd_format;
    }

    $columns = $this->getPHPExcelColumns();
    if (!array_key_exists($column, $columns)) {
      return $this;
    }
    $phpexcel_column = $columns[$column];
    $page = $this->excel->getActiveSheet();
    foreach ($page->getRowIterator() as $row) {
      $row_index = $row->getRowIndex();
      $page->getStyle("$phpexcel_column$row_index")
        ->getNumberFormat()
        ->setFormatCode($format_code);
    }

    // The parent method does not know about
    // \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_* so if we can
    // convert that constant to 'USD' we will do that here.
    switch ($format_code) {
      case $phpexcel_usd_format;
      case '"$"#,##0_-':
      case '$#,##0_-':
        $format_code = 'USD';
        break;
    }

    return parent::formatColumn($column, $format_code);
  }

  /**
   * Convert a column key into an excel column, e.g. A, AB, etc.
   *
   * @param string $column
   *
   * @return string
   */
  public function getPHPExcelColumns() {

    // @todo This will not work past 26 columns; fix!!!!
    $columns = array();
    $current = array(65);
    $chr = &$current[count($current) - 1];
    foreach ($this->getHeader() as $header_key) {
      $columns[$header_key] = chr($chr++);
    }

    return $columns;
  }

  /**
   * Return the value of a single property or NULL
   *
   * @param string $property_name e.g. Creator
   *
   * @return mixed
   */
  public function getProperty($property_name) {
    $obj = $this->excel->getProperties();
    $method = "get$property_name";

    return method_exists($obj, $method) ? $obj->$method() : NULL;
  }
}
