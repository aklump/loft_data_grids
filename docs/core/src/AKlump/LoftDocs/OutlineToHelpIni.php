<?php
namespace AKlump\LoftDocs;

use Piwik\Ini\IniWriter;

class OutlineToHelpIni
{
    protected $filepath;
    protected $options = array(
        'line break'  => true,
        'readme file' => false,
    );
    protected $outline = array();

    /**
     * OutlineToHelpIni constructor.
     *
     * @param       $filepath
     * @param array $options
     */
    public function __construct($help_dir, $module_name, array $options = null)
    {
        $this->filepath = rtrim($help_dir, '/') . '/' . $module_name . '.help.ini';
        if (!is_null($options)) {
            $this->options = $options;
        }
    }

    /**
     * @return array
     */
    public function getOutline()
    {
        return $this->outline;
    }

    /**
     * @param string|object $outline Passing a json string or an object
     *                               defining the index.
     *
     * @return $this
     */
    public function setOutline($outline)
    {
        $this->outline = is_string($outline) ? json_decode($outline) : $outline;

        return $this;
    }

    public function generateFile()
    {
        $data = array();
        $data['advanced help settings'] = $this->options;
        $outline = $this->getOutline();
        uasort($outline['sections'], '_sort_sort');
        foreach ($outline['sections'] as $section) {
            $data[pathinfo($section['file'], PATHINFO_FILENAME)] = array(
                'title'  => $section['title'],
                'weight' => $section['sort'],
            );
        }
        $obj = new IniWriter();
        $obj->writeToFile($this->filepath, $data);
    }
}
