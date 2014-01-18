<?php
namespace AKlump\LoftDataGrids;

/**
 * Class JSONExporter
 */
class JSONExporter extends Exporter implements ExporterInterface {
  protected $extension = '.json';

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'JSON Format',
      'shortname' => 'JSON', 
      'description' => 'Export data in JSON file format. For more information visit: http://www.json.org.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    if ($page_id && array_key_exists($page_id, $pages)) {
      $pages = array($pages[$page_id]);
    }
    $this->output = json_encode($pages);
  }
}