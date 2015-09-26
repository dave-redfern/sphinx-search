Scorpio SphinxSearch
====================

SphinxSearch library is a wrapper around the PHP Sphinx extension that allows for a cleaner object oriented interface.

Most aspects of the API are covered.

Requirements
------------

 * PHP 5.5+
 * PHP Sphinx extension
 * Codeception 2.1+ for unit tests
 * Sphinx Server

Note: while the Sphinx extension is required for composer, provided the client class has a SphinxClient
like interface, it can be substituted in the ServerSettings; however installation must be done manually.

Installation
------------

Install using composer, or checkout / pull the files from github.com.

 * composer install scorpio/sphinx-search

A stub file is included in Resources for IDE completion / constant reference.

Using
-----

You need a Sphinx server instance running. A sample config file is located in Resources/docs.

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

    $manager = new SearchManager(new ServerSettings('localhost', 9312));

To search via Sphinx, you need to create Index definitions. Each index must be created
in the Sphinx config file first. A specific instance for each index must then be
created that sets the available fields, attributes (filters) etc that this index
exposes. Once that is done, the index is passed to the query.

The SearchManager allows multiple queries to be run at once.

Note: once a query has been bound to SphinxClient it cannot be removed. To run
separate queries on the same Sphinx client you must create a new client instance. The
SearchManager will automatically destroy the SphinxClient after a search run.

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

To run code-coverage, you will need to temporarily disable the Sphinx extension as it
causes a seg-fault in ServerSettings to do with the extension check and SphinxClient
class. At the time of writing a solution had not been found.

Links
-----

 * http://sphinxsearch.com/docs/latest/index.html
 * http://ca.php.net/sphinx
 * https://github.com/gigablah/sphinxphp

A more modern SQL like Sphinx-QL exists, see the following for more details:

 * https://github.com/FoolCode/SphinxQL-Query-Builder