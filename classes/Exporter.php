<?php
/**
 * @file
 * Exporter Class
 *
 * @ingroup loft_data_grids
 * @{
 */
use Symfony\Component\Yaml\Yaml;

if (is_file(dirname(__FILE__) . '/../vendor/autoload.php')) {
  require_once dirname(__FILE__) . '/../vendor/autoload.php';
}

/**
 * Interface ExporterInterface
 */
interface ExporterInterface {

  /**
   * Set the export data object
   *
   * @param ExportDataInterface $data
   *
   * @return $this
   */
  public function setData(ExportDataInterface $data);

  /**
   * Set a title for the exported document
   *
   * @return $this
   */
  public function setTitle($title);

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
   * Get the filename
   *
   * @return string
   */
  public function getFilename();

  /**
   * Export data as a string
   *
   * @param mixed $page_id
   *   (Optional) Defaults to NULL.  Set this to export a single page.
   *
   * @return string
   */
  public function export($page_id = NULL);

  /**
   * Save as a file to the server
   *
   * @param string $filename
   *   The correct extension will be appended to this string
   * @param mixed $page_id
   *   (Optional) Defaults to NULL.  Set this to export a single page.
   */
  public function save($filename = '', $page_id = NULL);

  /**
   * Return the ExportDataInterface object
   *
   * @return ExportDataInterface
   */
  public function getData();

  /**
   * Return an array containing the header values for a page
   *
   * @param mixed $page_id
   *   (Optional) Defaults to 0.
   *
   * @return array
   * - The keys of the header MUST match the keys of each row of data
   */
  public function getHeader($page_id = 0);

  /**
   * Return info about this class
   *
   * @param type $string
   *   description
   *
   * @return array
   *   - name string The human name of this exporter
   *   - descripttion string A further description
   *   - extension string The file extension used by this class
   */
  public function getInfo();
}

/**
 * Class Exporter
 */
abstract class Exporter implements ExporterInterface {

  protected $export_data, $title, $filename, $extension, $output;
  protected $header = array();

  /**
   * @var $dependencies
   * You may check this public variable to see if any dependencies for this
   * class are missing. If this is false it means a dependency is missing and
   * the exporter cannot function properly. The way to do this is to instantiate
   * the class and then immediately check this variable, before calling any
   * other methods.
   */
  public $dependencies = TRUE;

  /**
   * Constructor
   *
   * @param ExportDataInterface $data
   * @param string $filename
   *   (Optional) Defaults to ''.
   */
  public function __construct(ExportDataInterface $data, $filename = '') {
    $this->setData($data);
    $this->setFilename($filename);
  }

  public function getInfo() {
    return array(
      'class' => get_class($this),
      'name' => get_class($this),
      'description' => get_class($this),
      'extension' => $this->extension,
    );
  }

  /**
   * Return a string as a safe filename
   *
   * @param string $string
   *   The candidtate filename.
   * @param array $options
   *   - array extensions: allowable extensions no periods
   *   - string ext: default extension if non found; blank for none no period
   *
   * @return string
   * - lowercased, with only letters, numbers, dots and hyphens
   *
   * @see file_munge_filename().
   */
  protected function filenameSafe($string, $options = array()) {
    $options += array(
      'extensions' => array('txt', 'md'),
      'ext' => 'txt',
    );
    $string = preg_replace('/[^a-z0-9\-\.]/', '-', strtolower($string));
    $string = preg_replace('/-{2,}/', '-', $string);

    // Add an extension if not found
    if ($options['ext'] && !preg_match('/\.[a-z]{1,5}$/', $string)) {
      $string .= '.' . trim($options['ext'], '.');
    }

    if ($string && function_exists('file_munge_filename')) {
      $string = file_munge_filename($string, implode(' ', $options['extensions']), FALSE);
    }

    //@todo Add in the module that cleans name if it's installed
    return $string;
  }

  public function setFilename($filename) {
    $extension = trim($this->extension, '.');
    $filename = $this->filenameSafe($filename, array(
      'extensions'  => array($extension),
      'ext'         => $extension,
    ));
    $info = pathinfo($filename);
    if ($info['filename']) {
      $filename = $info['filename'];
    }
    else {
      $filename = time();
    }
    $this->filename = trim($filename, '.') . '.' . trim($this->extension, '.');

    return $this->filename;
  }

  public function setTitle($title) {
    $this->title = $title;

    return $this;
  }

  public function getFilename() {
    return $this->filename;
  }

  public function getData() {
    // Sort the data into the correct order based on the header
    $pages = $this->export_data->get();
    $temp = new ExportData();
    foreach ($pages as $page_id => $data) {
      $temp->setPage($page_id);
      $header = $this->getHeader($page_id);
      foreach ($data as $d) {
        foreach (array_keys($header) as $key) {
          $temp->add($key, $d[$key]);
        }
        $temp->next();
      }
    }
    $this->setData($temp);

    return $this->export_data;
  }

  public function getHeader($page_id = 0) {
    $header = array();
    foreach ($this->export_data->getPage($page_id) as $row) {
      $keys = array_keys($row);
      $header += array_combine($keys, $keys);
    }

    return $header;
  }

  public function setData(ExportDataInterface $data) {
    $this->export_data = $data;
    $data->normalize('');

    return $this;
  }

  /**
   * Build $this->output in prep for export/save
   *
   * This is the main method to be extended for the different exporters
   */
  abstract public function compile($page_id = NULL);

  public function export($page_id = NULL) {
    $this->compile($page_id);

    return $this->output;
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

    $filename = $this->getFilename();

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
 * Class XLSXExporter
 */
class XLSXExporter extends Exporter implements ExporterInterface {

  protected $extension = '.xlsx';
  protected $sheets = array();

  /**
   * @var $worksheet
   * The PHPExcel object
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
  public function __construct(ExportDataInterface $data, $filename = '', $properties = array()) {
    parent::__construct($data, $filename);
    $this->output = FALSE;
    if (!class_exists('PHPExcel')) {
      $this->dependencies = FALSE;
    }
    else {
      $this->excel = new PHPExcel();
    }
    if ($properties) {
      $this->setProperties($properties);
    }
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Excel Format',
      'description' => 'Export data in the .xlsx file format.',
    ) + $info;

    return $info;
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
        $col_index = 'A';
        $worksheet->fromArray($row, NULL, 'A' . $row_index, TRUE);
        $row_index++;
      }
    }
  }

  /**
   * Returns the PHPExcel object
   *
   * @return PHPExcel
   */
  public function export($page_id = NULL) {
    return $this->excel;
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

    $filename = $this->getFilename();

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
  // * Return the header keyed by column letter beginning with 'A'
  // *
  // * @return array
  // * - keys: column letters
  // * - values: header strings
  // */
  //public function getHeader($page_id = 0) {
  //  $header = parent::getHeader($page_id);
  //
  //
  //}
}

/**
 * Class XMLExporter
 */
class XMLExporter extends Exporter implements ExporterInterface {
  protected $extension = '.xml';

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'XML Format',
      'description' => 'Export data in XML file format.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $data = $this->getData()->get();
    $xml = new SimpleXMLElement('<data/>');
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    foreach ($pages as $page_id => $data) {
      $page = $xml->addChild('page');
      $page->addAttribute('id', $page_id);
      foreach ($data as $id => $data_set) {
        $set = $page->addChild('record');
        $set->addAttribute('id', $id);
        foreach ($data_set as $key => $value) {
          // make sure the key is in good format
          $key = preg_replace('/[^a-z0-9_-]/', '_', strtolower($key));
          // Wrap cdata as needed
          if (strstr($value, '<') || strstr($value, '&')) {
            $value = '<![CDATA[' . $value . ']]>';
          }
          $set->addChild($key, $value);
        }
      }
    }
    $this->output = $xml->asXML();
    $this->output = str_replace('&lt;![CDATA[', '<![CDATA[', $this->output);
    $this->output = str_replace(']]&gt;</', ']]></', $this->output);
  }
}

/**
 * Class JSONExporter
 */
class JSONExporter extends Exporter implements ExporterInterface {
  protected $extension = '.json';

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'JSON Format',
      'description' => 'Export data in JSON file format. For more information visit: http://www.json.org.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    $this->output = json_encode($pages);
  }
}

/**
 * Class YAMLExporter
 */
class YAMLExporter extends Exporter implements ExporterInterface {
  protected $extension = '.yaml';

  public function __construct(ExportDataInterface $data, $filename = '') {
    parent::__construct($data, $filename);
    if (!class_exists('Symfony\Component\Yaml\Yaml')) {
      $this->dependencies = FALSE;
    }
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'YAML Format',
      'description' => 'Export data in YAML file format. For more information visit: http://www.yaml.org.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    $this->output = Yaml::dump($pages);
  }
}

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
    $this->format = new stdClass;
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

/**
 * Class TabTextExporter
 */
class TabTextExporter extends CSVExporter implements ExporterInterface {
  protected $extension = '.txt';

  /**
   * Constructor
   */
  public function __construct(ExportDataInterface $data, $filename = '') {
    parent::__construct($data, $filename);
    $this->format = new stdClass;
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
      'description' => 'Export data in the .txt file format.  Fields separated with tabs.  Lines are separated by \r\n',
    ) + $info;

    return $info;
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
  public function __construct(ExportDataInterface $data, $filename = '') {
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

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Monospace Flatfile Text',
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

/** @} */ //end of grouploft_data_grids loft_data_grids
