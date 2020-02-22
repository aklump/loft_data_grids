<?php
/**
 * @file
 * Prints the realpath of argv1
 *
 */
print ($path = realpath($argv[1])) ? $path : $argv[1];
