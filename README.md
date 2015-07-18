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

## Using Composer
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
