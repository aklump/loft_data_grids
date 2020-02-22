<?php

/**
 * @file
 * Version sniffing from various file formats
 *
 * @return  string actual/path/used version.string
 *
 * @see     http://www.intheloftstudios.com/packages/shell/web_package
 * @see     https://getcomposer.org/doc/04-schema.md#version
 *
 * @ingroup loft_docs
 * @{
 */

use Symfony\Component\Yaml\Yaml;

require_once dirname(__FILE__) . '/vendor/autoload.php';


$version_file = $argv[3];

if (is_file($version_file)) {
  $extension = pathinfo($version_file, PATHINFO_EXTENSION);
  $contents = file_get_contents($version_file);

  switch ($extension) {

    // Web Package
    case 'info':
      $data = (object) parse_ini_file($version_file);
      break;

    // Composer or other json file with version as a first child
    case 'json':
      $data = (object) json_decode($contents);
      break;

    // Composer or other json file with version as a first child
    case 'yml':
    case 'yaml':
      $data = (object) Yaml::parse($contents);
      break;
  }

  print isset($data->version) ? $data->version : '';
}
