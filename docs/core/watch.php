#!/usr/bin/env php
<?php
/**
 * Begin watching the source directory.
 *
 * You can set a poll interval by passing the seconds as an argument.
 *
 * watch.php 10 = poll for changes ever 10 seconds.
 */
require_once __DIR__ . '/vendor/autoload.php';
$g = new \AKlump\Data\Data();
$poll_interval = $g->get($argv, 1, 20);
$CORE = dirname(__FILE__);
$watch_dir = realpath("$CORE/../source");
$work_from = getcwd();
$files = new Illuminate\Filesystem\Filesystem;
$tracker = new JasonLewis\ResourceWatcher\Tracker;
$watcher = new JasonLewis\ResourceWatcher\Watcher($tracker, $files);
$listener = $watcher->watch($watch_dir);
$listener->modify(function () use ($work_from) {
    echo '.';
    shell_exec("cd $work_from && ./core/compile.sh");
});
echo "Watching for changes every $poll_interval seconds..." . PHP_EOL;
echo "Press CTRL-C to exit" . PHP_EOL;
$watcher->start($poll_interval * 1000000);
