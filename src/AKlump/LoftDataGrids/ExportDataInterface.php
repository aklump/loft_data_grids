<?php
namespace AKlump\LoftDataGrids;

/**
 * Interface ExportDataInterface
 */
interface ExportDataInterface {

  /**
   * Set locations using a formatted array.
   *
   * @see  getLocations() for formatting.
   */
  public function setLocations($locations);

  /**
   * Return an array of location data keyed by location_id
   *
   * @return array
   * - keys are location_ids
   * - values: arrays
   *   - pointers
   *   - page
   */
  public function getLocations();

  /**
   * Store the current page and pointer location for later resuming
   *
   * @param  string $location_id A unique key or preset id to remember this location by.
   *
   * @return $this              
   */
  public function storeLocation($location_id);

  /**
   * Goto to a stored location
   *
   * @param  string $location_id A unique key or preset id to remember this
   * location by.  If nothing has been stored using this id, nothing happens.
   *
   * @return $this              
   */
  public function gotoLocation($location_id);

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
   * Add (or Set) data to the current record
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
   *   to begin a new row of data.
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
   * designate the current page to write data to.
   *
   * @param mixed $page_id
   *
   * @return $this
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
   *   be returned. If included the value of the current row at $key will be
   *   returned.
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
   * Return a single page as an ExportData object
   *
   * @param  mixed $page_id
   *
   * @return ExportDataInterface
   */
  public function getPageData($page_id);

  /**
   * Delete a single page from the object
   *
   * @param  mixed $page_id
   *
   * @return $this
   */
  public function deletePage($page_id);

  /**
   * Normalize all rows so that they all contain the same number of columns, the
   * column order will be taken from the order of the first column
   *
   * @param array|mixed $empty_value
   *   If this is an array then you may provide default values by column key, as
   *   well as the special key `#default` which is the default value for any
   *   column whose key is not present in $empty_value.
   *   If this is not an array, then the value will be used for all empty values
   *   of all columns, rows, cells, etc.
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
