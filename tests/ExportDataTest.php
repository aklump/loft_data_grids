<?php
/**
 * @file
 * Tests for the ExportData class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids;
require_once '../vendor/autoload.php';

class ExportDataTest extends \PHPUnit_Framework_TestCase {
  public function getAllPageIds() {
    $obj = new ExportData();
    $this->assertSame(array(), $obj->getAllPageIds());
  }

  public function testLocations() {
    $obj = new ExportData();
    
    $obj->add('Name', 'Aaron')->add('Age', 39)->next();
    $obj->add('Name', 'Hillary')->add('Age', 37)->next();
    $obj->add('Name', 'Maia')->add('Age', 7)->next();
    
    $obj->setPointer(1);
    $this->assertSame('Hillary', $obj->getCurrent('Name'));
    $return = $obj->storeLocation('start');
    $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $return);

    $obj->setPage(1);
    $obj->add('Color', 'Black')->add('Make', 'Subaru')->next();
    $obj->add('Color', 'White')->add('Make', 'Hyundai')->next();

    // This is null because page 2 doesn't have a Name column
    $this->assertNull($obj->getCurrent('Name'));

    // This is null because we're on row 2
    $this->assertNull($obj->getCurrent('Color'));

    $return = $obj->setPointer(1)->getCurrent('Color');
    $this->assertSame('White', $return);
    $obj->storeLocation('cars');

    $return = $obj->gotoLocation('start');
    $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $return);
    $this->assertSame('Hillary', $obj->getCurrent('Name'));
    $this->assertEquals(0, $obj->getCurrentPageId());
    $this->assertEquals(1, $obj->getPointer());

    $subject = array(
      'start' => array(
        'page' => 0,
        'pointers' => array(0 => 1, 1 => 0),
      ),
      'cars' => array(
        'page' => 1,
        'pointers' => array(0 => 1, 1 => 1),
      ),
    );
    $this->assertSame($subject, ($locs = $obj->getLocations()));

     $copy = new ExportData();
     $copy->setLocations($locs);
     $this->assertSame($subject, $copy->getLocations());
  }
}

/** @} */ //end of group: loft_data_grids