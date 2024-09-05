<?php
/**
 * @file
 * Tests for the DollarFormatter class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids\Tests\Unit;

use PHPUnit\Framework\TestCase;
use AKlump\LoftDataGrids\DollarFormatter;

/**
 * @covers \AKlump\LoftDataGrids\DollarFormatter
 * @uses \AKlump\LoftDataGrids\Formatter
 */
class DollarFormatterTest extends TestCase {

  public function testFormatting() {
    $obj = new DollarFormatter(98.6);
    $this->assertSame('$98.60', $obj->get());

    $obj = new DollarFormatter();
    $this->assertSame('$0.00', $obj->get());

    $obj = new DollarFormatter('$125.00');
    $this->assertSame('$125.00', $obj->get());

    $obj = new DollarFormatter('$65');
    $this->assertSame('$65.00', $obj->get());

    $obj = new DollarFormatter('123456');
    $this->assertSame('$123,456.00', $obj->get());
  }
}
