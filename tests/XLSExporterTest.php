<?php
/**
 * @file
 * Tests for the XLSExporter class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids;
require_once '../vendor/autoload.php';

class XLSExporterTest extends \PHPUnit_Framework_TestCase {

  function testColumnNumberFormat() {
    $obj = clone $this->exporter;
    $currency = 'USD';
    $obj->formatColumn('California Taxed Purchase Amount', $currency);
    $obj->formatColumn('Domestic Purchase Amount',         $currency);
    $record = $obj->getData()->setPointer(0);
    $data = $record->getCurrent();
    $this->assertSame('$0.00', $data['California Taxed Purchase Amount']);
    $this->assertSame('$20.00', $data['Domestic Purchase Amount']);

    $obj = clone $this->exporter;
    $currency = '"$"#,##0.00_-';
    $obj->formatColumn('California Taxed Purchase Amount', $currency);
    $obj->formatColumn('Domestic Purchase Amount',         $currency);
    $record = $obj->getData()->setPointer(0);
    $data = $record->getCurrent();
    $this->assertSame('$0.00', $data['California Taxed Purchase Amount']);
    $this->assertSame('$20.00', $data['Domestic Purchase Amount']);
  }

  function testGetPHPExcelColumns() {
    $obj = $this->exporter;
    $subject = array_fill_keys(array_keys($this->records[0]), NULL);
    $current = array(65);
    $chr = &$current[count($current) - 1];
    foreach (array_keys($subject) as $header_key) {
      $subject[$header_key] = chr($chr++);
    }

    $this->assertSame($subject, $obj->getPHPExcelColumns());    

    // @todo this will not test past L; make it really work!
  }  

  function testFilename() {
    $this->exporter->setFilename('alpha.txt');
    $this->assertSame('alpha.xlsx', $this->exporter->getFilename());
    $this->exporter->setFilename('bravo.xlsx');
    $this->assertSame('bravo.xlsx', $this->exporter->getFilename());
    $obj = new XLSXExporter($this->data, 'omega.php');
    $this->assertSame('omega.xlsx', $obj->getFilename());
  }
  
  function testExport() {
    $result = $this->exporter->export();
    $this->assertInstanceOf('PHPExcel', $result);
  }

  function testGetInfo() {
    $info = $this->exporter->getInfo();
    $this->assertArrayHasKey('name', $info);
    $this->assertArrayHasKey('description', $info);
    $this->assertSame('.xlsx', $info['extension']);
    $this->assertSame('AKlump\LoftDataGrids\XLSXExporter', $info['class']);
  }

  function testPropertiesAndTitle() {
    $obj = $this->exporter;

    $subject = array(
      'Creator' => 'do',
      'LastModifiedBy' => 're',
      'Title' => 'mi',
      'Description' => 'fa',
      'Keywords' => 'so',
      'Category' => 'la',
    );
    $return = $obj->setProperties($subject);
    $this->assertInstanceOf('AKlump\LoftDataGrids\XLSXExporter', $return);

    $this->assertNull($obj->getProperty('BreakfastMenu'));
    $this->assertSame('do', $obj->getProperty('Creator'));
    $this->assertSame('re', $obj->getProperty('LastModifiedBy'));
    $this->assertSame('mi', $obj->getProperty('Title'));
    $this->assertSame('fa', $obj->getProperty('Description'));
    $this->assertSame('so', $obj->getProperty('Keywords'));
    $this->assertSame('la', $obj->getProperty('Category'));

    $obj->setTitle('ti');
    $this->assertSame('ti', $obj->getProperty('Title'));
  }

  function testHeaders() {
    $subject = $this->records[0];
    $return = $this->exporter->getHeader();
    $this->assertSame(array_combine(array_keys($subject), array_keys($subject)), $return);
  }

  function __construct() {
    $this->data = new ExportData();
    $this->records[0] = array (
      'Order No.' => '1181',
      'Transaction Date' => '11/6/13',
      'Customer Name' => 'Hope, Bob',
      'Customer Billing Address' => '22255 King Street Apt. 1Z',
      'Customer Billing City' => 'Honolulu',
      'Customer Billing State' => 'HI',
      'Customer Billing Zip' => '96813',
      'Customer Billing Country' => 'US',
      'California Taxed Purchase Amount' => 0,
      'Domestic Purchase Amount' => 20,
      'International Purchase Amount' => 0,
      'Order Status' => 'Completed',
    );
    foreach ($this->records[0] as $key => $value) {
      $this->data->add($key, $value);
    }
    $this->data->next();

    $this->exporter = new XLSXExporter($this->data);
  }  
}

/** @} */ //end of group: loft_data_grids