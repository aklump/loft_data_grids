<?php
/**
 * @file
 * Tests for the YamlExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;


/**
 * Class ExporterBase
 *
 * A base test for a given exporter.
 *
 * @package AKlump\LoftDataGrids
 */
class ExporterBase extends \PHPUnit_Framework_TestCase {

  public function testCompileReturnsSelf() {
    $this->assertSame($this->exporter, $this->exporter->compile());
  }

  public function testInfo() {
    $info = $this->exporter->getInfo();
    $this->assertNotEmpty($info['name']);
    $this->assertNotEmpty($info['shortname']);
    $this->assertNotEmpty($info['description']);
    $this->assertNotEmpty($info['class']);
    $this->assertNotEmpty($info['extension']);
  }

  /**
   * @expectedException RuntimeException
   */
  public function testSaveFileToUnwriteableDirThrows() {
    chmod($this->sandbox, 0444);
    try {
      $this->exporter->saveFile($this->sandbox);
    } catch (\Exception $exception) {
      chmod($this->sandbox, 0777);
      throw $exception;
    }
  }

  /**
   * Make sure that saveFile works on this exporter
   *
   * @param string $control The expected file contents.
   */
  public function assertMethodSaveFile($control) {
    $obj = clone $this->exporter;
    $path = $this->sandbox . '/' . $obj->setFilename('export');
    $this->assertFileNotExists($path);
    $returnPath = $this->exporter->saveFile($this->sandbox, 'export');
    $this->assertSame($path, $returnPath);
    $this->assertFileExists($path);
    $this->assertSame($control, file_get_contents($path));
    unlink($path);
    $this->assertFileNotExists($path);
  }

  public function setUp() {
    $this->data = new ExportData();
    $this->records[0] = array(
      'Order No.'                        => 1181,
      'Customer Billing Country'         => 'US',
      'California Taxed Purchase Amount' => 0,
    );

    $this->records[1] = array(
      'Order No.'        => '1182',
      'Transaction Date' => '11/7/13',
      'Customer Name'    => 'Hope, Roberta',
    );

    // Page 1
    foreach ($this->records[0] as $key => $value) {
      $this->data->add($key, $value);
    }
    $this->data->next();

    // Page 2
    $this->data->setPage(1);
    foreach ($this->records[1] as $key => $value) {
      $this->data->add($key, $value);
    }
    $this->data->next();

    // Move pointers back to 0 on all pages; return to page 0
    $this->data->setPage(1);
    $this->data->setPointer(0);
    $this->data->setPage(0);
    $this->data->setPointer(0);

    // Create the sandbox directory for saving
    $this->sandbox = dirname(__FILE__) . '/../sandbox';
    $this->assertTrue(is_writable($this->sandbox));
  }
}
