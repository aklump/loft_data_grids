<?php
namespace AKlump\LoftDataGrids;

/**
 * Class XMLExporter
 */
class XMLExporter extends Exporter implements ExporterInterface {

    protected $extension = '.xml';

    public function getInfo()
    {
        $info = parent::getInfo();
        $info = array(
                'name'        => 'XML Format',
                'shortname'   => 'XML',
                'description' => 'Export data in XML file format.',
            ) + $info;

        return $info;
    }

    public function compile($page_id = null)
    {
        $xml = new \SimpleXMLElement('<data/>');
        $pages = $this->getDataAsTransformedArray($page_id, $page_id);
        foreach ($pages as $page_id => $data) {
            $page = $xml->addChild('page');
            $page->addAttribute('id', $page_id);
            foreach ($data as $id => $data_set) {
                $set = $page->addChild('record');
                $set->addAttribute('id', $id);
                foreach ($data_set as $key => $value) {
                    // make sure the key is in good format
                    $key = preg_replace('/[^a-z0-9_-]/', '_', strtolower($key));
                    // Wrap cdata as needed
                    if (strstr($value, '<') || strstr($value, '&')) {
                        $value = '<![CDATA[' . $value . ']]>';
                    }
                    $set->addChild($key, $value);
                }
            }
        }
        $this->output = $xml->asXML();
        $this->output = str_replace('&lt;![CDATA[', '<![CDATA[', $this->output);
        $this->output = str_replace(']]&gt;</', ']]></', $this->output);

        return $this;
    }
}
