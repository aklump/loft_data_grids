<?php
/**
 * @file
 * Parses Drupal's Advanced Help .ini file and creates page var .kit variables
 *
 * @ingroup loft_docs
 * @{
 */
use AKlump\Data\Data;
use AKlump\LoftDocs\OutlineJson as Index;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$g = new Data();
$index = new Index($argv[1]);

$vars = array(
    'classes' => array(),
);
if (($data = $index->getData()) && isset($data[$argv[2]])) {
    $vars = $data[$argv[2]];
    $vars['classes'] = array('page-' . $vars['id']);
}
$vars['classes'] = implode(' ', $vars['classes']);

// Ensure these default vars
$g->ensure($vars, 'title', '');
$g->ensure($vars, 'prev', 'javascript:void(0)');
$g->ensure($vars, 'prev_id', '');
$g->ensure($vars, 'prev_title', '');
$g->ensure($vars, 'next', 'javascript:void(0)');
$g->ensure($vars, 'next_id', '');
$g->ensure($vars, 'next_title', '');

$declarations = array();
foreach ($vars as $key => $value) {
    $declarations[] = "\$$key = $value";
}

// Add in additional kit vars:
$now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
$declarations[] = '$date = ' . $now->format('r');

$declarations[] = '$version = ' . $argv[3];

// Search support
if (!empty($outline['settings']['search'])) {
    $declarations[] = '$search = true';
    if ($argv[2] === 'search--results') {
        $declarations[] = '$search_results_page = true';
    }
    else {
        $declarations[] = '$search_results_page = false';
    }
}
else {
    $declarations[] = '$search = false';
    $declarations[] = '$search_results_page = false';
}

// Now write the vars
print '<!--' . implode("-->\n<!--", $declarations) . "-->\n";

/** @} */ //end of group: loft_docs
