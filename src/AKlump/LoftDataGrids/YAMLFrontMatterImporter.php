<?php

namespace AKlump\LoftDataGrids;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YAMLFrontMatterImporter
 *
 * Pull data out of a YAMLFrontMatterExporter output string into an ExportData
 * object.
 *
 * @package AKlump\LoftDataGrids
 *
 * @see     YAMLFrontMatterExporter
 */
class YAMLFrontMatterImporter implements ImporterInterface {

    protected $settings = array(
        'bodyKey' => 'body',
    );

    public function getInfo()
    {
        $info = array(
            'name'        => 'YAML Text File with Front Matter Format',
            'shortname'   => 'YAML + Text',
            'description' => 'Import data from a text file with YAML Front Matter.',
        );

        return $info;
    }

    public function addSetting($key, $value)
    {
        $this->settings[$key] = $value;

        return $this;
    }

    public function import($string)
    {
        $obj = new ExportData();
        $bodyKey = $this->settings['bodyKey'];
        $header = null;
        $body = $string;
        $chunks = array_values(array_filter(explode('---', $string, 3)));
        if(count($chunks) === 2) {
          $header = trim($chunks[0]);
          $body = trim($chunks[1]);
        }
        if($header) {
          try {
            $header = Yaml::parse($header);
            if(is_array($header)) {
              foreach ($header as $key => $item) {
                $obj->add($key, $item);
              }
            }
          } catch (\Exception $exception) {
            $header = null;
          }
        }

      $obj->add($bodyKey, $body);

        // Give the body a default empty string.
        if (!in_array($bodyKey, $obj->getKeys())) {
            $obj->add($bodyKey, '');
        }

        return $obj;
    }
}
