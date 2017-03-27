<?php

namespace AKlump\LoftDataGrids;

/**
 * Class JSONExporter
 *
 * // Use the prune setting for leaner JSON when possible.  This is best when
 * you know you are only working with one page or one page and one row, and
 * that is consistent in your implementation.
 *
 * @code
 * $obj = new JsonExporter($data);
 * $obj->addSetting('prune', true);
 * $json = $obj->export();
 * @endcode
 *
 */
class JSONExporter extends Exporter implements ExporterInterface {

    protected $extension = '.json';

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'JSON Format',
                'shortname'   => 'JSON',
                'description' => 'Export data in JSON file format. For more information visit: http://www.json.org.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $data = $this->getDataAsTransformedArray($page_id, null);

        // Prune?
        if ($this->getSettings()->prune) {

            // Prune pages.
            if (count($data) === 1) {
                $data = reset($data);
            }

            // Prune rows
            if (count($data) === 1) {
                $data = reset($data);
            }
        }
        $this->output = json_encode($data);

        return $this;
    }

    protected function setSettingsDefault()
    {
        parent::setSettingsDefault();

        // When true...
        // ... if there is only one page do not include a page key.
        // ... if there is only one row do not include a row key.
        $this->settings->prune = false;

        return $this;

    }
}
