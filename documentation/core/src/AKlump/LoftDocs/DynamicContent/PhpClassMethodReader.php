<?php

namespace AKlump\LoftDocs\DynamicContent;

/**
 * A class to generate class method documentation.
 */
class PhpClassMethodReader {

  /**
   * A flag indicating a filter excludes when matched.
   *
   * @var bool
   */
  const EXCLUDE = 1;

  /**
   * A flag indicating a filter includes when matched.
   *
   * @var bool
   */
  const INCLUDE = 2;

  /**
   * Holds all class job definitions.
   *
   * @var array
   */
  protected $scanDefinitions = [];

  /**
   * Holds the existing configuration options.
   *
   * @var array
   */
  protected $config = [];

  /**
   * Holds an array of RegExp for exclusion from all classes.
   *
   * @var array
   */
  protected $excludeRegExp = [];

  /**
   * ClassMethodReader constructor.
   *
   * @param array $config
   *   The configuration settings.
   */
  public function __construct(array $config = []) {
    $this->config = $config;
  }

  /**
   * Add a class to be scanned for method inclusion.
   *
   * @param string $classname
   *   The fully-qualified classname.
   * @param array $filter
   *   A two element indexed array:
   *   - 0 This must be one of \ClassMethodReader::EXCLUDE or
   *   \ClassMethodReader::INCLUDE
   *   - 1 An array of RegExp that will be used to match a method name.  If the
   *   method name matches and the filter is \ClassMethodReader::EXCLUDE, then
   *   that method will be thrown out.  If the filter type is
   *   \ClassMethodReader::INCLUDE, then the method must match an expression tom
   *   be included in the final results.
   * @param callable|NULL $group
   *   A callable that receives the job array and returns a string representing
   *   the group for the class methods tha are returned.
   */
  public function addClassToScan(
    $classname,
    array $filter = [],
    $group = NULL
  ) {
    $filter = empty($filter) ? [self::EXCLUDE, []] : $filter;
    $scan_definition = [
      'class' => trim($classname, '\\'),
      'filter' => $filter,
      'group' => $group,
    ];
    if (is_null($group)) {
      $scan_definition['group'] = function ($scan_definition) {
        $class = explode('\\', $scan_definition['class']);

        return end($class);
      };
    }

    $this->scanDefinitions[] = $scan_definition;
  }

  /**
   * Perform all scans and return the discovered data.
   *
   * @return array
   *   An array keyed by groups, each with the method info.
   *
   * @throws \ReflectionException
   *   If the class cannot be loaded/scanned/reflected.
   */
  public function scan() {
    $methods = [];
    foreach ($this->scanDefinitions as $scan_definition) {
      $group = is_callable($scan_definition['group']) ? $scan_definition['group']($scan_definition) : $scan_definition['group'];
      $methods[$group] = isset($methods[$group]) ? $methods[$group] : [];
      $test = new \ReflectionClass($scan_definition['class']);
      $parent = $test->getParentClass();
      $methods[$group] = array_merge($methods[$group], array_map(function ($item) use ($parent) {
        return [
          'ReflectionMethod' => $item,
          'parent' => $parent ? $parent->name : NULL,
          'name' => $item->name,
          'params' => array_map(function ($item) {
            preg_match('/\[(.+)\]/', strval($item), $matches);
            // Strip tags will remove <required> <optional> flags.
            $params = trim(strip_tags($matches[1]));

            return $params;
          }, $item->getParameters()),
        ];
      }, array_filter($test->getMethods(), function ($item) use ($scan_definition) {
        $return_if_not_found = FALSE;

        foreach ($this->excludeRegExp as $regexp) {
          if (preg_match($regexp, $item->name)) {
            return FALSE;
          }
        }

        if ($item->class === $scan_definition['class']) {
          list($type, $regexps) = $scan_definition['filter'];
          $return_if_not_found = $type === self::INCLUDE ? FALSE : TRUE;
          foreach ($regexps as $regexp) {
            if (preg_match($regexp, $item->name)) {
              return !$return_if_not_found;
            }
          }
        }

        return $return_if_not_found;
      })));

      $found = [];
      $methods[$group] = array_filter($methods[$group], function ($item) use (&$found) {
        $keep = !in_array($item['name'], $found);
        $found[] = $item['name'];

        return $keep;
      });
      usort($methods[$group], function ($a, $b) {
        return strcasecmp($a['name'], $b['name']);
      });
    }

    return $methods;
  }

  /**
   * Set a list of shared excludes RegExp for all methods.
   *
   * @param array $regexps
   *   Each element is a regex, eg. '/^setUp$/' that if matched, will hide a
   *   method for the result set.
   */
  public function excludeFromAll(array $regexps) {
    $this->excludeRegExp = $regexps;
  }

}
