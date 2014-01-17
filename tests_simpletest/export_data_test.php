<?php
/**
 * @file
 * Tests for the ExportData object
 *
 * @ingroup ExportData
 * @{
 */
namespace AKlump\LoftDataGrids;
require_once '../vendor/autoload.php';
require_once(dirname(__FILE__) . '/simpletest/autorun.php');

class ExportDataTests extends \UnitTestCase {

  public function testConstruct() {
    /**
     * Assert constructing with a page sets it correctly
     */
    $_control_group = 'ExportData::__construct';
    $subject = 'default';
    // Desired test result
    $control = array($subject);
    // The test and result
    $object = new ExportData($subject);
    $return = $object->getAllPageIds();
    $result = $return;
    $this->assertIdentical($control, $result, "Assert constructing with a page sets it correctly", $_control_group);

    // Assert setting a new page before any data is written to the first page replaces the first page in the pageIds array()
    $control = array('second');
    $object->setPage('second');
    $result = $object->getAllPageIds();
    $this->assertIdentical($control, $result, "Assert setting a new page appends to the page_ids array", $_control_group);

    // Assert adding data and then changing page creates two page ids in the array
    $control = array('second', 'default');
    $object->add('food', 'pizza');
    $object->next();
    $object->next();
    $object->setPage('default');
    $result = array_values($object->getAllPageIds());
    $this->assertIdentical($control, $result, "Assert adding data and then changing page creates two page ids in the array", $_control_group);

    // Assert starting a new page returns a pointer of 0
    $control = 0;
    $object->next();
    $object->setPage('third');
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert starting a new page returns a pointer of 0", $_control_group);

    // Assert switching back to former page returns the pointer where we left off
    $control = 2;
    $object->setPage('second');
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert switching back to former page returns the pointer where we left off", $_control_group);
    // END ASSERT


    /**
     * Assert constructing with no arguments set page to 0
     */
    $_control_group = 'ExportData::__construct';
    // Desired test result
    $control = array(0);
    // The test and result
    $object = new ExportData();
    $return = $object->getAllPageIds();
    $result = $return;
    $this->assertIdentical($control, $result, "Assert constructing with a page sets it correctly", $_control_group);
    // END ASSERT

    /**
     * Assert the current pointer starts as 0
     */
    $_control_group = 'ExportData::getPointer';
    // Desired test result
    $control = 0;
    // The test and result
    $object = new ExportData;
    $return = $object->getPointer();
    $result = $return;
    $this->assertIdentical($control, $result, "Assert the current pointer starts as 0", $_control_group);
    // END ASSERT

    /**
     * Assert the add method returns the object
     */
    $_control_group = 'ExportData::add';
    // Desired test result
    // The test and result
    $object = new ExportData;
    $return = $object->add('eyes', 'blue');
    $result = is_object($return) && is_a($return, 'AKlump\LoftDataGrids\ExportData');
    $this->assertTrue($result, "Assert the add method returns the object", $_control_group);

    // Assert the pointer did not advance after adding on value
    $control = 0;
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert the pointer did not advance after adding on value", $_control_group);

    // Assert the next method advances the pointer
    $control = 1;
    $object->next();
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert the next method advances the pointer", $_control_group);

    // Assert setPointer works correctly
    $control = 0;
    $object->setPointer(0);
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert setPointer works correctly", $_control_group);

    // Assert adding a second column works
    $control = array('eyes' => 'blue', 'hair' => 'blond');
    $object->add('hair', 'blond');
    $result = $object->getCurrent();
    $this->assertIdentical($control, $result, "Assert adding a second column works", $_control_group);
    // END ASSERT
  }

  public function testInputOutput() {
    $this->control = new \stdClass;
    $this->control->page = 'sheet1';
    $this->control->data = array(
      array(
        'hair' => 'blond',
        'eyes' => 'green',
        'pants' => 'jeans',
      ),
      array(
        'hair' => 'brown',
        'eyes' => 'blue',
      ),
    );


    /**
     * Assert get() returns an empty array after construction
     */
    $_control_group = 'ExportData::get';
    // Desired test result
    $control = array();
    // The test and result
    $object = new ExportData($this->control->page);
    $return = $object->get();
    $result = $return;
    $this->assertIdentical($control, $result, "Assert get() returns an empty array after construction", $_control_group);

    // Assert after added two rows of data, get returns the correct expected array
    $control = array('sheet1' => array(
      0 => array(
        'hair' => 'blond',
        'eyes' => 'green',
        'pants' => 'jeans',
      ),
      1 => array(
        'hair' => 'brown',
        'eyes' => 'blue',
      ),
    ));
    foreach ($this->control->data as $row) {
      foreach ($row as $key => $value) {
        $object->add($key, $value);
      }
      $object->next();
    }
    $result = $object->get();
    $this->assertIdentical($control, $result, "Assert after added two rows of data, get returns the correct expected array", $_control_group);

    // Assert getPointer returns the expected value
    $control = 2;
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert getPointer returns the expected value", $_control_group);


    // Assert getCurrent will return empty array on new line
    $control = array();
    $result = $object->getCurrent();
    $this->assertIdentical($control, $result, "Assert getCurrent will return empty array on new line", $_control_group);

    // Assert getCurrent after setting back to prev rows returns the row as expected
    $control = array(
      'hair' => 'brown',
      'eyes' => 'blue',
    );
    $object->setPointer(1);
    $result = $object->getCurrent();
    $this->assertIdentical($control, $result, "Assert getCurrent after setting back to prev rows returns the row as expected", $_control_group);

    // Assert getCurrent with an argument returns expected value
    $control = 'blue';
    $result = $object->getCurrent('eyes');
    $this->assertIdentical($control, $result, "Assert getCurrent with an argument returns expected value", $_control_group);

    // Assert normalize works correctly for a single page
    $control = array('sheet1' => array(
      0 => array(
        'hair' => 'blond',
        'eyes' => 'green',
        'pants' => 'jeans',
      ),
      1 => array(
        'hair' => 'brown',
        'eyes' => 'blue',
        'pants' => '&mdash;',
      ),
    ));
    // The test and result
    $return = $object->normalize('&mdash;');
    $result = $object->get();
    $this->assertIdentical($control, $result, "Assert normalize works correctly for a single page", $_control_group);

    // Assert find with min arguments returns pointer and key value in array
    $control = array(1 => array(
      'eyes' => 'blue',
    ));
    $result = $object->find('blue');
    $this->assertIdentical($control, $result, "Assert find with min arguments works correctly", $_control_group);

    // Assert find with key argument returns correctly
    $control = array(1 => array(
      'eyes' => 'blue',
    ));
    $result = $object->find('blue', 'eyes');
    $this->assertIdentical($control, $result, "Assert find with key argument", $_control_group);

    // Assert find with key argument that does not contain the value returns empty array
    $control = array();
    $result = $object->find('hair', 'eyes');
    $this->assertIdentical($control, $result, "Assert find with key argument", $_control_group);

    // Assert setPointer with a NULL value moves the pointer to the end
    $control = 2;
    $object->setPointer();
    $result = $object->getPointer();
    $this->assertIdentical($control, $result, "Assert setPointer with a NULL value moves the pointer to the end", $_control_group);

    // Assert find with 1 argument does not return multiple records when possible, only one and it's the earliest
    $control = array(1 => array(
      'eyes' => 'blue',
    ));
    $object->add('eyes', 'blue');
    $object->add('pants', 'blue');
    $result = $object->find('blue');
    $this->assertIdentical($control, $result, "Assert find with 1 argument does not return multiple records when possible, only one and it's the most recent", $_control_group);

    // Assert find asking for 1 record but searching descending returns the most recent record
    $control = array(2 => array(
      'eyes' => 'blue',
      'pants' => 'blue',
    ));
    $result = $object->find('blue', NULL, 1, 1);
    $this->assertIdentical($control, $result, "Assert find asking for 1 record but searching descending returns the most recent record", $_control_group);

    // Assert find asking for 1 record with key but searching descending returns the most recent record
    $control = array(2 => array(
      'eyes' => 'blue',
    ));
    $result = $object->find('blue', 'eyes', 1, 1);
    $this->assertIdentical($control, $result, "Assert find asking for 1 record but searching descending returns the most recent record", $_control_group);

    // Assert find asking for all records with key but searching descending returns what we expect
    $control = array(
      2 => array(
                 'eyes' => 'blue',
                ),
      1 => array(
                 'eyes' => 'blue',
                ),
    );
    $result = $object->find('blue', 'eyes', 0, 1);
    $this->assertIdentical($control, $result, "Assert find asking for all records with key but searching descending returns what we expect", $_control_group);
    // END ASSERT
  }
  public function testKeys() {
    /**
     * Assert setting the keys as an array populates the first row
     */
    $_control_group = 'ExportData::setKeys';
    // Desired test result
    $control = array('do', 're', 'mi', 'fa');
    // The test and result
    $object = new ExportData;
    $return = $object->setKeys($control);
    $object->add('do', 'C');
    $return = $object->getCurrent();
    $result = array_keys($return);
    $this->assertIdentical($control, $result, "Assert setting the keys populates the first row", $_control_group);
    // END ASSERT

    /**
     * Assert setting the keys as arguments populates the first row
     */
    $_control_group = 'ExportData::setKeys';
    // Desired test result
    $control = array('do', 're', 'mi', 'fa');
    // The test and result
    $object = new ExportData;
    $return = $object->setKeys('do', 're', 'mi', 'fa');
    $object->add('mi', 'E');
    $return = $object->getCurrent();
    $result = array_keys($return);
    $this->assertIdentical($control, $result, "Assert setting the keys populates the first row", $_control_group);

    // Assert setting the keys after data exists modifies the order of data returned by get
    $control = array('fa', 'mi', 're', 'do');
    $object->setKeys($control);
    $return = $object->getCurrent();
    $result = array_keys($return);
    $this->assertIdentical($control, $result, "Assert setting the keys after data exists modifies the order of data returned by get", $_control_group);

    // Assert getCurrentPageId
    $control = 0;
    $result = $object->getCurrentPageId();
    $this->assertIdentical($control, $result, "Assert getCurrentPageId", $_control_group);

    // Assert get current page id returns after switching pages
    $control = 'my_next_page';
    $object->setPage($control);
    $result = $object->getCurrentPageId();
    $this->assertIdentical($control, $result, "Assert get current page id returns after switching pages", $_control_group);





    // END ASSERT

  }
}

/** @} */ //end of group: ExportData
