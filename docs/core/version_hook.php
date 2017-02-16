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
$version_file = $argv[3];

if (is_file($version_file)) {
    $extension = pathinfo($version_file, PATHINFO_EXTENSION);

    $version = '';
    switch ($extension) {

        // Web Package
        case 'info':
            $data = parse_ini_file($version_file);
            if (isset($data['version'])) {
                $version = $data['version'];
            }
            break;

        // Composer or other json file with version as a first child
        case 'json':
            $data = file_get_contents($version_file);
            if (($json = json_decode($data)) && isset($json->version)) {
                $version = $json->version;
            }
            break;
    }

    print $version;
}
