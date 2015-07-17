#!/usr/bin/env php
<?php

/*
 * Based on
 * http://carlosbuenosvinos.com/write-your-git-hooks-in-php-and-keep-them-under-git-control/
 */

if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require_once __DIR__.'/../vendor/autoload.php';
} elseif (file_exists(__DIR__.'/../../../autoload.php')) {
    require_once __DIR__.'/../../../autoload.php';
} else {
    echo 'Something goes wrong with your archive'.PHP_EOL.
      'Try downloading again'.PHP_EOL;
    exit(1);
}

use JMOlivas\Phpqa\Console\Application;
use JMOlivas\Phpqa\Command\AnalyzeCommand;
use JMOlivas\Phpqa\Command\InitCommand;

$application = new Application('PHP QA Analyzer', '0.0.1');
$application->add(new AnalyzeCommand());
$application->add(new InitCommand());
$application->run();
