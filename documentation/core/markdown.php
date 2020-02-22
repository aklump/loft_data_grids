<?php

/**
 * @file
 * Run the markdown compiler
 *
 * @in group loft_docs
 * @{
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';

use AKlump\LoftDocs\Compiler;
use AKlump\LoftDocs\MarkdownExtra;
use AKlump\LoftLib\Bash\Color;
use AKlump\LoftLib\Storage\FilePath;
use Webuni\FrontMatter\FrontMatter;

$in_file = $argv[1];
$html_output_dir = rtrim($argv[2], '/');
$static_source_dir = $argv[3];
$twig_extension = $argv[4];
$template_dirs = explode(':', $argv[5]);
$dynamic_source_dir = $argv[6];
$outline_file = $argv[7];
$markdown_extension = $argv[8];

$is_file = is_file($in_file);

if (!$is_file) {
  exit(1);
}

try {

  $compiler = new Compiler(
    FilePath::create($static_source_dir),
    FilePath::create($dynamic_source_dir),
    FilePath::create($outline_file)
  );

  $path_info = pathinfo($in_file);

  // Twig Pre-Processing if ends in .twig.md.
  if (substr($in_file, -1 * strlen($twig_extension)) === $twig_extension) {
    $out_file = $html_output_dir . '/' . preg_replace('/\.twig$/', '', $path_info['filename']) . '.html';
    $loader = new Twig_Loader_Filesystem($template_dirs);
    $twig = new Twig_Environment($loader, array(
      'cache' => FALSE,
    ));

    $twig_vars = $compiler->getVariables();
    $regex = '/^' . preg_quote($static_source_dir, '/') . '/';
    $relative_file = trim(preg_replace($regex, '', $in_file), '/');
    $contents = $twig->render($relative_file, $twig_vars);

    // Save a preprocessed version to cache/source.
    $compiler->addSourceFile(str_replace($twig_extension, $markdown_extension, $relative_file), $contents);
  }
  else {
    $out_file = $html_output_dir . '/' . $path_info['filename'] . '.html';
    $contents = file_get_contents($in_file);
  }

  $fm = new FrontMatter();
  $document = $fm->parse($contents);
  $contents = $document->getContent();
  $data = $document->getData();

  if (isset($data['twig'])) {
    foreach ($data['twig'] as $find => $replace) {
      $data['tokens']["{{ $find }}"] = $replace;
    }
  }

  // If the tokens frontmatter key is present then we need to perform a token replace.
  if (isset($data['tokens'])) {
    uksort($data['tokens'], function ($a, $b) {
      $a = strlen($a);
      $b = strlen($b);

      return $b - $a;
    });
    foreach ($data['tokens'] as $find => $replace) {
      $contents = str_replace($find, $replace, $contents);
    }
  }

  $my_html = MarkdownExtra::defaultTransform($contents);
  $my_html = $compiler->processInternalLinks($my_html);

  file_put_contents($out_file, $my_html);
  exit(0);
}
catch (\Exception $exception) {
  echo Color::wrap('red', 'In section: ' . $path_info['filename'] . ': ' . $exception->getMessage());
  exit(1);
}
