<?php
/**
 * Wrap a partial with the twig tpl wrappers.
 */

use AKlump\Data\Data;
use aklump\loft_parser\HTMLTagRemoveAction;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

$g = new Data();
list (, $tpl_dir, $partial, $destination, $vars) = $argv;
$vars = json_decode($vars, TRUE);

$loader = new Twig_Loader_Filesystem($tpl_dir);
$twig = new Twig_Environment($loader, array(
  'cache' => FALSE,
));

switch ($partial) {
  case 'index.html':
    $vars['breadcrumbs'] = [];
    $vars['is_index'] = TRUE;
    $vars['title'] = $g->get($vars, 'book.title', 'Index');
    $vars['content'] = $twig->render('index.twig', $vars);
    $vars['classes'][] = 'page--index';

    $next = reset($vars['index']);
    $g->getThen($next, 'id')->set($vars, 'next_id');
    $g->getThen($next, 'title')->set($vars, 'next_title');
    $g->getThen($next, 'file')->set($vars, 'next');

    $prev = end($vars['index']);
    $g->getThen($prev, 'id')->set($vars, 'prev_id');
    $g->getThen($prev, 'title')->set($vars, 'prev_title');
    $g->getThen($prev, 'file')->set($vars, 'prev');
    break;

  default:
    $vars['breadcrumbs'][] = ['Index', 'index.html'];
    if (($chapter = $g->get($vars, 'chapter'))) {
      $chapter_id = $g->get($vars, 'chapter_id');
      $crumb = [$chapter];
      if (!empty($vars['chapters'][$chapter_id]['href'])) {
        $crumb[] = $vars['chapters'][$chapter_id]['href'];
      }
      $vars['breadcrumbs'][] = $crumb;
    }
    $vars['is_index'] = FALSE;
    $vars['content'] = '<section>' . file_get_contents($partial) . '</section>';
    break;
}

$build = $twig->render('page.twig', $vars);

// Remove additional h1 tags from files; we make a general assumption that the
// tpl header will include an h1 tag, and that if there is another one it has
// been provided in the source page and should be supressed.
$parser = new HTMLTagRemoveAction('h1', 1);
$parser->parse($build);

file_put_contents($destination, $build);
