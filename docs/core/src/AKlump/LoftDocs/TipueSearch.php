<?php
/**
 * @file
 * Defines the TipueSearch class.
 *
 * @ingroup name
 * @{
 */
namespace AKlump\LoftDocs;

/**
 * Represents a TipueSearch object class.
 *
 * @brief Integrates TipueSearch with LoftDocs
 */
class TipueSearch
{

    const FILENAME = "tipuesearch_content.js";

    protected $pages = array();

    /**
     * Adds a page to the index.
     *
     * @param SearchPageData $data
     */
    public function addPage(SearchPageData $data)
    {
        $url = $data->getUrl();
        $this->pages[$url] = array(
            'title' => $data->getTitle(),
            'text'  => $data->getContents(),
            'tags'  => $data->getTags(true),
            'url'   => $url,
        );

        return $this;
    }

    /**
     * Builds the search content file contents as a string.
     *
     * @return string
     */
    public function buildFileContents()
    {
        $data = array('pages' => $this->getPages());
        $output = array();
        $output[] = "var tipuesearch = " . json_encode($data) . ";";
        $output[] = null;

        return implode(PHP_EOL, $output);
    }

    /**
     * Creates a search contents file in $directory.
     *
     * @param  string $directory
     *
     * @return this
     */
    public function createFile($directory, $force = false)
    {
        if (!is_dir($directory) || !is_writeable($directory)) {
            throw new \InvalidArgumentException("Invalid output directory or not writeable: $directory");
        }

        $path = $directory . '/' . self::FILENAME;

        if ($force && is_file($path)) {
            unlink($path);
        }

        if (file_put_contents($path, $this->buildFileContents()) === false) {
            throw new \RuntimeException("Unable to write file: $path");
        }

        return $this;
    }

    /**
     * Returns all pages sorted and made unique with numerical keys.
     *
     * @return array
     */
    protected function getPages()
    {
        $pages = $this->pages;
        ksort($pages);
        $pages = array_values($pages);

        return $pages;
    }

}
