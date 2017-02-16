<?php
/**
 * @file
 * Extract the todo items from a single file and append them to the global file
 *
 * @ingroup loft_docs
 * @{
 */
require_once dirname(__FILE__) . '/../vendor/autoload.php';

$source = $argv[1];
$global = $argv[2];
$todos = array();

// Skip this file if no todo items.
$prefix = '' . pathinfo($source, PATHINFO_FILENAME) . ': ';
if (!is_readable($source)
    || (($contents = file_get_contents($source))
        && (!($todos = parse_todos($contents, $prefix))))
) {

    return;
}

// Merge any todo items from the global list.
if (($global_list = file_get_contents($global))
    && ($global_list = parse_todos($global_list))
) {
    $todos = array_merge($global_list, $todos);
}

sort_todos($todos);

if (($contents = flatten_todos($todos))) {
    $contents = "#Tasklist\n<pre>$contents</pre>";
    file_put_contents($global, $contents);
}
