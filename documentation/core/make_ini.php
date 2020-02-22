<?php

/**
 * @file
 * Generates the advanced help ini file for Drupal advanced help.
 */

use AKlump\LoftDocs\OutlineToHelpIni;

require_once dirname(__FILE__) . '/vendor/autoload.php';

list(, $help_dir, $module_name, $outline_file) = $argv;

if (file_exists($help_dir)) {
  $obj = new OutlineToHelpIni($help_dir, $module_name);
  $outline = load_outline($outline_file);
  $obj->setOutline($outline)->generateFile();
}
