<?php


namespace AKlump\LoftDataGrids;


interface ImporterInterface {

    /**
     * Return info about this class
     *
     * @return array
     *   - name string The human name of this importer
     *   - shortname string A more concise human name for ui elements like
     *   option lists
     *   - description string A further description
     *   - extension string The file extension used by this class
     */
    public function getInfo();

    /**
     * Import data from a format returning and ExportData object.
     *
     * @param string $string The data as returned by an ExporterClass::export().
     *
     * @return mixed
     */
    public function import($string);


    /**
     * Adds/Updates a single setting by name.
     *
     * You can also use $this->getSettings()->{name} = {value}.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function addSetting($name, $value);
}
