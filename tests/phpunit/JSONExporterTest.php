<?php
/**
 * @file
 * Tests for the JSONExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;

class JSONExporterTest extends ExporterBase {

    public function testExportPage0()
    {
        $control = '[[{"Order No.":1181,"Customer Billing Country":"US","California Taxed Purchase Amount":0}]]';
        $subject = $this->exporter->export(0);
        $this->assertSame($control, $subject);
    }

    public function testDateTimeObjectHandling()
    {
        $this->assertDateHandlerWorks(function ($date) {
            return json_encode(array('date' => $date));
        });
    }

    public function testPrunePage()
    {
        $obj = clone $this->exporter;
        $obj->addSetting('prune', true);

        $data = new ExportData;
        $data->add('name', 'Bob');

        $this->assertSame('{"name":"Bob"}', $obj->setData($data)->export());
    }

    public function testExportPage1()
    {
        $control = '[[{"Order No.":"1182","Transaction Date":"11\/7\/13","Customer Name":"Hope, Roberta"}]]';
        $subject = $this->exporter->export(1);
        $this->assertSame($control, $subject);
    }

    public function testExportAllPages()
    {
        $control = '[[{"Order No.":1181,"Customer Billing Country":"US","California Taxed Purchase Amount":0}],[{"Order No.":"1182","Transaction Date":"11\/7\/13","Customer Name":"Hope, Roberta"}]]';
        $subject = $this->exporter->export();
        $this->assertSame($control, $subject);
        $this->assertMethodSaveFile();
        $this->assertSandboxFileContents($control);
    }

    public function testInfoValues()
    {
        $info = $this->exporter->getInfo();
        $this->assertSame('.json', $info['extension']);
    }

    public function setUp()
    {
        parent::setUp();
        $this->exporter = new JSONExporter($this->data);
    }
}
