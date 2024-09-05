<!--
id: changelog
tags: ''
-->

# Changelog

There are no plans to add any new features to this project.

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.6.0] - 2024-09-04

### Changed

- Simplified PSR-4 directory structure by removing unnecessary directory nesting. (e.g. src/AKlump/LoftDataGrids/ArrayExporter.php -> src/ArrayExporter.php).
- Changed documentation engine from Loft Docs to [Knowledge](https://github.com/aklump/knowledge).
- Upgraded tests to PhpUnit 9.

### Removed

- Doxygene files.
- Simpletest tests.

## [0.5.0] - 2020-02-22

### Changed

- Replaced [PhpExcel](https://github.com/PHPOffice/PHPExcel) with [PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet) to address <https://github.com/aklump/loft_data_grids/issues/3>.

### Removed

- Support for PHP < 7.1
  
