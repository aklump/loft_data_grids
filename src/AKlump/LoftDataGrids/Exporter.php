<?php

namespace AKlump\LoftDataGrids;

/**
 * Class Exporter
 */
abstract class Exporter implements ExporterInterface {

    protected $export_data, $title, $filename, $extension, $output;
    protected $header = array();

    protected $settings;

    /**
     * Constructor
     *
     * @param ExportDataInterface $data
     * @param string              $filename
     *   (Optional) Defaults to ''.
     */
    public function __construct(ExportDataInterface $data = null, $filename = '')
    {
        $this->setSettingsDefault();
        if (isset($data)) {
            $this->setData($data);
        }
        $this->setFilename($filename);
    }

    public function getInfo()
    {
        return array(
            'class'       => get_class($this),
            'name'        => get_class($this),
            'description' => get_class($this),
            'extension'   => $this->extension,
        );
    }

    public function addSetting($name, $value)
    {
        $this->settings->{$name} = $value;

        return $this;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings)
    {
        $this->settings = new \stdClass;
        foreach ($settings as $name => $value) {
            $this->addSetting($name, $value);
        }

        return $this;
    }

    public function setFilename($filename)
    {
        $extension = trim($this->extension, '.');
        $filename = $this->filenameSafe($filename, array(
            'extensions' => array($extension),
            'ext'        => $extension,
        ));
        $info = pathinfo($filename);
        if ($info['filename']) {
            $filename = $info['filename'];
        }
        else {
            $filename = time();
        }
        $this->filename = trim($filename, '.') . '.' . trim($this->extension, '.');

        return $this->filename;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getData()
    {

        // @todo I feel like this is a really bloated way of doing this
        // by creating a new object, etc.  Maybe we can do this differntly...

        // We pause our locations so that our iterations don't mess with things,
        // we'll later resume below.
        $this->export_data->storeLocation('getData');
        $locations = $this->export_data->getLocations();

        // Sort the data into the correct order based on the header
        $pages = $this->export_data->get();
        $temp = new ExportData();
        foreach ($pages as $page_id => $data) {
            $temp->setPage($page_id);
            $header = $this->getHeader($page_id);
            foreach ($data as $d) {
                foreach (array_keys($header) as $key) {
                    $temp->add($key, $d[$key]);
                }
                $temp->next();
            }
        }
        $this->setData($temp);

        $this->export_data->setLocations($locations);
        $this->export_data->gotoLocation('getData');

        return $this->export_data;
    }

    public function getHeader($page_id = 0)
    {
        $rows = $this->export_data->getPage($page_id);
        $keys = array_keys(reset($rows));

        return array_combine($keys, $keys);
    }

    public function setData(ExportDataInterface $data)
    {
        $this->export_data = $data;
        $data->normalize('');

        return $this;
    }

    public function formatColumn($column, $format_code)
    {
        $formatter = null;
        switch ($format_code) {
            case 'USD':
                $formatter = new DollarFormatter();
                break;
        }
        if (empty($formatter)) {
            return;
        }

        // Iterate all pages, all records on column and format cell
        $data = $this->getData();
        foreach ($data->getAllPageIds() as $page_id) {
            $page = $data->getPage($page_id);
            foreach ($page as $record_id => $row) {
                if (isset($row[$column])) {
                    $formatter->set($row[$column]);
                    $data->setPage($page_id);
                    $data->setPointer($record_id);
                    $data->add($column, $formatter->get());
                }
            }
        }

        return $this->compile();
    }

    public function export($page_id = null)
    {
        $this->compile($page_id);

        return $this->output;
    }

    public function saveFile($directory, $filename = null, $page_id = null)
    {
        // Go through the setter to ensure the file_extension.
        $filename = $filename ? $this->setFilename($filename) : $this->getFilename();
        if (!is_writable(($directory))) {
            throw new \RuntimeException("$directory is not writable; cannot save $filename.");
        }
        $path = $directory . '/' . $filename;
        file_put_contents($path, $this->compile($page_id)
                                      ->export());

        return $path;
    }

    public function save($filename = '', $page_id = null)
    {

        // Make sure we have rendered the data
        if (empty($this->output)) {
            $this->compile($page_id);
        }

        // Assure the correct file extension
        if ($filename) {
            $this->setFilename($filename);
        }

        $filename = $this->getFilename();

        // Make sure we don't timeout
        $original = (int) ini_get('memory_limit');
        $memory_limit = strlen($this->output); //bytes
        $memory_limit /= 1048576; //convert to megabytes
        $memory_limit *= 2; //double the memory so we don't run out
        if ($memory_limit > $original) {
            $memory_limit *= 20; //double the memory so we don't run out
            $memory_limit = max($original, round($memory_limit)) . 'M';
            ini_set('memory_limit', $memory_limit);
        }

        // Send download headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . strlen($this->output));

        // Send contents
        print $this->output;
        exit();
    }

    public function showPageIds()
    {
        $this->addSetting('showPageIds', true);

        return $this;
    }

    public function hidePageIds()
    {
        $this->addSetting('showPageIds', false);

        return $this;
    }

    public function getShowPageIds()
    {
        return $this->getSettings()->showPageIds;
    }

    /**
     * Setup default values on object data.
     *
     * Child classes should implement like this, making sure you don't use
     * setting name already defined in the parents.
     *
     * @code
     *   protected function setSettingsDefault() {
     *     parent::setSettingsDefault();
     *     $this->settings->showSponsors = TRUE;
     *
     *     return $this;
     *   }
     * @endcode
     *
     * @return {$this}
     */
    protected function setSettingsDefault()
    {
        $this->settings = (object) array(
            'showPageIds' => true,
            'dateFormat'  => false,
        );

        return $this;
    }

    /**
     * Iterate over all cells and transform data as appropriate.
     */
    protected function dataTransform(&$value)
    {
        if (is_object($value)) {

            // Convert dates to the settings for date_format.
            if (($dateFormat = $this->getSettings()->dateFormat) && $value instanceof \DateTime) {
                $value = $value->format($dateFormat);
            }

            // Handle other objects as needed.
            if (method_exists($this, 'objectHandler')) {
                $value = $this->objectHandler($key, $value);
            }
        }
    }

    /**
     * Convert ExportData to an array transforming every cell.
     *
     * @param mixed $page_id           The page id from which to pull data or
     *                                 empty for all pages.
     * @param mixed $page_id_key       If $page_id is provided, set this to the
     *                                 key value to use to indicate the page
     *                                 id, for example JSON keeps this as NULL
     *                                 and XML uses the page id.
     *
     * @return array
     *
     * @see $this->dataTransform().
     */
    protected function getDataAsTransformedArray($page_id = null, $page_id_key = null)
    {
        $data = $this->getData()->get();
        if (!is_null($page_id)) {
            $data = is_null($page_id_key) ? array($data[$page_id]) : array($page_id_key => $data[$page_id]);
        }
        foreach ($data as &$page) {
            foreach ($page as &$row) {
                foreach ($row as &$cell) {
                    $this->dataTransform($cell);
                }
            }
        }

        return $data;
    }

    /**
     * Return a string as a safe filename
     *
     * @param string $string
     *   The candidtate filename.
     * @param array  $options
     *   - array extensions: allowable extensions no periods
     *   - string ext: default extension if non found; blank for none no period
     *
     * @return string
     * - lowercased, with only letters, numbers, dots and hyphens
     *
     * @see file_munge_filename().
     */
    protected function filenameSafe($string, $options = array())
    {
        $options += array(
            'extensions' => array('txt', 'md'),
            'ext'        => 'txt',
        );
        $string = preg_replace('/[^a-z0-9\-\.]/', '-', strtolower($string));
        $string = preg_replace('/-{2,}/', '-', $string);

        // Add an extension if not found
        if ($options['ext'] && !preg_match('/\.[a-z]{1,5}$/', $string)) {
            $string .= '.' . trim($options['ext'], '.');
        }

        if ($string && function_exists('file_munge_filename')) {
            $string = file_munge_filename($string, implode(' ', $options['extensions']), false);
        }

        //@todo Add in the module that cleans name if it's installed
        return $string;
    }

    protected function cssSafe($string)
    {
        $string = preg_replace('/[^a-z0-9\-]/', '-', strtolower($string));
        $string = preg_replace('/^\d/', 'c-\0', $string);

        return trim(preg_replace('/-{2,}/', '-', $string), '-');
    }
}
