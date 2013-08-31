<?php
/**
 * @file
 * ExportData class
 *
 * @ingroup loft_data_grids
 * @{
 */
if (is_file(dirname(__FILE__) . '/../vendor/autoload.php')) {
  require_once dirname(__FILE__) . '/../vendor/autoload.php';
}

/**
 * Interface ExportDataInterface
 */
interface ExportDataInterface {

  /**
   * Set the data key order for the current page
   *
   * @param array|mixed $key
   *   - If this is an array, it will be taken as the key order
   *   - Or, you can list individual keys in correct order as function arguments
   *
   * @return $this
   */
  public function setKeys($key);

  /**
   * Return an array of all keys for current of specified page
   *
   * @return array
   */
  public function getKeys($page_id = NULL);

  /**
   * Add data to the current record
   *
   * @param string $key
   * @param string $value
   *
   * @return $this
   */
  public function add($key, $value);

  /**
   * Returns the record index and advances the record pointer
   *
   * @return int
   *   The index of the record that was just closed
   */
  public function next();

  /**
   * Advance the record pointer to a given index or all the way to the end
   *
   * @param int|NULL $index
   *   (Optional) Defaults to NULL. When NULL the pointer will move to the end
       to begin a new row of data.
   *
   * @return $this
   */
  public function setPointer($index = NULL);

  /**
   * Return the current pointer
   *
   * @return int
   */
  public function getPointer();

  /**
   * Set the current page
   *
   * It's possible to store multiple pages or grids in one object, use this to
     designate the current page to write data to.

     @param mixed $page_id

     @return $this
   */
  public function setPage($page_id);

  /**
   * Return an array of all page ids
   *
   * @return array
   */
  public function getAllPageIds();

  /**
   * Set the order the pages appear in the data array
   *
   * @param string $page_id
   *   Enter all page_ids in correct order as function arguments
   *
   * @return $this
   */
  public function setPageOrder($page_id);

  /**
   * Get the data
   *
   * @return array
   */
  public function get();

  /**
   * Return the current record or data by key
   *
   * @param string $key
   *   (Optional) Defaults to NULL. If excluded the entire current record will
       be returned. If included the value of the current row at $key will be
       returned.
   *
   * @return array | mixed
   */
  public function getCurrent($key = NULL);

  /**
   * Get the current page id
   *
   * @return mixed
   */
  public function getCurrentPageId();

  /**
   * Return a single page of data
   *
   * @param mixed $page_id
   *
   * @return array
   */
  public function getPage($page_id);

  /**
   * Normalize all rows so that they all contain the same number of columns, the
     column order will be taken from the order of the first column
   *
   * @param array|mixed $empty_value
   *   If this is an array then you may provide default values by column key, as
       well as the special key `#default` which is the default value for any
       column whose key is not present in $empty_value.
       If this is not an array, then the value will be used for all empty values
       of all columns, rows, cells, etc.
   *
   * @return $this
   */
  public function normalize($empty_value);

  /**
   * Merge another ExportData object into this one
   *
   * - Rows will be merged by key
   * - All rows will be expanded to have all columns
   *
   * @param ExportData $data
   * @param mixed $empty_value
   *
   * @return $this
   *
   * @see ExportData::normalize()
   */
  public function merge(ExportDataInterface $data, $empty_value);

  /**
   * Find a value in the dataset of the current page
   *
   * @param mixed $value
   * @param mixed $key
   *   (Optional) Defaults to NULL.  Set this to constrain the search by key.
   * @param int $results
   *   (Optional) Defaults to 1. The number of results to return.  Enter 0 for no limit.
   * @param int $direction
   *   (Optional) Defaults to 0. 0 to search from beginning, 1 to search backward from end.
   *
   * @return array
   *   - Keys are the pointers.
   *   - Values are an array of fields in the current pointer
   */
  public function find($value, $key = NULL, $results = 1, $direction = 0);
}

/*
 * Class ExportData
 */
class ExportData implements ExportDataInterface {

  protected $data = array();
  protected $keys = array();
  protected $current_pointers = array();
  protected $current_page = 0;

  /**
   * Constructor
   *
   * @param mixed $page_id
   *   (Optional) Defaults to 0.
   */
  public function __construct($page_id = 0) {
    $this->setPage($page_id);
  }

  public function setKeys($key) {
    if (is_array($key)) {
      $this->keys[$this->current_page] = $key;
    }
    else {
      $this->keys[$this->current_page] = func_get_args();
    }

    // If we have data then we need to go through and modify the order
    if (!empty($this->data[$this->current_page])) {
      foreach ($this->data[$this->current_page] as $row => $data) {
        $new_row = array();
        foreach ($this->keys[$this->current_page] as $key) {
          if (array_key_exists($key, $data)) {
            $new_row[$key] = $data[$key];
          }
        }
        $new_row += $data;
        $this->data[$this->current_page][$row] = $new_row;
      }
    }

    return $this;
  }

  public function getKeys($page_id = NULL) {
    if ($page_id === NULL) {
      $page_id = $this->current_page;
    }
    $keys = array();
    foreach ($this->data[$page_id] as $row) {
      $keys += $row;
    }
    $keys = array_keys($keys);
    $this->setKeys($keys);

    return $keys;
  }

  /**
   * Return the current record pointer for the current page
   */
  public function getPointer() {
    if (!isset($this->current_pointers[$this->current_page])) {
      $this->current_pointers[$this->current_page] = 0;
    }

    return $this->current_pointers[$this->current_page];
  }

  public function add($key, $value) {
    $page = $this->current_page;
    if (empty($this->current_pointers[$page])) {
      $this->current_pointers[$page] = 0;
    }
    $row = $this->current_pointers[$page];

    if (!array_key_exists($page, $this->keys)) {
      $this->keys[$page] = array();
    }

    if (empty($this->data[$page][$row])) {
      $this->data[$page][$row] = array_fill_keys($this->keys[$page], NULL);
    }
    $this->data[$page][$row][$key] = $value;

    return $this;
  }

  public function next() {
    $return = $this->getPointer();
    $this->current_pointers[$this->current_page] = $return + 1;

    return $return;
  }

  public function setPointer($index = NULL) {
    if ($index === NULL) {
      $index = count($this->data[$this->current_page]);
    }
    $this->current_pointers[$this->current_page] = $index;

    return $this;
  }

  public function setPage($page_id) {
    $this->current_page = $page_id;

    return $this;
  }

  public function getCurrentPageId() {
    return $this->current_page;
  }

  public function getAllPageIds() {
    $ids = array_keys($this->get());
    if (!in_array($this->current_page, $ids)) {
      $ids[] = $this->current_page;
    }

    return $ids;
  }

  public function setPageOrder($page_id) {
    $page_ids = func_get_args();
    $temp = array();
    foreach ($page_ids as $page_id) {
      $temp[$page_id] = isset($this->data[$page_id]) ? $this->data[$page_id] : array();
    }
    $temp += $this->data;
    $this->data = $temp;

    return $this;
  }

  public function get() {
    return $this->data;
  }

  public function getCurrent($key = NULL) {
    $current_pointer = $this->getPointer();
    $data = isset($this->data[$this->current_page][$current_pointer])
    ? $this->data[$this->current_page][$current_pointer]
    : array();
    if ($key === NULL) {
      return $data;
    }
    return array_key_exists($key, $data) ? $data[$key] : NULL;
  }

  public function getPage($page_id) {
    return isset($this->data[$page_id]) ? $this->data[$page_id] : array();
  }

  public function normalize($empty_value) {
    foreach (array_keys($this->data) as $page_id) {
      $columns = $this->getKeys($page_id);

      // Now go through and add all columns
      $column_order = array();
      foreach ($this->data[$page_id] as $key => $row) {

        if (is_array($empty_value)) {
          if (array_key_exists($key, $empty_value)) {
            $empty = $empty_value[$key];
          }
          elseif (array_key_exists('default', $empty_value)) {
            $empty = $empty_value['default'];
          }
          else {
            $empty = NULL;
          }
        }
        else {
          $empty = $empty_value;
        }

        $this->data[$page_id][$key] += array_fill_keys($columns, $empty);

        // Assure the same column order for all rows
        if (empty($column_order)) {
          $column_order = array_keys($this->data[$page_id][$key]);
        }
        $ordered = array();
        foreach ($column_order as $col_key) {
          $ordered[$col_key] = $this->data[$page_id][$key][$col_key];
        }
        $this->data[$page_id][$key] = $ordered;
      }
    }

    return $this;
  }

  //@todo write a test for merge
  public function merge(ExportDataInterface $data, $empty_value) {

    // Normalize columns on incoming
    $this->normalize($empty_value);
    $data->normalize($empty_value);

    foreach (array_keys($this->data) as $page_id) {
      foreach ($this->data[$page_id] as $key => $row) {
        $this->data[$page_id][$key] += $row;
      }
    }

    // Normalize result
    $this->normalize($empty_value);

    return $this;
  }

  public function find($needle, $key = NULL, $results = 1, $direction = 0) {
    $result_set = array();
    $haystack = $this->getPage($this->current_page);
    if ($direction == 1) {
      $haystack = array_reverse($haystack, TRUE);
    }
    foreach ($haystack as $pointer => $row) {
      $set = $row;
      if ($key !== NULL) {
        $set = array($key => $row[$key]);
      }
      if ($found = array_intersect($set, array($needle))) {
        $result_set[$pointer] = $found;
      }
      if ($results && count($result_set) === $results) {
        break;
      }
    }

    return $result_set;
  }
}
