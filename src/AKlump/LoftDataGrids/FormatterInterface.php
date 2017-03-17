<?php
namespace AKlump\LoftDataGrids;

interface FormatterInterface {

    public function set($data);

    public function get();

    public function getUnformatted();
}
