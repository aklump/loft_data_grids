<?php
/**
 * @file
 * PHPUnit tests for the TipueSearch class
 */

namespace AKlump\LoftDocs;
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class TipueSearchTest extends \PHPUnit_Framework_TestCase {
  
  public function testAddTwoSortsCorrectly() {
    $obj = new TipueSearch;
    $obj
    ->addPage(new SearchPageData('/bravo.html/', 'bravo', 'contents', array('fa', 'so', 'la')))
    ->addPage(new SearchPageData('alpha.html', 'alpha', 'contents', array('do', 're', 'mi')));
    $control = <<<EOD
var tipuesearch = {"pages":[{"title":"alpha","text":"contents","tags":"do re mi","url":"alpha.html"},{"title":"bravo","text":"contents","tags":"fa so la","url":"bravo.html"}]};

EOD;
    $this->assertSame($control, $obj->buildFileContents());
  }

  public function testAddDuplicatesFilters() {
    $obj = new TipueSearch;
    $obj->addPage(new SearchPageData('/page.html', 'title', 'contents', array('do', 're', 'mi')));
    $obj->addPage(new SearchPageData('/page.html', 'title', 'contents', array('do', 're', 'mi')));
    $control = <<<EOD
var tipuesearch = {"pages":[{"title":"title","text":"contents","tags":"do re mi","url":"page.html"}]};

EOD;
    $this->assertSame($control, $obj->buildFileContents());
  }

  public function testAddOneGet() {
    $obj = new TipueSearch;
    $obj->addPage(new SearchPageData('/page.html', 'title', 'contents', array('do', 're', 'mi')));
    $control = <<<EOD
var tipuesearch = {"pages":[{"title":"title","text":"contents","tags":"do re mi","url":"page.html"}]};

EOD;
    $this->assertSame($control, $obj->buildFileContents());
  }
      
}
