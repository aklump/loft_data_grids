<?php
/**
 * @file
 * Boostrapper and function declarations
 *
 * @ingroup loft_docs
 * @{
 */

use AKlump\Data\Data;

/**
 * Parse all todos in a string
 *
 * @param  string $string Looking for this pattern  '- [ ] @todo...EOL'
 * @param  string $prefix A prefix to the todo item; used to include the file
 *                        name in the todo item.
 *
 * @return array
 */
function parse_todos($string, $prefix = '') {
  $todos = array();
  if (is_string($string)
    //&& preg_match_all('/- \[ \] @todo.*$/m', $string, $matches)) {
    && preg_match_all('/- \[ \] .*$/m', $string, $matches)
  ) {

    if (!empty($prefix) || !empty($remove_todo)) {
      foreach (array_keys($matches[0]) as $key) {

        if ($prefix) {
          $matches[0][$key] = str_replace('- [ ] ', "- [ ] $prefix", $matches[0][$key]);
        }
      }
    }
    $todos = $matches[0];
  }

  return $todos;
}

/**
 * Flattens an array of todo items into a string
 *
 * @param  array $array
 *
 * @return string
 */
function flatten_todos($array) {
  if (is_array($array)) {
    return implode("\n", array_filter($array)) . "\n";
  }
}

/**
 * Sort an array of todo items by @w flag
 *
 * @param  array &$todos
 *
 * @return bool
 *   True on success.
 */
function sort_todos(&$todos) {
  $result = TRUE;
  if (is_array($todos)) {
    $result = usort($todos, '_sort_todos');
    $todos = array_values(array_filter(array_unique($todos)));
  }

  return $result;
}

/**
 * Helper for usort
 */
function _sort_todos($a, $b) {
  if (get_weight($a) === get_weight($b)) {
    return 0;
  }

  return get_weight($a) > get_weight($b) ? 1 : -1;
}

/**
 * Return the numeric weight of a todo item
 *
 * @param  string $string
 *
 * @return int||float
 */
function get_weight($string) {
  if (preg_match_all('/@w([\d\.]+)/', $string, $matches)) {
    return 1 * end($matches[1]);
  }

  return 0;
}

/**
 * Returns a string read to be used as an id.
 *
 * @param  string $text
 *
 * @return string
 */
function clean_id($text) {
  $regex = get_markdown_extensions(TRUE);
  $text = preg_replace($regex, '', $text);

  return strtolower($text);
}

/**
 * Returns a string to be used as a chapter/section title.
 *
 * @param  string $text
 *
 * @return string
 */
function clean_title($text) {
  $regex = get_markdown_extensions(TRUE);
  $text = preg_replace($regex, '', $text);

  return ucwords(preg_replace('/[-_]/', ' ', $text));
}

/**
 * Return the recognized markdown extensions (without the dot).
 *
 * @param  boolean $regex True for a regex expression.
 *
 * @return array|string
 */
function get_markdown_extensions($regex = FALSE) {
  $ext = array(
    'md',
    'markdown',
    'mdown',
    'mkdn',
    'mkd',
    'mdwn',
    'mdtxt',
    'mdtext',
    'text',
    'Rmd',
  );

  return $regex ? '/\.(' . implode('|', $ext) . ')$/' : $ext;
}

/**
 * Returns the markdown file extension for a filename
 *
 * @param  string $file This is the filename relative to the source directory
 *                      without extension.
 *
 * @return string
 */
function get_md_source_file_extension($file) {
  //@todo make this dynamic? by looking up the file and matching filename and reading in the extension? see getFrontMatterTagsFromHtmlFile().
  $extensions = get_markdown_extensions();

  return '.' . reset($extensions);
}

/**
 * Loads the outline json file and fills in missing defaults.
 *
 * @param  string $outline_file
 *
 * @return array
 */
function load_outline($outline_file) {
  $outline = array();

  if (file_exists($outline_file)) {
    $outline = json_decode(file_get_contents($outline_file), TRUE);
    $outline += array(
      'appendices' => array(),
      'authors' => array(),
      'chapters' => array(),
      'cover' => array(),
      'sections' => array(),
      'settings' => array('search' => 'tipuesearch'),
    );
  }

  return $outline;

}

function _sort_weight($a, $b) {
  $g = new Data;
  $a = $g->get($a, 'weight', 0);
  $b = $g->get($b, 'weight', 0);

  return $a - $b;
}

function _sort_sort($a, $b) {
  $g = new Data;
  $a = $g->get($a, 'sort', 0);
  $b = $g->get($b, 'sort', 0);

  return $a - $b;
}

/**
 * Merge all instances of an outline json into the one and return it.
 *
 * Latter arguments superscede former ones.
 *
 * @return array The merged outline array.
 */
function json_outline_merge() {
  $g = new Data();
  $args = func_get_args();
  $base = array_shift($args);
  foreach ($args as $merge) {
    foreach (array_keys($merge) as $key) {
      $g->ensure($base, $key, array());
      $g->ensure($merge, $key, array());
      switch ($key) {
        case 'title':
          $base[$key] = $merge[$key];
          break;

        case 'chapters':
        case 'appendices':
          $base[$key] = _json_array_replace_by_id($base[$key], $merge[$key]);
          break;

        case 'sections':
          $base[$key] = _json_array_merge_by_id($base[$key], $merge[$key]);
          break;

        default:
          $base[$key] = array_replace_recursive($base[$key], $merge[$key]);
          break;
      }
    }
  }

  return $base;
}

/**
 * Replace $a element keys with $b element keys matching on 'id'.
 *
 * The resulting array is in the order of $b.
 *
 * @param array $a
 * @param array $b
 *
 * @return array
 */
function _json_array_merge_by_id(array $a, array $b) {
  $g = new Data();
  if (empty($a)) {
    return $b;
  }
  $a = array_combine(array_map(function ($a) {
    return $a['id'];
  }, $a), $a);
  $b = array_combine(array_map(function ($b) {
    return $b['id'];
  }, $b), $b);

  foreach (array_keys($b) as $key) {
    if (isset($a[$key])) {
      $b[$key] += $a[$key];
    }
  }

  return array_values($b + $a);
}

/**
 * Helper function.
 *
 * @param $a
 * @param $b
 *
 * @return array
 *
 * @see json_outline_merge().
 */
function _json_array_replace_by_id(array $a, array $b) {
  foreach ($b as $item) {
    $added = FALSE;
    foreach ($a as &$a_item) {
      if ($a_item['id'] == $item['id']) {
        $a_item = array_replace_recursive($a_item, $item);
        $added = TRUE;
      }
    }
    if (!$added) {
      $a[] = $item;
    }
  }

  return $a;
}
