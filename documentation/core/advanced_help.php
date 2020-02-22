<?php
/**
 * @file
 * Process advanced help html files to make them fit into said format
 *
 * @param $argv
 *   0: Filepath to the source code file
 *   1: Drupal module name
 *
 * Inside your help file, link to other topics using the format <a
 * href="&topic:module/topic&">. This format will ensure the popup status remains
 * consistent when switching between links.
 *
 * Use <a href="&path&example.jpg"> to reference items within the help directory,
 * such as images you wish to embed within the help text.
 *
 * Use <a href="&base_url&admin/settings/site-configuration"> to reference any
 * normal path in the site.
 *
 *
 * @ingroup loft_docs
 * @{
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';
use aklump\loft_parser\HTMLTagRemoveAction;

// Convert paths to images to include @page
$output = '';
if (isset($argv[1])
  && is_readable($argv[1])
  && ($output = file_get_contents($argv[1]))) {

  // Replace images
  if (preg_match_all('/<img.*?src="((?!http|https|&\w+&)\/?([^"]+))".*?>/', $output, $matches)) {
    foreach (array_keys($matches[0]) as $key) {
      $image = str_replace($matches[1][$key], '&path&' . $matches[2][$key], $matches[0][$key]);
      $output = str_replace($matches[0][$key], $image, $output);
    }
  }

  // Replace links to other help topic pages
  if (preg_match_all('/<a.*?href="((?!http|https|&\w+&)\/?([^"]+))".*?>/', $output, $matches)) {
    foreach (array_keys($matches[0]) as $key) {
      $topic = pathinfo($matches[2][$key]);
      if (!empty($topic['extension'])) {
        $topic = substr($matches[2][$key], 0, -1 * (strlen($topic['extension']) + 1));
      }
      else {
        $topic = $topic['filename'];
      }
      $image = str_replace($matches[1][$key], '&topic:' . $argv[2] . '/' . $topic . '&', $matches[0][$key]);
      $output = str_replace($matches[0][$key], $image, $output);
    }
  }

  // Remove the h1 tag
  $parser = new HTMLTagRemoveAction('h1');
  $parser->parse($output);
}

print $output;

/** @} */ //end of group: loft_docs
