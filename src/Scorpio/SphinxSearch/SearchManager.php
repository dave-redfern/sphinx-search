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
 * SearchManager will use a fresh Sphinx connection for every batch of queries run. This
 * is because the SphinxClient in the extension does not allow removing queries and cannot
 * be cloned.
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
 *      new SomeSearchIndex(), 'some keywords', SearchQuery::RANK_PROXIMITY_BM25, [
 *          new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [6])
 *      ],
 *      new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 * );
 *
 * $oSphinx = new SearchManager(new ServerSettings('localhost', '9312'));
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
 * $oSphinx = new SearchManager(new ServerSettings('localhost', '9312'));
 * $oSphinx->addQuery(
 *      new SearchQuery(
 *          new SomeSearchIndex(), 'some keywords', SearchQuery::RANK_PROXIMITY_BM25, [
 *              new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [6])
 *          ],
 *          new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 *      )
 * )->addQuery(
 *      new SearchQuery(
 *          new SomeSearchIndex(), 'some other keywords', SearchQuery::RANK_PROXIMITY_BM25, [
 *              new Filter\FilterAttribute(SomeSearchIndex::FILTER_ON_SOME_FILED, [4])
 *          ],
 *          new SortBy(Query\SortBy::SORT_BY_RELEVANCE, '@relevance DESC')
 *      )
 * )->addQuery(
 *      new SearchQuery(
 *          new SomeSearchIndex(), 'some other keywords', SearchQuery::RANK_PROXIMITY_BM25, [
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
     * The sphinx ServerSettings instance
     *
     * @var ServerSettings
     */
    private $settings;

    /**
     * The current Sphinx connection instance
     *
     * @var \SphinxClient
     */
    private $currentConnection;



    /**
     * Constructor.
     *
     * @param ServerSettings $settings
     */
    function __construct(ServerSettings $settings)
    {
        $this->settings = $settings;
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
        $this->currentConnection = null;
        $this->queries           = [];
    }



    /**
     * Adds a search query, note: queries cannot be removed once added
     *
     * @param SearchQuery $query
     *
     * @return $this
     */
    public function addQuery(SearchQuery $query)
    {
        $query->bindToSphinx($this->getCurrentConnection());
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

        if (false === $results = $this->getCurrentConnection()->runQueries()) {
            $this->throwException($this->getCurrentConnection()->getLastError());
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

        $this->currentConnection = null;

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
     * @return ServerSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Set the Sphinx ServerSettings instance
     *
     * This will reset the current connection and any assigned queries. Queries
     * must be manually added again after calling this method.
     *
     * @param ServerSettings $settings
     *
     * @return $this
     */
    public function setSettings(ServerSettings $settings)
    {
        $this->settings          = $settings;
        $this->currentConnection = null;
        $this->queries           = [];

        return $this;
    }

    /**
     * Returns an active Sphinx connection from the settings
     *
     * @return \SphinxClient
     */
    public function getCurrentConnection()
    {
        if ( !$this->currentConnection ) {
            $this->currentConnection = $this->settings->connect();
        }

        return $this->currentConnection;
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