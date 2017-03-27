<?php
/**
 * @file
 * Tests for the YamlExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;


/**
 * Class ExporterBase
 *
 * A base test for a given exporter.
 *
 * @package AKlump\LoftDataGrids
 */
class ExporterBase extends \PHPUnit_Framework_TestCase {

    public function testCompileReturnsSelf()
    {
        $this->assertSame($this->exporter, $this->exporter->compile());
    }

    public function testInfo()
    {
        $info = $this->exporter->getInfo();
        $this->assertNotEmpty($info['name']);
        $this->assertNotEmpty($info['shortname']);
        $this->assertNotEmpty($info['description']);
        $this->assertNotEmpty($info['class']);
        $this->assertNotEmpty($info['extension']);
    }

    /**
     * @expectedException RuntimeException
     */
    public function testSaveFileToUnwriteableDirThrows()
    {
        chmod($this->sandbox, 0444);
        try {
            $this->exporter->saveFile($this->sandbox);
        } catch (\Exception $exception) {
            chmod($this->sandbox, 0777);
            throw $exception;
        }
    }

    public function assertDateHandlerWorks($control)
    {
        $data = new ExportData;
        $date = new \DateTime('2010-04-04');
        $control = $control($date->format(\DATE_ISO8601));
        $data->add('date', $date);
        $output = $this->exporter->setData($data)
                                 ->addSetting('dateFormat', \DATE_ISO8601)
                                 ->addSetting('prune', true)->export();
        $this->assertSame($control, $output);
    }

    /**
     * Make sure that saveFile creates a file and returns the path.
     */
    public function assertMethodSaveFile()
    {
        $obj = clone $this->exporter;
        $this->sandboxFilePath = $this->sandbox . '/' . $obj->setFilename('export');
        $this->assertFileNotExists($this->sandboxFilePath);
        $returnPath = $this->exporter->saveFile($this->sandbox, 'export');
        $this->assertSame($this->sandboxFilePath, $returnPath);
    }

    /**
     * @param string $control The expected file contents.
     */
    public function assertSandboxFileContents($control)
    {
        $this->assertFileExists($this->sandboxFilePath);
        $this->assertSame($control, file_get_contents($this->sandboxFilePath));
        unlink($this->sandboxFilePath);
        $this->assertFileNotExists($this->sandboxFilePath);
    }

    /**
     * Compare the sandbox file against it's control file.
     *
     * For .csv we expect a control file sandbox/control.csv to preexist.
     */
    public function assertSandboxFileEquals()
    {
        $obj = clone $this->exporter;
        $reflect = new \ReflectionClass($obj);
        $basepath = $reflect->getShortName();
        $this->controlFilePath = $this->sandbox . '/' . $obj->setFilename($basepath);
        $this->assertFileExists($this->sandboxFilePath);
        $this->assertFileExists($this->controlFilePath);
        $this->assertFileEquals($this->controlFilePath, $this->sandboxFilePath);
        unlink($this->sandboxFilePath);
        $this->assertFileNotExists($this->sandboxFilePath);
    }

    public function setUp()
    {
        $this->data = new ExportData();
        $this->records[0] = array(
            'Order No.'                        => 1181,
            'Customer Billing Country'         => 'US',
            'California Taxed Purchase Amount' => 0,
        );

        $this->records[1] = array(
            'Order No.'        => '1182',
            'Transaction Date' => '11/7/13',
            'Customer Name'    => 'Hope, Roberta',
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

        // Create the sandbox directory for saving
        $this->sandbox = dirname(__FILE__) . '/../sandbox';
        $this->assertTrue(is_writable($this->sandbox));
    }

    public function tearDown()
    {
        if (isset($this->sandboxFilePath) && file_exists($this->sandboxFilePath)) {
            unlink($this->sandboxFilePath);
        }
    }
}
