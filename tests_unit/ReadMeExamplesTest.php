<?php

/**
 * @coversNothing
 */
class ReadMeExamplesTest extends \PHPUnit\Framework\TestCase {


  public function testJSON() {
    $data_grid = $this->grid;

    $exporter = new \AKlump\LoftDataGrids\JSONExporter($data_grid);
    $json_string = $exporter->export();
    $this->assertSame('[[{"Name":"Adam","Age":39},{"Name":"Brandon","Age":37},{"Name":"Charlie","Age":7}],[{"Color":"Black","Make":"Honda"},{"Color":"White","Make":"BMW"}]]', $json_string);
  }

  public function testCSV() {
    $data_grid = $this->grid;

    $exporter = new \AKlump\LoftDataGrids\CSVExporter($data_grid);
    $csv_string = $exporter->export();
    $actual = str_replace('%s', "\r\n", "\"Name\",\"Age\"%s\"Adam\",\"39\"%s\"Brandon\",\"37\"%s\"Charlie\",\"7\"%s");
    $this->assertSame($actual, $csv_string);

    $csv_string = $exporter->export(1);
    $actual = str_replace('%s', "\r\n", "\"Color\",\"Make\"%s\"Black\",\"Honda\"%s\"White\",\"BMW\"%s");
    $this->assertSame($actual, $csv_string);
  }


  public function setUp(): void {
    $data_grid = new \AKlump\LoftDataGrids\ExportData();

    // By default we're on page 0, row 0.
    $data_grid->add('Name', 'Adam')->add('Age', 39)->next();
    $data_grid->add('Name', 'Brandon')->add('Age', 37)->next();
    $data_grid->add('Name', 'Charlie')->add('Age', 7)->next();

    // Switch to page 1; we'll be placed on row 0.
    $data_grid->setPage(1);
    $data_grid->add('Color', 'Black')->add('Make', 'Honda')->next();
    $data_grid->add('Color', 'White')->add('Make', 'BMW')->next();

    $this->grid = $data_grid;
  }
}
