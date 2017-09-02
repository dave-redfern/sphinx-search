<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Result;

use Scorpio\SphinxSearch\SearchQuery;

/**
 * Class ResultSet
 *
 * Base sphinx result class providing common abstractions for handling search results.
 * This class can be used for looping - it will loop over any matches found and
 * optionally wrap the result in a {@link Scorpio\SphinxSearch\Result\ResultRecord} object
 * allowing easier access to the attributes and mapping the document Id.
 *
 * Both the search result set and the result can be extended to provide tailored
 * support for the specific index.
 *
 * @package    Scorpio\SphinxSearch\Result
 * @subpackage Scorpio\SphinxSearch\Result\ResultSet
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class ResultSet implements \Iterator, \Countable
{

    /**
     * @var SearchQuery
     */
    protected $query;

    /**
     * @var array
     */
    protected $results = [];



    /**
     * Constructor.
     *
     * @param SearchQuery $query
     * @param array       $results
     */
    function __construct(SearchQuery $query, $results)
    {
        $this->query = $query;
        if (is_array($results)) {
            $this->results = $results;
        }

        $this->initialise();
    }

    /**
     * Initialise the result set with custom logic, called last in constructor
     *
     * @return void
     * @abstract
     */
    protected function initialise()
    {

    }

    /**
     * Returns true if there were any matches from the search
     *
     * @return boolean
     */
    public function hasResults()
    {
        return array_key_exists('matches', $this->results);
    }

    /**
     * Returns the active filters applied in the search
     *
     * @return array
     */
    public function getActiveFilters()
    {
        return $this->query->getFilters();
    }

    /**
     * Returns just the document ids found in the search
     *
     * @return array
     */
    public function getDocumentIds()
    {
        if ($this->hasResults()) {
            return array_keys($this->results['matches']);
        } else {
            return [];
        }
    }

    /**
     * Returns an array indexed by documentId containing all the attributes matching $attribute
     *
     * Notes:
     * Only valid attribute names can be used. If the attribute is not found, an empty
     * array will be returned.
     *
     * If the flatten option is used, the returned array will contain only the unique
     * attribute ids with no reference to the parent document id e.g. if there are tags
     * mapped in the index, they will not be mapped to the owning document.
     *
     * @param string  $attribute
     * @param boolean $flatten (optional) flatten to single dimensional array
     *
     * @return array
     */
    public function getAttributeFromDocuments($attribute, $flatten = false)
    {
        $return = [];

        if ($this->count() > 0) {
            foreach ($this->results['matches'] as $documentId => $match) {
                if (array_key_exists($attribute, $match['attrs'])) {
                    if ($flatten) {
                        $return = array_unique(array_merge($return, (array)$match['attrs'][$attribute]));
                    } else {
                        $return[$documentId] = $match['attrs'][$attribute];
                    }
                }
            }
        }

        return $return;
    }

    /**
     * Returns the execution time of the request
     *
     * @return integer
     */
    public function getExecutionTime()
    {
        return $this->results['time'];
    }

    /**
     * Returns the query executed on Sphinx
     *
     * @return SearchQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Returns an array detailing matched words and the number of hits / docs matched
     *
     * Format:
     * array(
     *     'searchword' => array('docs' => ##, 'hits' => ##),
     * )
     *
     * @return array
     */
    public function getMatchStatistics()
    {
        return array_key_exists('words', $this->results) ? $this->results['words'] : [];
    }

    /**
     * Returns the number of results in this result set
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count();
    }

    /**
     * Returns the total possible matches from an unlimited search
     *
     * @return integer
     */
    public function getTotalResults()
    {
        return $this->results['total_found'];
    }



    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->results['matches']);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return $this->hasResults() ? count($this->results['matches']) : 0;
    }

    /**
     * Wraps the result in an object
     *
     * @param integer $key
     * @param array   $result
     *
     * @return ResultRecord
     */
    protected function getResult($key, $result)
    {
        $class = ResultRecord::class;

        if ( $this->query->getIndex() ) {
            $class = $this->query->getIndex()->getResultClass();
        }

        if ($class !== ResultRecord::class && !is_subclass_of($class, ResultRecord::class)) {
            throw new \RuntimeException(
                sprintf('ResultClass specified in SearchIndex must extend ResultsRecord, "%s" is not valid', $class)
            );
        }

        return new $class($key, $result);
    }

    /**
     * Resets array pointer to the start of the array
     *
     * @return void
     */
    public function rewind()
    {
        reset($this->results['matches']);
    }

    /**
     * Returns current array item
     *
     * @return mixed
     */
    public function current()
    {
        return $this->getResult($this->key(), current($this->results['matches']));
    }

    /**
     * Returns current array key
     *
     * @return mixed
     */
    public function key()
    {
        return key($this->results['matches']);
    }

    /**
     * Returns next item in array
     *
     * @return mixed
     */
    public function next()
    {
        $result = next($this->results['matches']);

        return $this->getResult($this->key(), $result);
    }

    /**
     * Returns true if current item is valid
     *
     * @return boolean
     */
    public function valid()
    {
        return current($this->results['matches']) !== false;
    }
}
