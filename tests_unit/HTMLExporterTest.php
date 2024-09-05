<?php
/**
 * @file
 * Tests for the HTMLExporter class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids\Tests\Unit;

use PHPUnit\Framework\TestCase;
use AKlump\LoftDataGrids\ExportData;
use AKlump\LoftDataGrids\HTMLExporter;

/**
 * @covers \AKlump\LoftDataGrids\HTMLExporter
 * @uses \AKlump\LoftDataGrids\ExportData
 * @uses \AKlump\LoftDataGrids\Exporter
 * @uses \AKlump\LoftDataGrids\CSVExporter
 */
class HTMLExporterTest extends TestCase {

    public function testCSSColumnClasses()
    {
        $obj = new ExportData;
        $obj
            ->add('Vivid Color', 'orange')
            ->add('Definitive_SHAPE', 'rectangle');
        $export = new HTMLExporter($obj);
        $control = '<table>
<thead>
<tr><td class="colgroup-vivid-color">Vivid Color</th><td class="colgroup-definitive-shape">Definitive_SHAPE</th></tr>
</thead>
<tbody>
<tr><td class="colgroup-vivid-color">orange</td><td class="colgroup-definitive-shape">rectangle</td></tr>
</tbody>
</table>
';
        $this->assertSame($control, $export->export());
    }
}
