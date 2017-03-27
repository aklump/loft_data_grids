<?php

namespace AKlump\LoftDataGrids;

/**
 * Class ArrayExporter
 *
 * // Use the prune setting for leaner arrays when possible.  This is best when
 * you know you are only working with one page or one page and one row, and
 * that is consistent in your implementation.
 *
 * @code
 * $obj = new ArrayExporter($data);
 * $obj->addSetting('prune', true);
 * $array = $obj->export();
 * @endcode
 *
 */
class ArrayExporter extends Exporter implements ExporterInterface {

    protected $extension = '.php';

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'Php Array Format',
                'shortname'   => 'Array',
                'description' => 'Export data in PHP array format.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $data = $this->getDataAsTransformedArray();

        if (!is_null($page_id) && array_key_exists($page_id, $data)) {
            $data = array($data[$page_id]);
        }

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
        $this->output = $data;

        return $this;
    }

    public function saveFile($directory, $filename = null, $page_id = null)
    {
        // Go through the setter to ensure the file_extension.
        $filename = $filename ? $this->setFilename($filename) : $this->getFilename();
        if (!is_writable(($directory))) {
            throw new \RuntimeException("$directory is not writable; cannot save $filename.");
        }
        $path = $directory . '/' . $filename;
        $contents = $this->compile($page_id)->export();
        $contents = "<?php" . PHP_EOL . var_export($contents, true) . ';' . PHP_EOL;
        file_put_contents($path, $contents);

        return $path;
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
