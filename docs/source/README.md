## Summary
This package is a PHP object-oriented solution for modelling data in two (rows + columns) or three dimensions (rows + columns + pages).  It can be thought of like a spreadsheet.

It allows a single data class `ExportData` to be used to organize your data in a grid, with various output styles `Exporter` so you can easily get a `.csv` file or a `.xlsx` file, among many others.

See the code for more documentation.

## Requirements
1. \>= Php 5.3.0

## Installation
1. Please install necessary dependencies using [Composer](http://getcomposer.org/).
2. Navigate to the root of this package and type: `composer install`      

## Documentation
1. Refer to the Doxygene documentation included in this package.

## Automated Tests

### PhpUnit

1. Run the PhpUnit tests using _phpunit.xml_ in the directory _tests/phpunit/_

### Legacy: Simpletest

1. Ensure you have downloaded a copy of [simpletest](http://simpletest.org/) as `tests/simpletest/simpletest/` directory.
1. Run `php all_tests.php` from the CLI using a supported PHP version.
1. You should see:

        all_tests.php
        OK
        Test cases run: 1/1, Passes: 32, Failures: 0, Exceptions: 0


## Example Usage

### Building a data object

In this example we'll build a 2 paged model, the first page contains two columns (names and ages) of three people.  The second page will contain two rows of vehicle information (color and make).

    $obj = new ExportData();
    
    // By default we're on page 0, row 0.
    $obj->add('Name', 'Aaron')->add('Age', 39)->next();
    $obj->add('Name', 'Hillary')->add('Age', 37)->next();
    $obj->add('Name', 'Maia')->add('Age', 7)->next();

    // Switch to page 1; we'll be placed on row 0 when the new page is created.
    $obj->setPage(1);
    $obj->add('Color', 'Black')->add('Make', 'Subaru')->next();
    $obj->add('Color', 'White')->add('Make', 'Hyundai')->next();

### Accessing data from the object

    $obj->setPage(0)->setPointer(0)->getValue('Name') === 'Aaron'
    $obj->getValue('Name') === 'Aaron'
    $obj->setPointer(2)->getValue('Name') === 'Maia'
    $obj->setPointer(0)->get() === array('Name' => 'Aaron', 'Age' => 39)

    $obj->setPage(1)->setPointer(1)->getValue('Color') === 'White'
 
### Exporting data to other formats

And now to get that as a CSV file we do...

    $exporter = new CSVExporter($obj);
    $csv_string = $exporter->export();

Or to get it as JSON...

    $exporter = new JSONExporter($obj);
    $json_string = $exporter->export();

Or any of the other exporter classes.

### Saving to File

    use AKlump\LoftDataGrids\XLSXExporter;

    $exporter = new XLSXExporter($obj, 'users');
    $exporter->saveFile();


## Contact

* **In the Loft Studios**
* Aaron Klump - Developer
* PO Box 29294 Bellingham, WA 98228-1294
* _aim_: theloft101
* _skype_: intheloftstudios
* _d.o_: aklump
* <http://www.InTheLoftStudios.com>
