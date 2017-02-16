<?php
/**
 * @file
 * Run all tests at once
 *
 * @ingroup ova_data
 * @{
 */
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
class AllFileTests extends TestSuite{
  function __construct() {
    parent::__construct();
    $this->collect(dirname(__FILE__), new SimplePatternCollector('/_test.php/'));
  }
}

/** @} */ //end of group: ova_data
