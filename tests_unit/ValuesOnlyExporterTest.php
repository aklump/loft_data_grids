<?php
/**
 * @file
 * Tests for the ValuesOnlyExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids\Tests\Unit;

use AKlump\LoftDataGrids\ValuesOnlyExporter;

/**
 * @covers \AKlump\LoftDataGrids\ValuesOnlyExporter
 * @uses \AKlump\LoftDataGrids\ExportData
 * @uses \AKlump\LoftDataGrids\Exporter
 */
class ValuesOnlyExporterTest extends ExporterBase {

    public function testOutput0()
    {
        $control = "1181\\US\\0";
        $subject = $this->exporter->export(0);
        $this->assertSame($control, $subject);
    }

    public function testOutput1()
    {
        $control = "1182\\11/7/13\\Hope, Roberta";
        $subject = $this->exporter->export(1);
        $this->assertSame($control, $subject);
    }

    public function testOutput()
    {
        $control = "1181\\US\\0\r\n1182\\11/7/13\\Hope, Roberta";
        $subject = $this->exporter->export();
        $this->assertSame($control, $subject);
        $this->assertMethodSaveFile();
        $this->assertSandboxFileContents($control);
    }

    public function testInfoValues()
    {
        $info = $this->exporter->getInfo();
        $this->assertSame('.txt', $info['extension']);
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->exporter = new ValuesOnlyExporter($this->data);
    }
}
