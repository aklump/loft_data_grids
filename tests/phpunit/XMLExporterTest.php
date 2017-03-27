<?php
/**
 * @file
 * Tests for the XMLExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;

class XMLExporterTest extends ExporterBase {

    public function testDateTimeObjectHandling()
    {
        $this->assertDateHandlerWorks(function ($date) {
            return '<?xml version="1.0"?>
<data><page id="0"><record id="0"><date>' . $date . '</date></record></page></data>
';
        });
    }

    public function testCData()
    {
        $data = new ExportData();
        $data->add('title', '<h1>Some</h1>');
        $this->exporter->setData($data);
        $control = '<?xml version="1.0"?>
<data><page id="0"><record id="0"><title><![CDATA[&lt;h1&gt;Some&lt;/h1&gt;]]></title></record></page></data>
';
        $this->assertSame($control, $this->exporter->export());
    }

    public function testOutput0()
    {
        $control = '<?xml version="1.0"?>
<data><page id="0"><record id="0"><order_no_>1181</order_no_><customer_billing_country>US</customer_billing_country><california_taxed_purchase_amount>0</california_taxed_purchase_amount></record></page></data>
';
        $subject = $this->exporter->export(0);
        $this->assertSame($control, $subject);
    }

    public function testOutput1()
    {
        $control = '<?xml version="1.0"?>
<data><page id="1"><record id="0"><order_no_>1182</order_no_><transaction_date>11/7/13</transaction_date><customer_name>Hope, Roberta</customer_name></record></page></data>
';
        $subject = $this->exporter->export(1);
        $this->assertSame($control, $subject);
    }

    public function testOutput()
    {
        $control = '<?xml version="1.0"?>
<data><page id="0"><record id="0"><order_no_>1181</order_no_><customer_billing_country>US</customer_billing_country><california_taxed_purchase_amount>0</california_taxed_purchase_amount></record></page><page id="1"><record id="0"><order_no_>1182</order_no_><transaction_date>11/7/13</transaction_date><customer_name>Hope, Roberta</customer_name></record></page></data>
';
        $subject = $this->exporter->export();
        $this->assertSame($control, $subject);

        $this->assertMethodSaveFile();
        $this->assertSandboxFileContents($control);
    }

    public function testInfoValues()
    {
        $info = $this->exporter->getInfo();
        $this->assertSame('.xml', $info['extension']);
    }

    public function setUp()
    {
        parent::setUp();
        $this->exporter = new XMLExporter($this->data);
    }
}
