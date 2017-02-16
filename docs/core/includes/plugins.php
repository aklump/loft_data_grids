<?php
/**
 * @file Parse plugins info file and print an include file per $op
 */
use AKlump\Data\Data;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$g = new Data();
list(, $plugins_dir, $type, $op) = $argv;
$info_file = $plugins_dir . "/$type/$type.json";
$plugin = json_decode(file_get_contents($info_file), true);
$include = $plugins_dir . "/$type";
if ($op && ($handler = $g->get($plugin, array('handlers', $op)))) {
    $include .= '/' . $handler;
}
print $include;
