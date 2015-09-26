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

use Scorpio\SphinxSearch\Result\ResultRecord;
use Scorpio\SphinxSearch\Result\ResultSet;

/**
 * Class SearchIndex
 *
 * Defines a valid Sphinx index that can be used in searches. An index will
 * contain one or more fields that make up the full text index, and then a
 * set of attributes (filters) that can be used to filter the results by.
 * Filters can also be used to group and order results.
 *
 * The index object includes several helper methods for creating field based
 * query strings and for wrapping search keywords in wildcards.
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchIndex
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SearchIndex
{

    /**
     * @var string
     */
    protected $name;

    /**
     * Class name to use for the result set
     *
     * @var string
     */
    protected $resultSetClass = ResultSet::class;

    /**
     * Class name to use for an individual result
     *
     * @var string
     */
    protected $resultClass = ResultRecord::class;

    /**
     * An array of fields in the full text index
     *
     * @var array
     */
    protected $availableFields = [];

    /**
     * An array of attributes that can be used for filtering
     *
     * @var array
     */
    protected $availableFilters = [];

    
    
    /**
     * Constructor.
     */
    function __construct()
    {
        $this->initialise();
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    function toString()
    {
        return $this->__toString();
    }

    /**
     * Sets up the index definition
     *
     * @return void
     * @abstract
     */
    protected function initialise()
    {

    }



    /**
     * Returns the index this search will use
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the result set class to use with searching
     *
     * @return string
     */
    public function getResultSetClass()
    {
        return $this->resultSetClass;
    }

    /**
     * Returns the individual result class to use with searching
     *
     * @return string
     */
    public function getResultClass()
    {
        return $this->resultClass;
    }

    /**
     * Returns the defined fields for the current index
     *
     * @return array
     */
    public function getAvailableFields()
    {
        return $this->availableFields;
    }

    /**
     * Returns the available filters for the current index
     *
     * @return array
     */
    public function getAvailableFilters()
    {
        return $this->availableFilters;
    }

    /**
     * Returns true if field is part of the full text index
     *
     * @param string $field
     *
     * @return boolean
     */
    public function isValidField($field)
    {
        return in_array($field, $this->getAvailableFields());
    }

    /**
     * Returns true if the filter is valid
     *
     * @param string $filter
     *
     * @return boolean
     */
    public function isValidFilter($filter)
    {
        return in_array($filter, $this->getAvailableFilters());
    }

    /**
     * Creates a query string for searching within the specific field of the index
     *
     * @param string|array $field Comma separated list of fields or array
     * @param string       $keywords
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function createFieldQueryString($field, $keywords)
    {
        if (!is_array($field)) {
            if (strpos($field, ',') !== false) {
                $field = explode(',', str_replace(' ', '', $field));
            } else {
                $field = [$field];
            }
        }
        foreach ($field as $fd) {
            if (!$this->isValidField($fd)) {
                throw new \InvalidArgumentException(
                    sprintf('Field "%s" is not valid for the current search', $fd)
                );
            }
        }

        if (count($field) > 1) {
            $field = '(' . implode(',', $field) . ')';
        } else {
            $field = $field[0];
        }

        return sprintf('@%s %s', $field, $keywords);
    }
}