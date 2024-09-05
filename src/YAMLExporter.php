<?php
namespace AKlump\LoftDataGrids;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YAMLExporter
 *
 * http://symfony.com/doc/current/components/yaml/introduction.html
 */
class YAMLExporter extends Exporter implements ExporterInterface {

    protected $extension = '.yml';

    public function __construct(ExportDataInterface $data = null, $filename = '')
    {
        parent::__construct($data, $filename);
    }

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'YAML Format',
                'shortname'   => 'YAML',
                'description' => 'Export data in YAML file format. For more information visit: http://www.yaml.org.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $pages = $this->getDataAsTransformedArray();
        if (!is_null($page_id) && array_key_exists($page_id, $pages)) {
            $pages = $pages[$page_id];
        }
        $this->output = Yaml::dump($pages);

        return $this;
    }

}
