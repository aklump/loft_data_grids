<?php
/**
 * @file
 * Tests for the YAMLFrontMatterExporter class
 *
 * @ingroup loft_data_grids
 */

namespace AKlump\LoftDataGrids;

class YAMLFrontMatterExporterTest extends ExporterBase {

    public function testExportNoFrontMatter()
    {
        $html = new ExportData();
        $html->add('body', '<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...');

        $exporter = new YAMLFrontMatterExporter($html);
        $control = '<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...';
        $this->assertSame($control, $exporter->export());
    }

    public function testExportNoBody()
    {
        $html = new ExportData();
        $html->add('title', 'YAML Front Matter')
             ->add('description', 'A very simple way to add structured data to a page.');

        $exporter = new YAMLFrontMatterExporter($html);
        $control = '---
title: \'YAML Front Matter\'
description: \'A very simple way to add structured data to a page.\'
---
';
        $this->assertSame($control, $exporter->export());
    }

    public function testExport()
    {
        $html = new ExportData();
        $html->add('title', 'YAML Front Matter')
             ->add('description', 'A very simple way to add structured data to a page.')
             ->add('body', '<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...');

        $exporter = new YAMLFrontMatterExporter($html);
        $control = '---
title: \'YAML Front Matter\'
description: \'A very simple way to add structured data to a page.\'
---
<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...';
        $this->assertSame($control, $exporter->export());
    }

    public function testGetInfo()
    {
        $info = $this->exporter->getInfo();
        $this->assertSame('.html', $info['extension']);
        $this->assertSame('AKlump\LoftDataGrids\YAMLFrontMatterExporter', $info['class']);
    }

    public function setUp()
    {
        parent::setUp();
        $this->exporter = new YAMLFrontMatterExporter($this->data);
    }
}
