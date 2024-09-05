<?php
/**
 * @file
 * Tests for the CSVExporter class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids\Tests\Unit;

use PHPUnit\Framework\TestCase;
use AKlump\LoftDataGrids\ExportData;
use AKlump\LoftDataGrids\FlatTextExporter;

/**
 * @covers \AKlump\LoftDataGrids\FlatTextExporter
 * @uses \AKlump\LoftDataGrids\ExportData
 * @uses \AKlump\LoftDataGrids\Exporter
 * @uses \AKlump\LoftDataGrids\CSVExporter
 */
class FlatTextExporterTest extends TestCase {

    function testExport()
    {
        $data = new ExportData();
        $data->setPage('Notes of the Scale');
        $data->add('do', 'C');
        $data->add('re re re', 'D');
        $data->add('mi miiiiii', 'E')->next();
        $data->add('do', 'D');
        $data->add('re re re', 'E');
        $data->add('mi miiiiii', 'F#')->next();
        $obj = new FlatTextExporter($data);

        $control = <<<EOD
------------------------------
| DO | RE RE RE | MI MIIIIII |
------------------------------
| C  | D        | E          |
------------------------------
| D  | E        | F#         |
------------------------------

EOD;
        $this->assertSame($control, $obj->export());

        $control = <<<EOD
-- NOTES OF THE SCALE --
------------------------------
| DO | RE RE RE | MI MIIIIII |
------------------------------
| C  | D        | E          |
------------------------------
| D  | E        | F#         |
------------------------------

EOD;
        $this->assertSame($control, $obj->showPageIds()->export());

        $control = <<<EOD
------------------------------
| DO | RE RE RE | MI MIIIIII |
------------------------------
| C  | D        | E          |
------------------------------
| D  | E        | F#         |
------------------------------

EOD;
        $this->assertSame($control, $obj->hidePageIds()->export());

    }
}
