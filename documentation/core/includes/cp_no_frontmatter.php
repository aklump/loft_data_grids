<?php

/**
 * @file
 * Copy a file from A to B removing all FrontMatter.
 */

use AKlump\LoftDocs\Compiler;
use AKlump\LoftLib\Storage\FilePath;
use Webuni\FrontMatter\FrontMatter;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$source_basename = pathinfo($argv[1], PATHINFO_BASENAME);
$to = $argv[2];
$static_source_dir = $argv[3];
$dynamic_source_dir = $argv[4];
$outline_file = $argv[5];

try {
  $compiler = new Compiler(
    FilePath::create($static_source_dir),
    FilePath::create($dynamic_source_dir),
    FilePath::create($outline_file)
  );
  if (!($from = $compiler->getSource($source_basename)->getPath())) {
    $from = $argv[1];
  }

  if (!is_file($from)) {
    exit(1);
  }

  $fm = new FrontMatter();
  if (($contents = file_get_contents($from))) {
    $document = $fm->parse($contents);

    // Store any metadata.
    if ($vars = $document->getData()) {
      $data_file = getenv('LOFT_DOCS_CACHE_DIR') . '/page_data.json';
      $json = file_exists($data_file) ? json_decode(file_get_contents($data_file), TRUE) : array();
      $json[pathinfo($to, PATHINFO_FILENAME)] = $vars;
      file_put_contents($data_file, json_encode($json));
    }
    file_put_contents($to, $document->getContent());
  }

  return file_exists($to) ? 0 : 1;
}
catch (\Exception $exception) {
  echo $exception->getMessage();
  exit(1);
}
