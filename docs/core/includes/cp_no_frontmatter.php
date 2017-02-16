<?php
/**
 * Copy a file from A to B removing all FrontMatter
 */
use Webuni\FrontMatter\FrontMatter;

require_once dirname(__FILE__) . '/../vendor/autoload.php';
$from = $argv[1];
$to = $argv[2];

if (!file_exists($from)) {
    return 1;
}

$fm = new FrontMatter();
if (($contents = file_get_contents($from))) {
    $document = $fm->parse($contents);

    // Store any metadata
    if ($vars = $document->getData()) {
        $data_file = getenv('LOFT_DOCS_CACHE_DIR') . '/page_data.json';
        $json = file_exists($data_file) ? json_decode(file_get_contents($data_file), true) : array();
        $json[pathinfo($to, PATHINFO_FILENAME)] = $vars;
        file_put_contents($data_file, json_encode($json));
    }
    file_put_contents($to, $document->getContent());
}

return file_exists($to) ? 0 : 1;
