<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [PHPQA](#phpqa)
  - [Based on:](#based-on)
  - [Install Stand Alone](#install-stand-alone)
    - [Clone project](#clone-project)
    - [Install dependencies](#install-dependencies)
    - [Create symbolink link](#create-symbolink-link)
  - [Include in your project](#include-in-your-project)
  - [How to run this project](#how-to-run-this-project)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

PHPQA
=============================================
PHPQA Analyzer CLI tool

## Based on:  
http://carlosbuenosvinos.com/write-your-git-hooks-in-php-and-keep-them-under-git-control/  
http://phpqatools.org/  

## Install Stand Alone

### Clone project

```
$ cd ~
$ git clone git@github.com:jmolivas/phpqa.git
```

### Install dependencies 

```
$ cd phpqa
$ composer install
```

### Create symbolink link

```
ln -s ~/phpqa/bin/phpqa.php /usr/local/bin/phpqa
```

## Include in your project
```
$ composer require jmolivas/phpqa
```

## How to run this project
```
# Make sure you run this command the first time  
$ phpqa init

# Run this command to test your project code
$ phpqa analyze --project=php --files=src/ 
```

> Note: This project is a work-in-progress need some love related to code clean up and testing coverage.
