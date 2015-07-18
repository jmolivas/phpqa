PHPQA
=============================================
PHPQA Analyzer CLI tool

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Overview](#overview)
- [Available Analyzers](#available-analyzers)
- [Install](#install)
- [Usage](#usage)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Overview
This project aims to serve as a CLI tool to make easy the use of different PHP tools related to Quality Assurance and code analysis in PHP.

Every analyzer tool handles arguments and options using different formats, the goal of this project is to provide a single way to interact with those projects, you can also set options and arguments using a default configuration file when the project supports it.

> This project was originally developed as part of [Drupal Console](https://github.com/hechoendrupal/DrupalConsole) and based on the blog post [Write your git hooks in PHP and keep them under git control](http://carlosbuenosvinos.com/write-your-git-hooks-in-php-and-keep-them-under-git-control/).

## Available Analyzers

- [PHP Parallel Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint)

  This tool check syntax of PHP files faster then serial check with fancier output.

- [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

  The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.

- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

  PHP_CodeSniffer is a set of two PHP scripts; the main `phpcs` script that tokenizes PHP, JavaScript and CSS files to detect violations of a defined coding standard, and a second `phpcbf` script to automatically correct coding standard violations.

- [PHPMD - PHP Mess Detector](http://phpmd.org/)

   It is a spin-off project of PHP Depend and aims to be a PHP equivalent of the well known Java tool PMD. PHPMD can be seen as an user friendly and easy to configure frontend for the raw metrics measured by PHP Depend.
   
- [PHPLOC](https://github.com/sebastianbergmann/phploc)

  `phploc` is a tool for quickly measuring the size and analyzing the structure of a PHP project
  
- [PHPDCD - PHP Dead Code Detector](https://github.com/sebastianbergmann/phpdcd)

  `phpdcd` is a Dead Code Detector (DCD) for PHP code. It scans a PHP project for all declared functions and methods and reports those as being "dead code" that are not called at least once.

- [PHPCPD - PHP Copy/Paste Detector]

  `phpcpd` is a Copy/Paste Detector (CPD) for PHP code.

- [PHPUnit](https://phpunit.de/)

  PHPUnit is a programmer-oriented testing framework for PHP. It is an instance of the xUnit architecture for unit testing frameworks.

## Install

### Cloning the project
```bash
$ cd ~
$ git clone git@github.com:jmolivas/phpqa.git
$ cd phpqa
# download dependencies
$ composer install
# make phpqa globally accessible creating a symlink 
$ ln -s ~/phpqa/bin/phpqa.php /usr/local/bin/phpqa
```

### Using Composer
```bash
$ composer global require jmolivas/phpqa
# Make sure you add ~/.composer/vendor/bin/ to your PATH.
```

## Usage

### Copy configuration files to user home directory 
```
$ phpqa init
```

### Analyze a project
``` 
$ cd to/project/path
$ phpqa analyze --project=PROJECT --files[=FILES] 
```

| Option  | Description | 
| ------- | ----------------------------- |  
| project | Available default values php, symfony |
| files   | If this option is not set then the files added to git index will be scanned. This is useful when setting executing this tool on a git-hook pre-commit. |    

## What features are planned for development?
- Add support for Drupal and Laravel coding standards.
- Add command to create new project.

> Note: This project is a work-in-progress and need some love related to code clean up and testing coverage.
