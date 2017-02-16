<?php
/**
 * @file
 * Base class for PHPUnit tests requiring files
 *
 * @defgroup loft_phpunit Loft PHPUnit
 * @{
 */
abstract class LoftPHPUnit_Framework_TestCase extends PHPUnit_Framework_TestCase {

  protected $paths;

  /**
   * Include a php file as if being called form a BASH script
   *
   * @param string $path
   * @param... Additional params will be sent as $argv
   *
   * @return string
   *   The captured output
   */
  public function includeCLI($filename) {
    $args = func_get_args();
    $filename = array_shift($args);
    $argv = '';
    if (count($args)) {
      $argv = implode(' ', $args);
    }
    $cmd = "php $filename $argv";
    exec($cmd, $return);
    $return = implode(PHP_EOL, $return);

    return $return;
  }

  protected function getTempDir() {
    return sys_get_temp_dir() . '/com.aklump.loft_phpunit';
  }

  /**
   * Write a string to a file
   *
   * @param string $contents
   * @param string $file
   *   Do not include the path in this argument
   * @param string $dir
   *   (Optional) Defaults to $this->getTempDir(). You may define a directory or
   *   directories that will be created inside the temp dir.
   *
   * @return string
   *   If the file is created the entire path to the file
   */
  protected function writeFile($contents, $file, $dir = NULL) {

    // Make sure the file is inside the temp dir
    if ($dir && strpos($dir, $this->getTempDir()) === 0) {
      $dir = substr($dir, strlen($this->getTempDir()));
    }
    $dir = $this->getTempDir() . '/' . trim($dir, '/');

    if (!is_dir($dir)) {
      mkdir($dir, 0700, TRUE);
    }

    if (is_writable($dir)) {
      $fp = fopen($dir . '/' . $file, 'w');
      fwrite($fp, $contents);
      fclose($fp);
      $this->paths[] = $dir . '/' . $file;
    }

    return is_file($dir . '/' . $file) ? $dir . '/' . $file : FALSE;
  }

  /**
   * Remove all files created during the test
   */
  function tearDown() {
    // Delete all of our temporary files
    if (is_dir($this->getTempDir())) {
      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($this->getTempDir(), RecursiveDirectoryIterator::SKIP_DOTS),
          RecursiveIteratorIterator::CHILD_FIRST
      );

      foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
      }

      rmdir($this->getTempDir());
    }
  }
}

/** @} */ //end of group: loft_phpunit
