<?php
/**
 * @file
 * Defines the OutlineJson class.
 *
 * @ingroup name
 * @{
 */
namespace AKlump\LoftDocs;

/**
 * Represents an OutlineJson object class.
 *
 * @brief Briefly describe what this class does.
 */
class OutlineJson implements IndexInterface
{

    protected $json;

    /**
     * Constructor
     *
     * @param array $json
     *   The array from Json
     */
    public function __construct($json)
    {
        $this->json = (array) $json;
    }

    public function getData()
    {
        $info = $this->json + array(
                'sections' => array(),
            );
        $data = array();
        $index = array(
            'id'    => 'index',
            'title' => 'Index',
            'file'  => 'index.html',
        );

        foreach ($info['sections'] as $value) {
            $key = pathinfo($value['file'], PATHINFO_FILENAME);
            $weight = isset($value['sort']) ? $value['sort'] : 0;
            if (in_array($key, array('index', 'advanced help settings'))
                && ($title = $this->getTitle($key, $value))
            ) {
                $index['title'] = $title;
            }
            else {
                $data[$weight][$key] = array(
                    'id'    => $value['id'],
                    'title' => $this->getTitle($key, $value),
                    'file'  => pathinfo($value['file'], PATHINFO_FILENAME) . '.html',
                );
            }
        }

        //Sort and Flatten
        ksort($data);
        $list = array();
        foreach ($data as $value) {
            foreach ($value as $key => $value2) {
                $list[$key] = $value2;
            }
        }
        $list = array('index' => $index) + $list;

        // Add in the prev and next links
        $prev = array();
        $last = null;
        foreach ($list as $key => $value) {
            $list[$key] += array(
                'prev_id'    => 'index',
                'prev'       => 'index.html',
                'prev_title' => 'Index',
                'next_id'    => 'index',
                'next'       => 'index.html',
                'next_title' => 'Index',
            );
            if ($last !== null) {
                $list[$last]['next_id'] = $key;
                $list[$last]['next'] = $value['file'];
                $list[$last]['next_title'] = $value['title'];
            }
            if ($prev) {
                $list[$key] = $prev + $list[$key];
            }
            if ($value) {
                $prev = array(
                    'prev_id'    => $key,
                    'prev'       => $value['file'],
                    'prev_title' => $value['title'],
                );
            }
            $last = $key;
        }

        // Set the index prev as the last in the list
        $last = end($list);
        $list['index']['prev_id'] = $last['id'];
        $list['index']['prev'] = $last['file'];
        $list['index']['prev_title'] = $last['title'];

        return $list;
    }

    public function getTitle($default, $value)
    {
        return isset($value['title']) ? $value['title'] :
            (isset($value['name']) ? $value['name'] : $default);
    }
}
