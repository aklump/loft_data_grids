<?php
/**
 * @file
 * Extract the tasks from a single file and append them to the global file.
 *
 * @ingroup loft_docs
 * @{
 */

use AKlump\LoftDocs\Compiler;
use AKlump\LoftLib\Storage\FilePath;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$source = FilePath::create($argv[1], ['install' => FALSE]);
$merged_basename = $argv[2];
$dynamic_source_dir = FilePath::create($argv[3]);
$outline_file = FilePath::create($argv[4], ['install' => FALSE]);
$compiler = new Compiler(FilePath::create(dirname($argv[1])), $dynamic_source_dir, $outline_file);
$global = $compiler->getInclude($merged_basename);

if (!$compiler->getSetting('tasklist.aggregate', FALSE)) {
  exit(2);
}

// Make sure to create an empty file so the build doesn't fail.
if (!$global->exists()) {
  $global->save();
}

$first_run = $argv[5] === 'true';
if ($first_run) {
  echo "Aggregating todo items";
}

$todos = [];
if (!$source->exists()
  || (($contents = $source->load()->get())
    && ($prefix = $source->getFileName() . ': ')
    && !($todos = parse_todos($contents, $prefix)))
) {
  exit(1);
}

// Merge any tasks from the global list.
if ($global->exists()
  && ($contents = $global->load()->get())
  && ($global_list = parse_todos($contents))
) {
  $todos = array_merge($global_list, $todos);
}

if ($todos && sort_todos($todos) && ($contents = flatten_todos($todos))) {
  $global->put($contents)->save();
  echo '.';
}

exit(0);
