<?php
/**
 * @file
 * Export functionality
 *
 * @ingroup loft_data_grids
 * @{
 */

if (is_file(dirname(__FILE__) . '/../vendor/autoload.php')) {
  require_once dirname(__FILE__) . '/../vendor/autoload.php';
}

/**
 * Interface ExportDataInterface
 */
interface ExportDataInterface {

  /**
   * Add data to the current record
   *
   * @param string $key
   * @param string $value
   *
   * @return $this
   */
  public function add($key, $value);

  /**
   * Returns the record index and advances the record pointer
   *
   * @return int
   *   The index of the record that was just closed
   */
  public function next();

  /**
   * Advance the record pointer to a given index
   *
   * @param int $index
   *
   * @return $this
   */
  public function setPointer($index);

  /**
   * Get the data
   *
   * @return array
   */
  public function get();

  /**
   * Normalize all rows so that they all contain the same number of columns, the
     column order will be taken from the order of the first column
   *
   * @param array | mixed $empty_value
   *   If this is an array then you may provide default values by column key, as
       well as the special key '#default' which is the default value for any
       column whose key is not present in $empty_value.
       If this is not an array, then the value will be used for all empty values
       of all columns, rows, cells, etc.
   *
   * @return $this
   */
  public function normalize($empty_value);

  /**
   * Merge another ExportData object into this one
   *
   * - Rows will be merged by key
   * - All rows will be expanded to have all columns
   *
   * @param ExportData $data
   * @param mixed $empty_value
   *
   * @return $this
   *
   * @see ExportData::normalize()
   */
  public function merge(ExportData $data, $empty_value);
}

/*
 * Class ExportData
 */
class ExportData implements ExportDataInterface {

  protected $data = array();
  protected $current_record = 0;

  public function add($key, $value) {
    $this->data[$this->current_record][$key] = $value;

    return $this;
  }

  public function next() {
    $return = $this->current_record;
    ++$this->current_record;

    return $return;
  }

  public function setPointer($index) {
    $this->current_record = $index;

    return $this;
  }

  public function get() {
    return $this->data;
  }

  public function normalize($empty_value) {
    $columns = array();

    // First discover all the columns
    foreach ($this->data as $row) {
      $columns += $row;
    }
    $columns = array_keys($columns);

    // Now go through and add all columns
    $column_order = array();
    foreach ($this->data as $key => $row) {

      if (is_array($empty_value)) {
        if (array_key_exists($key, $empty_value)) {
          $empty = $empty_value[$key];
        }
        elseif (array_key_exists('default', $empty_value)) {
          $empty = $empty_value['default'];
        }
        else {
          $empty = NULL;
        }
      }
      else {
        $empty = $empty_value;
      }

      $this->data[$key] += array_fill_keys($columns, $empty);

      // Assure the same column order for all rows
      if (empty($column_order)) {
        $column_order = array_keys($this->data[$key]);
      }
      $ordered = array();
      foreach ($column_order as $col_key) {
        $ordered[$col_key] = $this->data[$key][$col_key];
      }
      $this->data[$key] = $ordered;
    }
  }

  public function merge(ExportData $data, $empty_value) {

    // Normalize columns on incoming
    $this->normalize($empty_value);
    $data->normalize($empty_value);

    foreach ($data->get() as $key => $row) {
      $this->data[$key] += $row;
    }

    // Normalize result
    $this->normalize($empty_value);

    return $this;
  }
}

/**
 * Interface ExporterInterface
 */
interface ExporterInterface {

  /**
   * Set the export data object
   *
   * @param ExportData $data
   *
   * @return $this
   */
  public function setData(ExportData $data);

  /**
   * Getter/Setter for the filename
   *
   * @param string $filename
   *   Extension (if present in $filename) will be corrected based on the object
   *
   * @return string
   *   The final $filename with correct extension
   */
  public function setFilename($filename);

  /**
   * Export data as a string
   *
   * @return string
   */
  public function export();

  /**
   * Save as a file to the server
   *
   * @param string $filename
   *   The correct extension will be appended to this string
   */
  public function save($message = '');

  /**
   * Return the ExportData object
   *
   * @return ExportData
   */
  public function getData();

  /**
   * Return an array containing the header values
   *
   * @return array
   */
  public function getHeader();

}
/**
 * Class Exporter
 */
abstract class Exporter implements ExporterInterface {

  protected $export_data, $filename, $extension, $output;

  /**
   * Constructor
   * @param ExportData $data
   * @param string $filename
   *   (Optional) Defaults to ''.
   */
  public function __construct(ExportData $data, $filename = '') {
    $this->setData($data);
    $this->setFilename($filename);
  }

  public function setFilename($filename) {
    $info = pathinfo($filename);
    if ($info['filename']) {
      $filename = $info['filename'];
    }
    $this->filename = trim($filename, '.') . '.' . trim($this->extension, '.');

    return $this->filename;
  }

  public function getData() {
    return $this->data;
  }

  public function getHeader() {
    $data = $this->export_data->get();
    $header = array_keys(reset($data));
    return $header;
  }

  public function setData(ExportData $data) {
    $this->export_data = $data;

    return $this;
  }

  /**
   * Build $this->output in prep for export/save
   *
   * This is the main method to be extended for the different exporters
   */
  abstract public function compile();

  public function export() {
    $this->compile();

    return $this->output;
  }

  public function save($filename = '') {

    // Make sure we have rendered the data
    if (empty($this->output)) {
      $this->compile();
    }

    // Assure the correct file extension
    if ($filename) {
      $this->setFilename($filename);
    }

    // Make sure we don't timeout
    $original = (int) ini_get('memory_limit');
    $memory_limit = strlen($this->output); //bytes
    $memory_limit /= 1048576; //convert to megabytes
    $memory_limit *= 2; //double the memory so we don't run out
    if ($memory_limit > $original) {
      $memory_limit *= 20; //double the memory so we don't run out
      $memory_limit = max($original, round($memory_limit)) . 'M';
      ini_set('memory_limit', $memory_limit);
    }

    // Send download headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="'. $this->filename .'"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . strlen($this->output));

    // Send contents
    print $this->output;
    exit();

  }
}

/**
 * Class CSVExporter
 */
class CSVExporter extends Exporter implements ExporterInterface {
  protected $extension = '.csv';
  protected $format;

  /**
   * Constructor
   */
  public function __construct(ExportData $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new stdClass;
    $this->format->bol    = '';
    $this->format->eol    = "\r\n";
    $this->format->left   = '"';
    $this->format->right  = '"';
    $this->format->sep    = ',';
    $this->format->escape = '"';
    $this->format->html   = FALSE;
  }

  public function compile() {
    $data = $this->export_data->get();
    $this->output = '';
    $this->output .= $this->collapseRow($this->getHeader());
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

/**
 * Class XLSXExporter
 */
class XLSXExporter extends Exporter implements ExporterInterface {

  protected $extension = '.xlsx';

  /**
   * @var $worksheet
   * The PHPExcel object
   */
  protected $excel;

  /**
   * Constructor
   *
   * @param ExportData $data
   * @param string $filename
   *   (Optional) Defaults to ''.
   * @param array $properties
   */
  public function __construct(ExportData $data, $filename = '', $properties = array()) {
    parent::__construct($data, $filename);
    $this->output = FALSE;
    $this->excel = new PHPExcel();
    if ($properties) {
      $this->setProperties($properties);
    }
  }

  /**
   * Set properties
   *
   * @param array $properties
   *   - Creator
   *   - LastModifiedBy
   *   - Title
   *   - Description
   *   - Keywords
   *   - Category
   *
   * @return $this
   */
  public function setProperties($properties) {
    foreach ($properties as $key => $value) {
      if (($method = "set$key")
          && ($obj_properties = $this->excel->getProperties())
          && method_exists($obj_properties, $method)) {
        $obj_properties->{$method}($value);
      }
    }

    return $this;
  }

  public function compile() {
    $data = $this->export_data->get();
    $this->output = TRUE;

    $this->excel->setActiveSheetIndex(0);
    $worksheet = $this->excel->getActiveSheet();

    // Format the header row:
    $header = $this->getHeader();

    // Format the rows:
    $row_index = 1;
    $worksheet->fromArray($header, NULL, 'A' . $row_index++, TRUE);
    foreach ($data as $row) {
      $col_index = 'A';
      $worksheet->fromArray($row, NULL, 'A' . $row_index, TRUE);
      $row_index++;
    }
  }

  /**
   * Returns the PHPExcel object
   *
   * @return PHPExcel
   */
  public function export() {
    return $this->excel;
  }

  public function save($filename = '') {
    if (!$this->output) {
      $this->compile();
    }

    // Redirect output to a client’s web browser (Excel2007)
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $this->filename . '"');
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
  }

  /**
   * Format a single column with a number format
   *
   * You must do this after calling $this->compile!
   *
   * @param string $column
   * @param string $format_code
   *
   * @return $this
   */
  public function formatColumnNumberFormat($column, $format_code) {
    foreach ($this->excel->getActiveSheet()->getRowIterator() as $row) {
      $row_index = $row->getRowIndex();
      $this->excel->getActiveSheet()->getStyle("$column$row_index")->getNumberFormat()
      ->setFormatCode($format_code);
    }
    return $this;
  }

  /**
   * Return the header keyed by column letter beginning with 'A'
   *
   * @return array
   * - keys: column letters
   * - values: header strings
   */
  public function getHeader() {
    $header = parent::getHeader();
    $revised = array();
    $column = 'A';
    foreach ($header as $value) {
      $revised[$column++] = $value;
    }
    return $revised;
  }
}

/**
 * Class HTMLExporter
 */
class HTMLExporter extends CSVExporter implements ExporterInterface {
  protected $extension = '.html';
  protected $format;

  /**
   * Constructor
   */
  public function __construct(ExportData $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new stdClass;
    $this->format->bol    = "<tr>";
    $this->format->cr     = "\n";
    $this->format->eol    = "</tr>" . $this->format->cr;
    $this->format->sep    = '';
    $this->format->escape = '';
    $this->format->html   = TRUE;
  }

  public function compile() {
    $data = $this->export_data->get();
    $this->output = '';
    $this->output .= '<thead>' . $this->format->cr;
    $this->format->left = '<th>';
    $this->format->right = '</th>';
    $this->output .= $this->collapseRow($this->getHeader());
    $this->output .= '</thead>' . $this->format->cr;

    // Format the rows:
    $this->format->left = '<td>';
    $this->format->right = '</td>';
    $this->output .= '<tbody>' . $this->format->cr;
    foreach ($data as $row) {
      $this->output .= $this->collapseRow($row);
    }
    $this->output .= '</tbody>' . $this->format->cr;

    $this->output = '<table>' . $this->format->cr . $this->output . '</table>' . $this->format->cr;
  }
}

/**
 * Class FlatTextExporter
 */
class FlatTextExporter extends CSVExporter implements ExporterInterface {
  protected $extension = '.txt';
  protected $format;

  /**
   * Constructor
   */
  public function __construct(ExportData $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new stdClass;
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

  public function compile() {
    $data = $this->export_data->get();
    $header = $this->getHeader();
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
    $this->output = '';
    $this->output .= $hrule . $this->format->cr;
    foreach ($data as $row) {
      $this->output .= $this->collapseRow($row);
      $this->output .= $hrule . $this->format->cr;
    }
  }
}

/** @} */ //end of grouploft_data_grids loft_data_grids
