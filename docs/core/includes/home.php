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

$outline = load_outline($argv[1]);
$index = new Index($outline);

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
