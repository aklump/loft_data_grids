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
    }

    public function import($string)
    {
        $obj = new ExportData();
        $header = $body = null;

        // Give the body a default empty string.
        $obj->add($this->settings['bodyKey'], '');

        $chunk = strtok($string, '---');
        while (($chunk !== false)) {
            $chunk = trim($chunk);
            if (is_null($header)) {
                try {
                    $header = Yaml::parse($chunk);
                    foreach ($header as $key => $item) {
                        $obj->add($key, $item);
                    }
                } catch (\Exception $exception) {
                    $header = false;
                    $obj->add($this->settings['bodyKey'], $chunk);
                }
            }
            elseif (is_null($body)) {
                $obj->add($this->settings['bodyKey'], $chunk);
            }
            else {
                break;
            }
            $chunk = strtok('---');
        }

        return $obj;
    }
}
