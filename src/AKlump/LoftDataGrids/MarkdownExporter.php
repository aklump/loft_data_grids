<?php
namespace AKlump\LoftDataGrids;

/**
 * Class MarkdownExporter
 */
class MarkdownExporter extends Exporter implements ExporterInterface {
  protected $extension = '.md';

  public function getInfo() {
    $info = parent::getInfo();
    $info = array(
      'name' => 'Markdown',
      'shortname' => 'Markdown',
      'description' => 'Export data in Markdown file format. For more information visit: http://daringfireball.net/projects/markdown/.',
    ) + $info;

    return $info;
  }

  public function compile($page_id = NULL) {
    $pages = $this->getData()->get();
    $this->output = '';
    $multi = count($pages) > 1 ? '#' : '';
    $output  = '';
    foreach ($pages as $page_id => $page) {
      if ($multi) {
        $output .= "{$multi}# Page " . ($page_id + 1) . "\n";
      }
      foreach ($page as $record_id => $record) {
        $index = 1;
        $output .= "{$multi}## Record " . ($record_id + 1) . "\n";
        foreach ($record as $key => $value) {
          $output .= $index++ . ". __{$key}__: {$value}\n";
        }
        $output .= "\n";
      }
      $output .= "\n";
    }
    $this->output = $output;
  }
}