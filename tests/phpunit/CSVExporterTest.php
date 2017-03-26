<?php
/**
 * @file
 * Tests for the CSVExporter class
 *
 * @ingroup loft_data_grids
 * @{
 */

namespace AKlump\LoftDataGrids;


class CSVExporterTest extends \PHPUnit_Framework_TestCase {

    function __construct()
    {
        $this->data = new ExportData();
        $this->records[0] = array(
            'Order No.'                        => '1181',
            'Transaction Date'                 => '11/6/13',
            'Customer Name'                    => 'Hope, Bob',
            'Customer Billing Address'         => '22255 King Street Apt. 1Z',
            'Customer Billing City'            => 'Honolulu',
            'Customer Billing State'           => 'HI',
            'Customer Billing Zip'             => '96813',
            'Customer Billing Country'         => 'US',
            'California Taxed Purchase Amount' => 0,
            'Domestic Purchase Amount'         => 20,
            'International Purchase Amount'    => 0,
            'Order Status'                     => 'Completed',
        );

        $this->records[1] = array(
            'Order No.'                        => '1182',
            'Transaction Date'                 => '11/7/13',
            'Customer Name'                    => 'Hope, Roberta',
            'Customer Billing Address'         => '22255 King Street Apt. 1Z',
            'Customer Billing City'            => 'Honolulu',
            'Customer Billing State'           => 'HI',
            'Customer Billing Zip'             => '96813',
            'Customer Billing Country'         => 'US',
            'California Taxed Purchase Amount' => 6,
            'Domestic Purchase Amount'         => 15,
            'International Purchase Amount'    => 0,
            'Order Status'                     => 'Completed',
        );

        // Page 1
        foreach ($this->records[0] as $key => $value) {
            $this->data->add($key, $value);
        }
        $this->data->next();

        // Page 2
        $this->data->setPage(1);
        foreach ($this->records[1] as $key => $value) {
            $this->data->add($key, $value);
        }
        $this->data->next();


        // Move pointers back to 0 on all pages; return to page 0
        $this->data->setPage(1);
        $this->data->setPointer(0);
        $this->data->setPage(0);
        $this->data->setPointer(0);

        $this->exporter = new CSVExporter($this->data);
    }

    public function testSettings()
    {
        $obj = $this->exporter;
        $obj->setSettings(array('height' => 50));
        $this->assertEquals((object) array('height' => 50), $obj->getSettings());
        $this->assertSame(50, $obj->getSettings()->height);

        $obj->getSettings()->height = 25;
        $this->assertSame(25, $obj->getSettings()->height);
        $this->assertEquals((object) array('height' => 25), $obj->getSettings());

        $obj->addSetting('width', 100);
        $this->assertSame(100, $obj->getSettings()->width);
        $this->assertEquals((object) array(
            'height' => 25,
            'width'  => 100,
        ), $obj->getSettings());
    }

    function testGetSetCurrent()
    {
        $obj = $this->exporter;

        $obj->getData()->setPointer(1);
        $this->assertNull($obj->getData()->getCurrent('Order No.'));

        $return = $obj->getData()->setPointer(0);
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $return);

        $this->assertEquals(1181, $obj->getData()->getCurrent('Order No.'));
        $this->assertEquals(0, $obj->getData()
                                   ->getCurrent('California Taxed Purchase Amount'));

        // Try setting the value of the California Taxed Purchase Amount
        $return = $obj->getData()
                      ->add('California Taxed Purchase Amount', 25.95);
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $return);
        $this->assertEquals(25.95, $obj->getData()
                                       ->getCurrent('California Taxed Purchase Amount'));
    }

    function testColumnNumberFormat()
    {
        $obj = $this->exporter;
        $currency = 'USD';

        $obj->formatColumn('California Taxed Purchase Amount', $currency);
        $obj->formatColumn('Domestic Purchase Amount', $currency);
        $record = $obj->getData()->setPage(0)->setPointer(0);
        $data = $record->getCurrent();
        $this->assertSame('$0.00', $data['California Taxed Purchase Amount']);
        $this->assertSame('$20.00', $data['Domestic Purchase Amount']);
    }

    function testFilename()
    {
        $this->exporter->setFilename('alpha.txt');
        $this->assertSame('alpha.csv', $this->exporter->getFilename());
        $this->exporter->setFilename('bravo.csv');
        $this->assertSame('bravo.csv', $this->exporter->getFilename());
        $obj = new CSVExporter($this->data, 'omega.php');
        $this->assertSame('omega.csv', $obj->getFilename());
    }

    function testGetInfo()
    {
        $info = $this->exporter->getInfo();
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('description', $info);
        $this->assertSame('.csv', $info['extension']);
        $this->assertSame('AKlump\LoftDataGrids\CSVExporter', $info['class']);
    }

    function testTitle()
    {
        $obj = $this->exporter;
        $obj->setTitle('ti');
        $this->assertSame('ti', $obj->getTitle());
    }

    public function testHeadersAnotherWay()
    {
        $data = new ExportData();
        $data->add('do', 1)->next();
        $data->add('re', 2)->next();
        $data->add('mi', 3)->next();
        $exporter = new CSVExporter($data);

        $control = array('do', 're', 'mi');
        $control = array_combine($control, $control);
        $this->assertSame($control, $exporter->getHeader());
    }

    function testHeaders()
    {
        $subject = $this->records[0];
        $return = $this->exporter->getHeader();
        $control = array_combine(array_keys($subject), array_keys($subject));
        $this->assertSame($control, $return);
    }
}

/** @} */ //end of group: loft_data_grids
