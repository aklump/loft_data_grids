<?php
/**
 * @file
 * PHPUnit tests for the SearchHtml class
 */

namespace AKlump\LoftDocs;
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class SearchHtmlTest extends \PHPUnit_Framework_TestCase {

  public function testNoTags() {
    $subject = "<h1>My nice title</h1><p>lorem ipsum</p>";
    $obj     = new SearchHtml($subject);
    $this->assertSame($obj->getData()->getTitle(), 'My nice title');
    $this->assertSame($obj->getData()->getContents(), 'lorem ipsum');
    $this->assertSame($obj->getData()->getTags(), array());
  }

  public function testTitle() {
    $subject = "<p>tags    : do re mi<br /><h1>My nice title</h1><p>lorem ipsum</p>";
    $obj     = new SearchHtml($subject);
    $this->assertSame($obj->getData()->getTitle(), 'My nice title');
    $this->assertSame($obj->getData()->getContents(), 'lorem ipsum');
    $this->assertSame($obj->getData()->getTags(), array('do', 're', 'mi'));
  }

  public function testReturnsObject() {
    $obj = new SearchHtml();
    $this->assertInstanceOf('\AKlump\LoftDocs\SearchPageData', $obj->getData());  
  }
      
}
