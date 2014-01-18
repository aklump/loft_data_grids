<?php
namespace AKlump\LoftDataGrids;

/**
 * Represents a cell formatter.
 */
abstract class Formatter implements FormatterInterface {
  protected $data;

  public function __construct($data = NULL) {
    if ($data !== NULL) {
      $this->set($data);
    }
  }

  public function set($data) {
    $this->data = $data;
  }

  abstract public function get();
  
  public function getUnformatted() {
    return $this->data;
  }
}