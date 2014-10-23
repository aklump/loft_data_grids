<?php
/**
 * @file
 * Tests for the ExportData class data access.
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids;
require_once dirname(__FILE__) . '/../vendor/autoload.php';

class AccessingDataTest extends \PHPUnit_Framework_TestCase {

  public function testGetRows() {
    $obj = new ExportData;
    $this->assertSame(array(), $obj->getRows());
    
    $obj->add('name', 'bob')->next();
    $obj->add('name', 'charlie')->next();
    $obj->add('name', 'dave')->next();

    $control = array (
      0 =>
      array (
        'name' => 'bob',
      ),
      1 =>
      array (
        'name' => 'charlie',
      ),
      2 =>
      array (
        'name' => 'dave',
      ),
    );
    $this->assertSame($control, $obj->getRows());

    $this->assertSame('dave', $obj->setPointer(2)->getValue('name'));
  }

  public function testGetCount() {
    $obj = new ExportData;
    $this->assertSame(0, $obj->getCount());
    
    $obj->add('name', 'bob')->next();
    $obj->add('name', 'charlie')->next();
    $obj->add('name', 'dave')->next();

    $this->assertSame(3, $obj->getCount());
  }

  public function testGetValue() {
    $obj = new ExportData;
    $obj->add('Name', 'Aaron')->add('Age', 39)->next();
    $obj->add('Name', 'Hillary')->add('Age', 37)->next();
    $obj->add('Name', 'Maia')->add('Age', 7)->next();
    $obj->setPage(1);
    $obj->add('Color', 'Black')->add('Make', 'Subaru')->next();
    $obj->add('Color', 'White')->add('Make', 'Hyundai')->next();

    $return = $obj->setPage(0)->setPointer(0)->getValue('Age');
    $this->assertSame(39, $return);

    $return = $obj->setPage(0)->getValue('Name');
    $this->assertSame('Aaron', $return);

    $return = $obj->setPage(1)->setPointer(0)->getValue('Color');
    $this->assertSame('Black', $return);

    $return = $obj->getValue('Make');
    $this->assertSame('Subaru', $return);
  }
}