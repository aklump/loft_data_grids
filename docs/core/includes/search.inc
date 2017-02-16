<?php
/**
 * @file
 * Adds search to the output
 *
 */
namespace AKlump\LoftDocs;

use AKlump\Data\Data;
use Webuni\FrontMatter\FrontMatter;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

list(, $outline, $core_dir, $build_dir, $webpage_dir) = $argv;

$outline = load_outline($outline);

// Search support
if (!empty($outline['settings']['search'])) {
    $search_dir = "$webpage_dir/search";

    $hook_file = $core_dir . "/includes/webpage." . $outline['settings']['search'] . ".sh";
    if (is_file($hook_file)) {
        system("$hook_file $core_dir $search_dir");
    }

    // Now create the search contents
    $searchBuilder = new TipueSearch;

    $source_files = array_filter(scandir($build_dir . '/html/'), function ($value) {
        return preg_match('/\.html$/', $value);
    });

    try {
        foreach ($source_files as $html_file) {
            $searchHtml = new SearchHtml($build_dir . '/html/' . $html_file, true);
            $data = $searchHtml->getData();

            // Get the tags from our front matter
            if ($tags = getFrontMatterTagsFromHtmlFile($html_file)) {
                $data->addTags($tags);
            }

            $searchBuilder->addPage($data);
        }
        $searchBuilder->createFile($search_dir, true);
    } catch (\Exception $e) {
        print $e;
    }
}

/**
 * Uses the filename of the html file and tries to find a markdown source file, parses frontmatter and returns any found tags.
 *
 * @param string $html_file
 *
 * @return array
 */
function getFrontMatterTagsFromHtmlFile($html_file)
{
    global $argv;
    $g = new Data();
    $markdown_source = $argv[5];
    $md_file = pathinfo($html_file, PATHINFO_FILENAME);
    $exists = false;
    foreach (get_markdown_extensions() as $ext) {
        $filename = $markdown_source . '/' . $md_file . '.' . $ext;
        if (file_exists($filename)) {
            $exists = true;
            break;
        }
    }

    $front_matter = array();
    if ($exists && ($contents = file_get_contents($filename))) {
        $fm = new FrontMatter();
        $document = $fm->parse($contents);
        $front_matter = $document->getData();
    }

    return $g->get($front_matter, 'tags', '', function ($value) {
        return array_unique(array_filter(explode(' ', $value)));
    });
}
