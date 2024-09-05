# Loft Data Grids

![](https://badgen.net/static/status/deprecated/red) [![Packagist link](https://badgen.net/packagist/name/aklump/loft_data_grids)](https://packagist.org/packages/aklump/loft_data_grids) ![](https://badgen.net/packagist/php/aklump/loft_data_grids) ![](https://badgen.net/github/license/aklump/dom-testing-selectors)

> There will be no new features added to this project. I suggest using the [Symfony Serializer Component](https://symfony.com/doc/current/components/serializer.html) instead for similar functionality. The project will not receive ongoing support.

## Summary

This package is a PHP object-oriented solution for modelling data in two (rows + columns) or three dimensions (rows + columns + pages). It can be thought of like a spreadsheet.

It allows a single data class `ExportData` to be used to organize your data in a grid, with various output styles `Exporter` so you can easily get a `.csv` file or a `.xlsx` file, among many others.

See the code for more documentation.

## Install with Composer

1. Require this package:
   
    ```
    composer require aklump/loft_data_grids:^0.5
    ```

## Usage

Let's build a two-layer data grid. (Layers are called _pages_.) The first page will contain names and ages of three people. The second page will contain vehicle information. It can be pictured in two tables:

*Page 0*
| Name | Age |
|---------|-----|
| Adam | 39 |
| Brandon | 37 |
| Charlie | 7 |

*Page 1*
| Color | Make |
|-------|-------|
| Black | Honda |
| White | BMW |

### In Code

```php
$data_grid = new \AKlump\LoftDataGrids\ExportData();

// By default we're on page 0, row 0.
$data_grid->add('Name', 'Adam')->add('Age', 39)->next();
$data_grid->add('Name', 'Brandon')->add('Age', 37)->next();
$data_grid->add('Name', 'Charlie')->add('Age', 7)->next();

// Switch to page 1; we'll be placed on row 0.
$data_grid->setPage(1);
$data_grid->add('Color', 'Black')->add('Make', 'Honda')->next();
$data_grid->add('Color', 'White')->add('Make', 'BMW')->next();
```

### Accessing data from the object

Think of _pointer_ as a row in a table.

```php
$value = $data_grid->setPage(0)->setPointer(0)->getValue('Name') // $value === 'Adam'
$value = $data_grid->getValue('Name') // $value === 'Adam'
$value = $data_grid->setPointer(2)->getValue('Name') // $value === 'Charlie'
$value = $data_grid->setPointer(0)->get() // $value === array('Name' => 'Adam', 'Age' => 39)
$value = $data_grid->setPage(1)->setPointer(1)->getValue('Color') // $value === 'White'
```

### Exporting data to other formats

We can export both pages in CSV like this:

```php
$exporter = new \AKlump\LoftDataGrids\CSVExporter($data_grid);

$csv_string = $exporter->export();
// "Name","Age"
// "Adam","39"
// "Brandon","37"
// "Charlie","7"

$csv_string = $exporter->export(1);
// "Color","Make"
// "Black","Honda"
// "White","BMW"
```

Or to get it as JSON...

```php
$exporter = new \AKlump\LoftDataGrids\JSONExporter($data_grid);
$json_string = $exporter->export();
```

_(This has been formatted for easier-reading; the actual JSON is minified.)_

```json
[
  [
    {
      "Name": "Adam",
      "Age": 39
    },
    {
      "Name": "Brandon",
      "Age": 37
    },
    {
      "Name": "Charlie",
      "Age": 7
    }
  ],
  [
    {
      "Color": "Black",
      "Make": "Honda"
    },
    {
      "Color": "White",
      "Make": "BMW"
    }
  ]
]
```

Or any of the other exporter classes.

### Saving to File

```php
$exporter = new \AKlump\LoftDataGrids\XLSXExporter($data_grid, 'users');
$exporter->saveFile();
```

## Exporters: objects as values

* Exporters can handle objects if they implement the `objectHandler` method.
* Exporters can handle \DateTime objects if they set a value for 'dateFormat'.

## Testing

1. Run the PhpUnit tests with `./bin/run_unit_tests.sh`
