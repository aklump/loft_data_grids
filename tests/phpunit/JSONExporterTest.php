<?php
/**
 * @file
 * Tests for the JSONExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;

class JSONExporterTest extends ExporterBase {

  public function testExportPage0() {
    $control = '[[{"Order No.":1181,"Customer Billing Country":"US","California Taxed Purchase Amount":0}]]';
    $subject = $this->exporter->export(0);
    $this->assertSame($control, $subject);
  }

  public function testExportPage1() {
    $control = '[[{"Order No.":"1182","Transaction Date":"11\/7\/13","Customer Name":"Hope, Roberta"}]]';
    $subject = $this->exporter->export(1);
    $this->assertSame($control, $subject);
  }

  public function testExportAllPages() {
    $control = '[[{"Order No.":1181,"Customer Billing Country":"US","California Taxed Purchase Amount":0}],[{"Order No.":"1182","Transaction Date":"11\/7\/13","Customer Name":"Hope, Roberta"}]]';
    $subject = $this->exporter->export();
    $this->assertSame($control, $subject);
  }

  public function testInfoValues() {
    $info = $this->exporter->getInfo();
    $this->assertSame('.json', $info['extension']);
  }

  public function setUp() {
    parent::setUp();
    $this->exporter = new JSONExporter($this->data);
  }
}
