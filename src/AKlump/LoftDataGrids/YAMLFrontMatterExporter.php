<?php

namespace AKlump\LoftDataGrids;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YAMLFrontMatterExporter
 *
 * @link http://symfony.com/doc/current/components/yaml/introduction.html
 * @link http://assemble.io/docs/YAML-front-matter.html
 */
class YAMLFrontMatterExporter extends Exporter implements ExporterInterface {

    public function __construct(ExportDataInterface $data = null, $filename = '')
    {
        parent::__construct($data, $filename);
        unset($this->extension);
    }

    /**
     * We use this magic method to have a configurable extension
     *
     * @param $key
     *
     * @return null
     */
    public function __get($key)
    {
        if ($key === 'extension') {
            return $this->settings->extension;
        }

        return null;
    }

    protected function setSettingsDefault()
    {
        parent::setSettingsDefault();

        /**
         * This is the data key used to fill the body of the file; every other key will be interpreted as YAML frontmatter.
         */
        $this->settings->bodyKey = 'body';
        $this->settings->extension = '.html';

        return $this;
    }

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'YAML Front Matter Format',
                'shortname'   => 'YAML Front Matter',
                'description' => 'Export data as text file with YAML Front Matter. For more information visit: http://www.yaml.org.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $bodyKey = $this->settings->bodyKey;
        // We will only operate on the current page.
        $data = $this->getData()->getPage();

        // We ignore all but the first row
        $row = reset($data);
        $build = array();
        $this->output = '';
        if ($data) {
            $build[] = '---';
            if ($build['fm'] = array_diff_key($row, array($bodyKey => null))) {
                $build['fm'] = trim(Yaml::dump($build['fm']));
            }
            $build[] = '---';
            if (empty($build['fm'])) {
                $build = array();
            }
            $build['body'] =isset($row[$bodyKey]) ? $row[$bodyKey] : '';
            $this->output = implode(PHP_EOL, $build);
        }

        return $this;
    }

}
