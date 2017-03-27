<?php
/**
 * @file
 * Tests for the ArrayExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;

class ArrayExporterTest extends ExporterBase {

    public function testDateTimeObjectHandling()
    {
        $this->assertDateHandlerWorks(function ($date) {
            return array('date' => $date);
        });
    }

    public function testSaveFile()
    {
        $data = new ExportData;
        $data->add('do', 're');
        $this->exporter->setData($data);
        $this->assertMethodSaveFile();
        $control = '<?php
array (
  0 => 
  array (
    0 => 
    array (
      \'do\' => \'re\',
    ),
  ),
);
';
        $this->assertSandboxFileContents($control);
    }

    public function testPrunePage()
    {
        $obj = clone $this->exporter;
        $obj->addSetting('prune', true);

        $data = new ExportData;
        $data->add('name', 'Bob');

        $this->assertSame(array('name' => 'Bob'), $obj->setData($data)
                                                      ->export());
    }

    public function testExportPage0()
    {
        $control = json_decode('[[{"Order No.":1181,"Customer Billing Country":"US","California Taxed Purchase Amount":0}]]', true);
        $subject = $this->exporter->export(0);
        $this->assertSame($control, $subject);
    }

    public function testExportPage1()
    {
        $control = json_decode('[[{"Order No.":"1182","Transaction Date":"11\/7\/13","Customer Name":"Hope, Roberta"}]]', true);
        $subject = $this->exporter->export(1);
        $this->assertSame($control, $subject);
    }

    public function testExportAllPages()
    {
        $control = json_decode('[[{"Order No.":1181,"Customer Billing Country":"US","California Taxed Purchase Amount":0}],[{"Order No.":"1182","Transaction Date":"11\/7\/13","Customer Name":"Hope, Roberta"}]]', true);
        $subject = $this->exporter->export();
        $this->assertSame($control, $subject);
    }

    public function testInfoValues()
    {
        $info = $this->exporter->getInfo();
        $this->assertSame('.php', $info['extension']);
    }

    public function setUp()
    {
        parent::setUp();
        $this->exporter = new ArrayExporter($this->data);
    }
}
