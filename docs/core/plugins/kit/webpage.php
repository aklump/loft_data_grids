<?php
/**
 * @file
 * Compile all .kit files a la CodeKit
 *
 * @ingroup loft_docs
 * @{
 */
namespace AKlump\LoftDocs;

require_once dirname(__FILE__) . '/vendor/autoload.php';

use aklump\kit_php\Compiler;
use aklump\loft_parser\HTMLTagRemoveAction;

list(, $source_dir, $output_dir, $outline, $core_dir) = $argv;

// Convert paths to images to include @page
if (isset($source_dir) && isset($output_dir)) {

    $outline = load_outline($outline);

    $obj = new Compiler($source_dir, $output_dir);
    $obj->apply();

    // Remove additional h1 tags from files; we make a general assumption that the
    // tpl header will include an h1 tag, and that if there is another one it has
    // been provided in the source page and should be supressed.
    $parser = new HTMLTagRemoveAction('h1', 1);
    foreach ($obj->getCompiledFiles() as $path) {
        if (($contents = file_get_contents($path))
            && $parser->parse($contents)
            && ($fp = fopen($path, 'w'))
        ) {
            fwrite($fp, $contents);
            fclose($fp);
        }
    }
}
