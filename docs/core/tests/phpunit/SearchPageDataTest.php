<?php
/**
 * @file
 * PHPUnit tests for the SearchPageData class
 */

namespace AKlump\LoftDocs;
require_once dirname(__FILE__) . '/../../vendor/autoload.php';

class SearchPageDataTest extends \PHPUnit_Framework_TestCase {
  
  /**
   * @expectedException \Exception
   */
  public function testTagsWithSpacesThrows() {
    new SearchPageData('','', '', array('my tag', 'yours'));
  }
      
}
