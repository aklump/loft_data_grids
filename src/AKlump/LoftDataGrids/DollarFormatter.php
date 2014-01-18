<?php
namespace AKlump\LoftDataGrids;

/**
 * Represents a cell formatter.
 */
class DollarFormatter extends Formatter implements FormatterInterface {
  public function get() {
    $base = 1 * preg_replace('/[^\d\.]/', '', $this->data);

    return (string) '$' . number_format($base, 2);
  }
}