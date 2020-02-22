<?php
/**
 * @file
 * Parses Drupal's Advanced Help .ini file to create the directory or index
 *
 * @ingroup loft_docs
 * @{
 */

use AKlump\LoftDocs\OutlineJson as Index;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$index = new Index($argv[1]);

$list = array();
foreach ($index->getData() as $key => $value) {
  // Skip a self reference
  if ($key == 'index') {
    continue;
  }
  $list[] = '<a href="' . $value['file'] . '">' . $value['title'] . '</a>';
}

$tpl_dir = $argv[2];
$list = implode("</li>\n<li>", $list);
$output = <<<EOD
<ul class="index"><li>{$list}</li></ul>
EOD;

print $output;

/** @} */ //end of group: loft_docs
