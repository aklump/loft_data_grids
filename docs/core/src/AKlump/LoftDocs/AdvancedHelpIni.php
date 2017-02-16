<?php
/**
 * @file
 * Defines the AdvancedHelpIni class.
 *
 * @ingroup name
 * @{
 */
namespace AKlump\LoftDocs;

/**
 * Represents an AdvancedHelpIni object class.
 *
 * @brief Briefly describe what this class does.
 */
class AdvancedHelpIni implements IndexInterface
{

    protected $path;

    /**
     * Constructor
     *
     * @param path $path
     *   The path to the ini file
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getTitle($default, $value)
    {
        return isset($value['title']) ? $value['title'] :
            (isset($value['name']) ? $value['name'] : $default);
    }

    public function getData()
    {
        $info = parse_ini_file($this->path, true);
        $data = array();
        $index = array(
            'id'    => 'index',
            'title' => 'Index',
            'file'  => 'index.html',
        );

        foreach ($info as $key => $value) {
            $weight = isset($value['weight']) ? $value['weight'] : 0;
            if (in_array($key, array('index', 'advanced help settings'))
                && ($title = $this->getTitle($key, $value))
            ) {
                $index['title'] = $title;
            }
            else {
                $data[$weight][$key] = array(
                    'id'    => $key,
                    'title' => $this->getTitle($key, $value),
                    'file'  => $key . '.html',
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
}
