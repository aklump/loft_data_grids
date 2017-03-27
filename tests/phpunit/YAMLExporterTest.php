<?php
/**
 * @file
 * Tests for the YAMLExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;

class YAMLExporterTest extends ExporterBase {

    public function testDateTimeObjectHandling()
    {
        $this->assertDateHandlerWorks(function ($date) {
            return "-
    - { date: '$date' }
";
        });
    }

    public function testOutput0()
    {
        $control = "-
    'Order No.': 1181
    'Customer Billing Country': US
    'California Taxed Purchase Amount': 0
";
        $subject = $this->exporter->export(0);
        $this->assertSame($control, $subject);
    }

    public function testOutput1()
    {
        $control = "-
    'Order No.': '1182'
    'Transaction Date': 11/7/13
    'Customer Name': 'Hope, Roberta'
";
        $subject = $this->exporter->export(1);
        $this->assertSame($control, $subject);
    }

    public function testOutput()
    {
        $control = "-
    - { 'Order No.': 1181, 'Customer Billing Country': US, 'California Taxed Purchase Amount': 0 }
-
    - { 'Order No.': '1182', 'Transaction Date': 11/7/13, 'Customer Name': 'Hope, Roberta' }
";
        $subject = $this->exporter->export();
        $this->assertSame($control, $subject);

        $this->assertMethodSaveFile();
        $this->assertSandboxFileContents($control);
    }

    public function testInfoValues()
    {
        $info = $this->exporter->getInfo();
        $this->assertSame('.yml', $info['extension']);
    }

    public function setUp()
    {
        parent::setUp();
        $this->exporter = new YAMLExporter($this->data);
    }
}
