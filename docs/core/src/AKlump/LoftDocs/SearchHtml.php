<?php
/**
 * @file
 * Defines the SearchHtml class.
 *
 * @ingroup name
 * @{
 */
namespace AKlump\LoftDocs;

/**
 * Represents a SearchHtml object class.
 *
 * @brief Converts an html document into a SearchPageData object.
 */
class SearchHtml
{

    protected $html, $url;
    protected $tags = array();

    /**
     * Constructor method for a new SearchHtml.
     */
    public function __construct($html = '', $is_file = false)
    {
        if ($is_file) {
            if (!is_file($html) || !($contents = file_get_contents($html))) {
                throw new \InvalidArgumentException("$html is not a file or is not readable.");
            }
            $this->url = basename($html);
            $html = $contents;
        }
        $this->html = $html;
    }

    /**
     * Returns the parsed data from the html
     *
     * @param  string $url This should be provided if you did not instantiate
     *                     with a file, if you did the url will be parsed from
     *                     that.  You may provide an url that would override a
     *                     parsed one to0.
     *
     * @return SearchPageData
     */
    public function getData($url = '')
    {
        $url = empty($url) ? $this->url : $url;
        $title = $tags = '';
        $contents = $this->html;

        if (preg_match('/(<h.+?>(.+?)<\/h.>)/si', $this->html, $matches)) {
            $title = $matches[2];

            // Pull out the meta data section.
            $tags = substr($contents, 0, strpos($contents, $matches[1]));
            $contents = substr($contents, strpos($contents, $matches[1]));

            // Remove the title from the contents.
            $contents = str_replace($matches[0], '', $contents);
        }

        if ($tags && preg_match('/tags\s*:(.*?)</si', $tags, $matches)) {
            $tags = explode(' ', $matches[1]);
        }
        else {
            $tags = array();
        }

        // Insert spaces for new lines to help format the strip_tags.
        $contents = implode(' ', explode(PHP_EOL, $contents));
        $contents = strip_tags($contents);

        return new SearchPageData($url, $title, $contents, $tags);
    }
}
