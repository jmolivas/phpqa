PHPQA
=============================================
PHPQA Analyzer CLI tool

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Overview](#overview)
- [Install Stand Alone](#install-stand-alone)
  - [Clone project](#clone-project)
  - [Install dependencies](#install-dependencies)
  - [Create symbolink link](#create-symbolink-link)
- [Include in your project](#include-in-your-project)
- [How to run this project](#how-to-run-this-project)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Overview
This project aims to serve as a CLI tool to unify the use of different PHP tools related to Quality Assurance and code analysis in PHP.

Every analyzer tool handles arguments and options using different formats, the goal of this project is to provide and single way to interact with those projects, you can also set options and arguments using the default configuration file the project supports.

> This project was originally developed as part of [Drupal Console](https://github.com/hechoendrupal/DrupalConsole) and based on the blog post [Write your git hooks in PHP and keep them under git control](http://carlosbuenosvinos.com/write-your-git-hooks-in-php-and-keep-them-under-git-control/).

## Available Analyzers

- [PHP Parallel Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint)
  This tool check syntax of PHP files faster then serial check with fancier output.

- [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)
  The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.

- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
  PHP_CodeSniffer is a set of two PHP scripts; the main phpcs script that tokenizes PHP, JavaScript and CSS files to detect violations of a defined coding standard, and a second phpcbf script to automatically correct coding standard violations.

- [PHPMD - PHP Mess Detector](http://phpmd.org/)
   It is a spin-off project of PHP Depend and aims to be a PHP equivalent of the well known Java tool PMD. PHPMD can be seen as an user friendly and easy to configure frontend for the raw metrics measured by PHP Depend.

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

**Options:**

| Option  | Description | 
| ------- | ----------------------------- |  
| project | Available default values php, symfony and drupal |
| files   | If this option is not set then the files added to git index will be scanned. This is useful when setting executing this tool on a git-hook pre-commit. |    

> Note: This project is a work-in-progress and need some love related to code clean up and testing coverage.
