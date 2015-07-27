PHPQA
=============================================
PHPQA Analyzer CLI tool

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Overview](#overview)
- [Available Analyzers](#available-analyzers)
- [Install](#install)
- [Usage](#usage)
- [Override configuration](#override-configuration)
- [Nice to have features](#nice-to-have-features)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Overview
This project aims to serve as a CLI tool to make easy the use of different PHP tools related to Quality Assurance and code analysis in PHP.

Every analyzer tool handles arguments and options using different formats, the goal of this project is to provide a single way to interact with those projects, you can also set options and arguments using a default configuration file when the project supports it.

> This project was originally developed as part of [Drupal Console](https://github.com/hechoendrupal/DrupalConsole) and based on the blog post [Write your git hooks in PHP and keep them under git control](http://carlosbuenosvinos.com/write-your-git-hooks-in-php-and-keep-them-under-git-control/).

## Available Analyzers

- [PHP Parallel Lint](https://github.com/JakubOnderka/PHP-Parallel-Lint)

  This tool check syntax of PHP files faster then serial check with fancier output.

  ![PHP-Parallel-Lint](http://i.imgur.com/F3BZsCP.png)

- [PHP Coding Standards Fixer](https://github.com/FriendsOfPHP/PHP-CS-Fixer)

  The PHP Coding Standards Fixer tool fixes most issues in your code when you want to follow the PHP coding standards as defined in the PSR-1 and PSR-2 documents.

  ![PHP-CS-Fixer](http://i.imgur.com/IU5pDhf.png)

- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)

  PHP_CodeSniffer is a set of two PHP scripts; the main `phpcs` script that tokenizes PHP, JavaScript and CSS files to detect violations of a defined coding standard, and a second `phpcbf` script to automatically correct coding standard violations.

  ![PHPCBF](http://i.imgur.com/0wiB36B.png)

  ![PHPCS](http://i.imgur.com/h8PLm4f.png)

- [PHPMD - PHP Mess Detector](http://phpmd.org/)

  It is a spin-off project of PHP Depend and aims to be a PHP equivalent of the well known Java tool PMD. PHPMD can be seen as an user friendly and easy to configure frontend for the raw metrics measured by PHP Depend.

  ![PHPMD](http://i.imgur.com/LhA4swF.png)

- [PHPLOC](https://github.com/sebastianbergmann/phploc)

  `phploc` is a tool for quickly measuring the size and analyzing the structure of a PHP project.

  ![PHPLOC](http://i.imgur.com/8Ewc07T.png)

- [PHPDCD - PHP Dead Code Detector](https://github.com/sebastianbergmann/phpdcd)

  `phpdcd` is a Dead Code Detector (DCD) for PHP code. It scans a PHP project for all declared functions and methods and reports those as being "dead code" that are not called at least once.

  ![PHPDCD](http://i.imgur.com/WPoDgcs.png)

- [PHPCPD - PHP Copy/Paste Detector](https://github.com/sebastianbergmann/phpcpd)

  `phpcpd` is a Copy/Paste Detector (CPD) for PHP code.

  ![PHPCPD](http://i.imgur.com/McvqmKJ.png)

- [PHPUnit](https://phpunit.de/)

  PHPUnit is a programmer-oriented testing framework for PHP. It is an instance of the xUnit architecture for unit testing frameworks.

  ![PHPUnit](http://i.imgur.com/80Q3pGm.png)

## Install

### Cloning the project
```
$ git clone git@github.com:jmolivas/phpqa.git
$ cd phpqa
# download dependencies
$ composer install
# make phpqa globally accessible creating a symlink
$ ln -s /path/to/phpqa/bin/phpqa /usr/local/bin/phpqa
```

## Usage

### Copy configuration file(s)
```
$ cd to/project/path
$ phpqa init --project=PROJECT --global --override
```
| Option   | Description |
| -------- | ----------------------------- |
| project  | Available values `php`, `symfony` and `drupal`. |
| global   | Copy configuration files to user home directory, instead of current working directory. |
| override | If this option is set, files are copied using override flag. |

**NOTES:**
- Option `global` does not accept a value must be set as `--global`.
- Option `override` does not accept a value must be set as `--override`.

### Analyze a project
```
$ cd to/project/path
$ phpqa analyze --project=PROJECT --files=FILES
$ phpqa analyze --project=PROJECT --git
```

| Option  | Description |
| ------- | ----------------------------- |
| project | Available values `php`, `symfony` and `drupal` |
| files   | Files or directories to analyze. |
| git     | If this option is set, all files added to git index will be scanned. This is useful when setting executing this tool on a pre-commit git-hook. |

**NOTES:**
- Option `git` does not accept a value must be set as `--git`.
- Options `files` and `git` can not used in combination.
- Option `project` could be omitted if a `phpqa.yml` or `phpqa.yml.dist` file is available at current working directory.

## Override configuration
This project was built to be fully customizable, you can enable/disable analyzers and modify arguments/options passed to analyzers by updating the `phpqa.yml` or `phpqa.yml.dist` file on your project root copied when running `init` command, or the files `~/.phpqa/php/config.yml`, `~/.phpqa/symfony/config.yml` or `~/.phpqa/drupal/config.yml` copied when running `init` command using `--global` option.

## Nice to have features
- Add command to create new project.
- Add more analyzers:
   - https://github.com/pdepend/pdepend
   - https://github.com/sensiolabs/security-checker
   - https://github.com/sensiolabs/insight
   - https://github.com/Halleck45/PhpMetrics
- Add analyzer via config and not as composer dependency.
- Detect if analyzer is already loaded on the local machine and use that instead of download.
- Add custom analyzers.
- Add SaaS analyzers via API.

> This project is a work-in-progress and needs some love related to code clean up, test coverage and documentation.
