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

  # This will grab the search engine files...
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

    $g = new Data();
    foreach ($source_files as $html_file) {
      $record = getOutlineRecordByHtmlFile($html_file);

      // Does json or front matter tell us to ignore this file?
      if ($g->get($record, 'search') === 'noindex'
        || (($front_matter = getFrontMatterFromHtmlFile($html_file)) && $g->get($front_matter, 'search') === 'noindex')
      ) {
        continue;
      }

      $searchHtml = new SearchHtml($build_dir . '/html/' . $html_file, TRUE);
      $data = $searchHtml->getData();

      // Get the tags from our front matter
      if ($tags = getTags($front_matter)) {
        $data->addTags($tags);
      }

      $searchBuilder->addPage($data);
    }
    $searchBuilder->createFile($search_dir, TRUE);
  }
  catch (\Exception $e) {
    print $e;
  }
}

function getOutlineRecordByHtmlFile($html_file) {
  global $outline;
  foreach ($outline['sections'] as $item) {
    if ($item['file'] === $html_file) {
      return $item;
    }
  }

  return [];
}

/**
 * Pulls out the tags from a front matter array.
 *
 * @param array $front_matter
 *
 * @return mixed
 */
function getTags(array $front_matter) {
  $g = new Data();

  return $g->get($front_matter, 'tags', '', function ($value) {
    return array_unique(array_filter(explode(' ', $value)));
  });
}


/**
 * Uses the filename of the html file and tries to find a markdown source file,
 * parses frontmatter and returns it.
 *
 * @param string $html_file
 *
 * @return array
 */
function getFrontMatterFromHtmlFile($html_file) {
  global $argv, $outline;
  $markdown_source = $argv[5];
  $md_file = pathinfo($html_file, PATHINFO_FILENAME);
  $exists = FALSE;
  foreach (get_markdown_extensions() as $ext) {
    $filename = $markdown_source . '/' . $md_file . '.' . $ext;
    if (file_exists($filename)) {
      $exists = TRUE;
      break;
    }
  }

  $front_matter = array();
  if ($exists && ($contents = file_get_contents($filename))) {
    $fm = new FrontMatter();
    $document = $fm->parse($contents);
    $front_matter = $document->getData();
  }

  // Now look for it in the outline
  if (!empty($outline['frontmatter'][$html_file])) {
    $front_matter = $outline['frontmatter'][$html_file] + $front_matter;
  }

  return $front_matter;
}

