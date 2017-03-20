<?php
/**
 * @file
 * Tests for the ExportData class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids;


class ExportDataTest extends \PHPUnit_Framework_TestCase {


    public function testToString()
    {
        $control = 'd4718a0d002ea73c60d3dc96d17665fc5cd7cbce';
        $this->assertSame($control, (string) $this->obj);
    }
    public function testShowKeys()
    {
        $obj = $this->obj;
        $obj->setPage(0);
        $control = array(
            0 =>
                array(
                    0 => array(),
                    1 => array(),
                    2 => array(),
                ),
            1 =>
                array(
                    0 =>
                        array(
                            'Color' => 'Black',
                            'Make'  => 'Subaru',
                        ),
                    1 =>
                        array(
                            'Color' => 'White',
                            'Make'  => 'Hyundai',
                        ),
                ),
        );
        $obj->hideKeys(true);
        $this->assertSame($control, $obj->get());

        $return = $obj->showKeys('Age')->getPage();
        $control = array(
            0 =>
                array(
                    'Age' => 39,
                ),
            1 =>
                array(
                    'Age' => 37,
                ),
            2 =>
                array(
                    'Age' => 7,
                ),
        );
        $this->assertSame($control, $return);
    }

    public function testHideKeys()
    {
        $obj = $this->obj;
        $obj->setPage(0);

        $control = array(
            0 =>
                array(
                    0 =>
                        array(
                            'Name' => 'Aaron',
                            'Age'  => 39,
                        ),
                    1 =>
                        array(
                            'Name' => 'Hillary',
                            'Age'  => 37,
                        ),
                    2 =>
                        array(
                            'Name' => 'Maia',
                            'Age'  => 7,
                        ),
                ),
            1 =>
                array(
                    0 =>
                        array(
                            'Color' => 'Black',
                            'Make'  => 'Subaru',
                        ),
                    1 =>
                        array(
                            'Color' => 'White',
                            'Make'  => 'Hyundai',
                        ),
                ),
        );
        $this->assertSame($control, $obj->get());

        $return = $obj->hideKeys('Name');
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $return);
        $control = array(
            0 =>
                array(
                    0 =>
                        array(
                            'Age' => 39,
                        ),
                    1 =>
                        array(
                            'Age' => 37,
                        ),
                    2 =>
                        array(
                            'Age' => 7,
                        ),
                ),
            1 =>
                array(
                    0 =>
                        array(
                            'Color' => 'Black',
                            'Make'  => 'Subaru',
                        ),
                    1 =>
                        array(
                            'Color' => 'White',
                            'Make'  => 'Hyundai',
                        ),
                ),
        );
        $this->assertSame($control, $obj->get());

        $control = array(
            0 =>
                array(
                    0 => array(),
                    1 => array(),
                    2 => array(),
                ),
            1 =>
                array(
                    0 =>
                        array(
                            'Color' => 'Black',
                            'Make'  => 'Subaru',
                        ),
                    1 =>
                        array(
                            'Color' => 'White',
                            'Make'  => 'Hyundai',
                        ),
                ),
        );
        $obj->hideKeys(true);
        $this->assertSame($control, $obj->get());

        $control = array(
            0 =>
                array(
                    0 =>
                        array(
                            'Name' => 'Aaron',
                            'Age'  => 39,
                        ),
                    1 =>
                        array(
                            'Name' => 'Hillary',
                            'Age'  => 37,
                        ),
                    2 =>
                        array(
                            'Name' => 'Maia',
                            'Age'  => 7,
                        ),
                ),
            1 =>
                array(
                    0 =>
                        array(
                            'Color' => 'Black',
                            'Make'  => 'Subaru',
                        ),
                    1 =>
                        array(
                            'Color' => 'White',
                            'Make'  => 'Hyundai',
                        ),
                ),
        );
        $obj->hideKeys(false);
        $this->assertSame($control, $obj->get());
    }

    public function testDeletePage()
    {
        $obj = $this->obj;

        $return = $obj->deletePage(0);
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportDataInterface', $return);
        $return = $return->getAllPageIds();
        $this->assertCount(1, $return);

        $return = $obj->setPage(1)->setPointer(0)->getCurrent();
        $this->assertSame('Black', $return['Color']);
        $this->assertSame('Subaru', $return['Make']);
    }

    public function testGetPageData()
    {
        $obj = $this->obj;
        $return = $obj->getPageData(1);
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportDataInterface', $return);
        $return = $obj->setPointer(0)->getCurrent();
        $this->assertSame('Black', $return['Color']);
        $this->assertSame('Subaru', $return['Make']);
    }

    public function getAllPageIds()
    {
        $obj = new ExportData();
        $this->assertSame(array(), $obj->getAllPageIds());
    }

    public function testLocations()
    {
        $obj = $this->obj;

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
                'page'     => 0,
                'pointers' => array(0 => 1, 1 => 0),
            ),
            'cars'  => array(
                'page'     => 1,
                'pointers' => array(0 => 1, 1 => 1),
            ),
        );
        $this->assertSame($subject, ($locs = $obj->getLocations()));

        $copy = new ExportData();
        $copy->setLocations($locs);
        $this->assertSame($subject, $copy->getLocations());
    }


    public function setUp()
    {
        $this->obj = new ExportData();

        $this->obj->add('Name', 'Aaron')->add('Age', 39)->next();
        $this->obj->add('Name', 'Hillary')->add('Age', 37)->next();
        $this->obj->add('Name', 'Maia')->add('Age', 7)->next();

        $this->obj->setPointer(1);
        $this->assertSame('Hillary', $this->obj->getCurrent('Name'));
        $return = $this->obj->storeLocation('start');
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $return);

        $this->obj->setPage(1);
        $this->obj->add('Color', 'Black')->add('Make', 'Subaru')->next();
        $this->obj->add('Color', 'White')->add('Make', 'Hyundai')->next();
    }
}

/** @} */ //end of group: loft_data_grids
