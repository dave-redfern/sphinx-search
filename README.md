Scorpio SphinxSearch Library Change Log
=======================================

SphinxSearch library is a wrapper around the PHP Sphinx extension that allows for a cleaner object oriented interface.

Most aspects of the API are covered.

Requirements
------------

 * PHP 5.5+
 * PHP Sphinx extension
 * Codeception 2.1+ for unit tests

Installation
------------

Install using composer, or checkout / pull the files from github.com.

 * composer install scorpio/sphinx-search

A stub file is included in Resources for IDE completion / constant reference.

Using
-----

You need a Sphinx server instance running.

OSX:

 * brew install sphinx php56-sphinx
 * configure sphinx instance

CentOS:

 * setup IUS repository for your version of CentOS
 * yum install php56-sphinx sphinx
 * configure sphinx server and data

Setup code:

    require_once 'path/to/vendor/autoload.php';

    use Scorpio\SphinxSearch\SearchManager;

    $sphinx  = new \SphinxClient('localhost', '9312');
    $manager = new SearchManager($sphinx);

To search via Sphinx, you need to create Index definition. Each index must be created
in the Sphinx config file first. A specific instance for each index must then be
created that sets the available fields, attributes (filters) etc that this index
exposes. Once that is done, the index is passed to the query.

The SearchManager allows multiple queries to be run at once.

Note: once a query has been bound to SphinxClient it cannot be removed. To run
separate queries on the same Sphinx client you must first clone the client instance.

Note: when setting max query time, this value is in milliseconds. If set below e.g.: 100 ms
you may return only a small, inconsistent set of results. Ensure that the time you use is
enough to cover your searching e.g.: 5000 ms is usually enough. 0 (zero) will set no limit.

Running Unit Tests
------------------

Codeception is used as the test framework:

 * curl -s http://getcomposer.org/installer | php
 * git clone git@github.com:scorpioframework/sphinx-search.git
 * php composer.phar install
 * bin/codecept run unit

To run code-coverage, you will need to temporarily disable the Sphinx extension as it causes a seg-fault.

Links
-----

 * http://ca.php.net/sphinx