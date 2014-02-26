<?php
namespace AKlump\LoftDataGrids;

/**
 * Represents a cell formatter.
 *
 * This class provides no formatting.  The get() method should be extended
 * and to copy $this->data, manipulate and return it.
 */
class Formatter implements FormatterInterface {
  protected $data;

  public function __construct($data = NULL) {
    if ($data !== NULL) {
      $this->set($data);
    }
  }

  public function set($data) {
    $this->data = $data;
  }

  public function get() {
    $data = $this->data;
    // Extend this method to provide some formatting of $data
    return $data;
  }
  
  public function getUnformatted() {
    return $this->data;
  }
}