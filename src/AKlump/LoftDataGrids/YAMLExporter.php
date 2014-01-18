<?php
namespace AKlump\LoftDataGrids;

/**
 * Class YAMLExporter
 */
class YAMLExporter extends Exporter implements ExporterInterface {
  protected $extension = '.yaml';

  public function __construct(ExportDataInterface $data, $filename = '') {
    parent::__construct($data, $filename);
  }

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'YAML Format',
      'shortname' => 'YAML', 
      'description' => 'Export data in YAML file format. For more information visit: http://www.yaml.org.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    $this->output = Yaml::dump($pages);
  }
}