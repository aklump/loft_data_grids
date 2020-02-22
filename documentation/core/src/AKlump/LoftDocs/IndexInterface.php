<?php

namespace AKlump\LoftDocs;

/**
 * Represents the interface for an Index.
 */
interface IndexInterface {

  /**
   * Get the index data from the source file and return as array in proper
   * order
   *
   * @return array
   *   Keys are the filenames (no path, no extension)
   *   Values are arrays with the following keys
   *   - id string The page id.
   *   - title string The title of the page.
   *   - file string The basename of the file.
   *   - prev string The page id of the previous page.
   *   - prev_title The page title of the previous page.
   *   - next string The page id of the next page.
   *   - next_title string The page title of the next page.
   */
  public function getData();

  /**
   * Return the correct title for a page
   *
   * @param string $default
   *   The should probably be the page id.
   * @param array $value
   *   Looking for keys: title or name
   *
   * @return string
   */
  public function getTitle($default, $value);
}
