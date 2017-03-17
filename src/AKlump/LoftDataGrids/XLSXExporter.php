<?php
namespace AKlump\LoftDataGrids;

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
     * The PHPExcel object
     */
    protected $excel;

    /**
     * Constructor
     *
     * @param ExportDataInterface $data
     * @param string              $filename
     *   (Optional) Defaults to ''.
     * @param array               $properties
     */
    public function __construct(ExportDataInterface $data = null, $filename = '', $properties = array())
    {
        parent::__construct($data, $filename);
        $this->output = false;
        $this->excel = new \PHPExcel();
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
    public function setProperties($properties)
    {
        $obj = $this->excel->getProperties();
        foreach ($properties as $property_name => $value) {
            $method = "set$property_name";
            if (method_exists($obj, $method)) {
                $obj->{$method}($value);
            }
        }

        return $this;
    }

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'Excel Format',
                'shortname'   => 'Excel',
                'description' => 'Export data in the .xlsx file format.',
            ) + $info;

        return $info;
    }

    public function setTitle($title)
    {
        parent::setTitle($title);
        $this->excel->getProperties()->setTitle($title);
    }

    /**
     * Returns the PHPExcel object
     *
     * @return PHPExcel
     */
    public function export($page_id = null)
    {
        return $this->excel;
    }

    public function compile($page_id = null)
    {
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
            $worksheet->fromArray($header, null, 'A' . $row_index++, true);
            foreach ($data as $row) {
                $col_index = 'A';
                $worksheet->fromArray($row, null, 'A' . $row_index, true);
                $row_index++;
            }
        }

        return $this;
    }

    public function save($filename = '', $page_id = null)
    {
        // Make sure we have rendered the data
        if (empty($this->output)) {
            $this->compile($page_id);
        }

        // Assure the correct file extension
        if ($filename) {
            $this->setFilename($filename);
        }

        // Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $this->getFilename() . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    public function saveFile($directory, $filename = '', $page_id = null)
    {
        // Go through the setter to ensure the file_extension.
        $filename = $filename ? $this->setFilename($filename) : $this->getFilename();
        if (!is_writable(($directory))) {
            throw new \RuntimeException("$directory is not writable; cannot save $filename.");
        }
        // Make sure we have rendered the data
        if (empty($this->output)) {
            $this->compile($page_id);
        }
        $path = $directory . '/' . $filename;
        $objWriter = \PHPExcel_IOFactory::createWriter($this->excel, 'Excel2007');
        $objWriter->save($path);

        return $path;
    }

    /**
     * Format a single column with a number format
     *
     * You must do this after calling $this->compile!
     *
     * @param string $column
     * @param string $format_code
     * - USD OR PHPExcel_Style_NumberFormat::setFormatCode()
     *
     * @return $this
     */
    public function formatColumn($column, $format_code)
    {

        // By default we'll use USD.
        $format_code = isset($format_code) ? $format_code : 'USD';
        $phpexcel_format = \PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;

        // Map to specific formats in PHPExcel
        if ($format_code === 'USD') {
            $phpexcel_format = \PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
        }
        elseif ($format_code === \PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_USD_SIMPLE) {
            $format_code = 'USD';
        }

        $columns = $this->getPHPExcelColumns();
        if (!array_key_exists($column, $columns)) {
            return;
        }
        $phpexcel_column = $columns[$column];
        $page = $this->excel->getActiveSheet();
        foreach ($page->getRowIterator() as $row) {
            $row_index = $row->getRowIndex();
            $page->getStyle("$phpexcel_column$row_index")
                 ->getNumberFormat()
                 ->setFormatCode($phpexcel_format);
        }

        return parent::formatColumn($column, $format_code);
    }

    /**
     * Convert a column key into an excel column, e.g. A, AB, etc.
     *
     * @param  string $column
     *
     * @return string
     */
    public function getPHPExcelColumns()
    {

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
     * @param  string $property_name e.g. Creator
     *
     * @return mixed
     */
    public function getProperty($property_name)
    {
        $obj = $this->excel->getProperties();
        $method = "get$property_name";

        return method_exists($obj, $method) ? $obj->$method() : null;
    }
}
