<?php
use AKlump\LoftDocs\OutlineToHelpIni;

require_once dirname(__FILE__) . '/vendor/autoload.php';

list(, $help_dir, $module_name, $outline_file) = $argv;

$obj = new OutlineToHelpIni($help_dir, $module_name);
$outline = load_outline($outline_file);
$obj->setOutline($outline)->generateFile();
