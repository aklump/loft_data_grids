<?php

namespace AKlump\LoftDataGrids;

/**
 * Represents a cell formatter.
 */
class DollarFormatter extends Formatter implements FormatterInterface {

  public function get() {
    $data = empty($this->data) ? 0 : $this->data;
    $base = 1 * preg_replace('/[^\d\.]/', '', $data);

    return (string) '$' . number_format($base, 2);
  }
}
