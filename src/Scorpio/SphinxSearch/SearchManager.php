<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch;

use Scorpio\SphinxSearch\Result\ResultSet;

/**
 * Class SearchManager
 *
 * SearchManager provides a wrapper around the {@link http://ca.php.net/sphinx SphinxClient}
 * library. This implementation separates out the index, query and results into discrete
 * objects with their own interfaces. Through the use of discrete query objects, multiple
 * queries can be executed at once with an independent result set for each query.
 *
 * The query contains all filters, sorting and query text used for that result set. Each
 * query requires an index definition that must be implemented.
 *
 * NOTE: the SphinxClient that this class uses allows multiple queries to be executed
 * in one request BUT you cannot remove a query once it has been bound to Sphinx. If you
 * need to run many single queries you must fetch a new instance of SphinxSearch or
 * clone a default instance that has not had any queries bound to it.
 *
 * Example Usage:
 *
 * Run a single query both the long way and using the query() shortcut:
 * <code>
 * use Scorpio\SphinxSearch;
 * use Scorpio\SphinxSearch\Filter;
 * use Scorpio\SphinxSearch\Query;
 *
 * $oQuery = new SearchQuery(
 *      new SomeSearchIndex(), 'some keywords', SearchQuery::MATCH_ADVANCED, [
 *          new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [6])
 *      ],
 *      new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 * );
 *
 * $oSphinx = new SearchManager(new \SphinxClient('localhost', '9312'));
 * $oSphinx->addQuery($oQuery);
 * // $results contains all query result sets
 * $results = $oSphinx->search();
 *
 * // shortcut - returns result set just for this query
 * $oResults = $oSphinx->query($oQuery);
 *
 * // iterate results
 * foreach ( $oResults as $oResult ) {
 *      // do something with result instance
 * }
 * </code>
 *
 * Run several queries in one batch:
 * <code>
 * $oSphinx = new SearchManager(new \SphinxClient('localhost', '9312'));
 * $oSphinx->addQuery(
 *      new SearchQuery(
 *          new SomeSearchIndex(), 'some keywords', SearchQuery::MATCH_ADVANCED, [
 *              new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [6])
 *          ],
 *          new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 *      )
 * )->addQuery(
 *      new SearchQuery(
 *          new SomeSearchIndex(), 'some other keywords', SearchQuery::MATCH_ADVANCED, [
 *              new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [4])
 *          ],
 *          new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 *      )
 * )->addQuery(
 *      new SearchQuery(
 *          new SomeSearchIndex(), 'some other keywords', SearchQuery::MATCH_ADVANCED, [
 *              new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [112])
 *          ],
 *          new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 *      )
 * );
 *
 * $results = $oSphinx->search();
 * foreach ( $results[0] as $oResult ) {
 *      // do something with result
 * }
 * </code>
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchManager
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SearchManager implements \IteratorAggregate, \Countable
{

    /**
     * An array of query objects
     *
     * @var SearchQuery[]
     */
    private $queries = [];

    /**
     * The sphinx client instance
     *
     * @var \SphinxClient
     */
    private $sphinx;



    /**
     * Constructor.
     *
     * @param \SphinxClient $sphinx
     */
    function __construct(\SphinxClient $sphinx = null)
    {
        $this->sphinx = $sphinx;
    }

    /**
     * @return \ArrayIterator
     */
    function getIterator()
    {
        return new \ArrayIterator($this->queries);
    }

    /**
     * @return integer
     */
    function count()
    {
        return count($this->queries);
    }

    /**
     * Resets the class
     *
     * @return void
     */
    public function reset()
    {
        $this->sphinx  = null;
        $this->queries = [];
    }



    /**
     * Adds a search query, note: queries cannot be removed once added
     *
     * @param SearchQuery $query
     *
     * @return SearchManager
     */
    public function addQuery(SearchQuery $query)
    {
        $query->bindToSphinx($this->sphinx);
        $this->queries[$query->getId()] = $query;

        return $this;
    }

    /**
     * Returns the query matching result id $id, or null if not found
     *
     * @param integer $id
     *
     * @return SearchQuery|null
     */
    public function getQuery($id)
    {
        if (array_key_exists($id, $this->queries) && $this->queries[$id] instanceof SearchQuery) {
            return $this->queries[$id];
        }

        return null;
    }

    /**
     * Runs all bound queries against Sphinx returning an array of result sets
     *
     * @return ResultSet[]
     * @throws \RuntimeException
     */
    function search()
    {
        if (count($this->queries) < 1) {
            throw new \RuntimeException(sprintf('No queries have been set to run'));
        }

        if (false === $results = $this->sphinx->runQueries()) {
            $this->throwException($this->sphinx->getLastError());
        }

        /*
         * Always ensure these elements are in the result array
         */
        $require = ['matches' => [], 'time' => 0, 'total_found' => 0];
        $return  = [];

        foreach ($results as $id => $result) {
            if (null !== $oQuery = $this->getQuery($id)) {
                $class = $oQuery->getIndex()->getResultSetClass();

                foreach ($require as $key => $default) {
                    if (!array_key_exists($key, $result)) {
                        $result[$key] = $default;
                    }
                }

                $return[$id] = new $class($oQuery, $result);
            }
        }

        return $return;
    }

    /**
     * Runs a single query returning a single result set object
     *
     * The query is bound to Sphinx, stored internally and the Id assigned used
     * to return the result set from the full result sets of all queries currently
     * logged. This allows the method to be used even when other queries have
     * been bound previously.
     *
     * @param SearchQuery $query
     *
     * @return ResultSet
     */
    function query(SearchQuery $query)
    {
        $this->addQuery($query);

        $results = $this->search();

        return $results[$query->getId()];
    }


    /**
     * Returns the current Sphinx client instance if there is one
     *
     * @return \SphinxClient
     */
    public function getSphinx()
    {
        return $this->sphinx;
    }

    /**
     * @param \SphinxClient $sphinx
     *
     * @return $this
     */
    public function setSphinx(\SphinxClient $sphinx)
    {
        $this->sphinx = $sphinx;

        return $this;
    }

    /**
     * Set the server / port to use
     *
     * @param string  $server
     * @param integer $port
     * @param integer $maxQueryTime (optional) How long a query should be allowed to run for (ms)
     *
     * @return SearchManager
     */
    public function setServer($server = 'localhost', $port = 9312, $maxQueryTime = 5000)
    {
        if ( null === $sphinx = $this->getSphinx() ) {
            throw new \RuntimeException('A SphinxClient instance has not been assigned to the Manager yet');
        }

        $sphinx->setServer($server, $port);
        $sphinx->setMaxQueryTime($maxQueryTime);

        return $this;
    }

    /**
     * Throws an exception from the error string
     *
     * @param string $error
     *
     * @throws \RuntimeException
     */
    public function throwException($error)
    {
        throw new \RuntimeException(sprintf('Sphinx Error: %s', ucfirst($error)));
    }
}