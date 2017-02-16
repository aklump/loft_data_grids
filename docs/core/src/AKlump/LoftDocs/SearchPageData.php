<?php
/**
 * @file
 * Defins the SearchPageData class
 */
namespace AKlump\LoftDocs;

/**
 * Represents a SearchPageData object class.
 *
 * @brief Contains page data for search engines.
 */
class SearchPageData
{

    protected $url;
    protected $title;
    protected $contents;
    protected $tags;

    /**
     * Constructor
     *
     * @param string       $url
     * @param string       $title
     * @param string       $contents
     * @param array|string $keywords If string should be space separated.
     */
    function __construct($url = '', $title = '', $contents = '', Array $tags = array())
    {
        $this->url = $this->normalizeUrl($url);
        $this->title = $title;
        $this->contents = $contents;

        // Process tags based on format.
        $this->tags = array_values(array_filter(array_unique($tags)));

        // Trim tags and then look for internal spaces.
        foreach ($this->tags as &$tag) {
            $tag = trim($tag);
            if (strpos($tag, ' ') !== false) {
                throw new \InvalidArgumentException("A single tag \"$tag\" may not contain spaces.");
            }
        }
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Adds tags to whatever was used in the constructor.
     *
     * @param array $tags
     *
     * @return $this
     */
    public function addTags(array $tags)
    {
        $this->tags += $tags;

        return $this;
    }

    public function getTags($flatten = false)
    {
        return $flatten ? implode(' ', $this->tags) : $this->tags;
    }

    protected function normalizeUrl($url)
    {
        $url = trim($url, '/');
        // if (strpos($url, 'http') !== 0) {
        //   $url = '/' . $url;
        // }

        return $url;
    }
}
