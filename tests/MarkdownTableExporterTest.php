<?php
/**
 * @file
 * Tests for the CSVExporter class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids;
require_once dirname(__FILE__) . '/../vendor/autoload.php';

class MarkdownTableExporterTest extends \PHPUnit_Framework_TestCase {

  function testExport() {
    $data = new ExportData('Notes of the Scale');
    $data->add('do', 'C');
    $data->add('re re re', 'D');
    $data->add('mi miiiiii', 'E')->next();
    $data->add('do', 'D');
    $data->add('re re re', 'E');
    $data->add('mi miiiiii', 'F#')->next();
    $obj = new MarkdownTableExporter($data);

    $control = <<<EOD
## Notes of the Scale
| do | re re re | mi miiiiii |
|----|----------|------------|
| C  | D        | E          |
| D  | E        | F#         |

EOD;
    $this->assertSame($control, $obj->export());

    $control = <<<EOD
| do | re re re | mi miiiiii |
|----|----------|------------|
| C  | D        | E          |
| D  | E        | F#         |

EOD;
    $this->assertSame($control, $obj->hidePageIds()->export());

    $control = <<<EOD
## Notes of the Scale
| do | re re re | mi miiiiii |
|----|----------|------------|
| C  | D        | E          |
| D  | E        | F#         |

EOD;
    $this->assertSame($control, $obj->showPageIds()->export());    
  }
}
