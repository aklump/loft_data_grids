<?php

namespace AKlump\LoftDataGrids;


class YAMLFrontMatterImporterTest extends \PHPUnit_Framework_TestCase {

    public function testAddSettingReturnsThis()
    {
        $obj = new YAMLFrontMatterImporter();
        $this->assertSame($obj, $obj->addSetting('bodyKey', 'footer'));
    }

    public function testImportWithoutFrontMatter()
    {
        $subject = '<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...';

        $obj = new YAMLFrontMatterImporter();
        $data = $obj->import($subject);
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $data);
        $this->assertSame($subject, $data->getValue('body'));
    }

    public function testImportEmptyString()
    {
        $obj = new YAMLFrontMatterImporter();
        $data = $obj->import('');
        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $data);
        $this->assertSame('', $data->getValue('body'));
    }

    public function testExample()
    {
        $subject = '---
title: \'YAML Front Matter\'
description: \'A very simple way to add structured data to a page.\'
---
<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...';

        $obj = new YAMLFrontMatterImporter();
        $data = $obj->import($subject);

        $this->assertInstanceOf('AKlump\LoftDataGrids\ExportData', $data);

        $this->assertSame('YAML Front Matter', $data->getValue('title'));
        $this->assertSame('A very simple way to add structured data to a page.', $data->getValue('description'));
        $this->assertSame('<h1> {{ title }} </h1>
<p> {{ description }} </p>
Page content here...', $data->getValue('body'));
    }
}
