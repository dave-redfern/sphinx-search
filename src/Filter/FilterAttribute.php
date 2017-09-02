<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Filter;

/**
 * Class FilterAttribute
 *
 * Wrapper for a search filter for use with {@link SphinxClient::setFilter()}.
 * Filters are defined attributes that have been setup on an index. Only
 * defined attributes on the index can be used as filters.
 *
 * This filter can only accept positive integer values.
 *
 * The optional exclude property is used to indicate if this filter should be
 * excluded from the results or not. For example: if the primary document id
 * is added as a separate attribute it is possible to exclude previous search
 * results from a new search.
 *
 * @package    Scorpio
 * @subpackage Scorpio\SphinxSearch\Filter
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class FilterAttribute extends AbstractFilter
{

    /**
     * @var array
     */
    protected $values = [];

    

    /**
     * Constructor
     *
     * @param string  $name
     * @param array   $values
     * @param boolean $exclude
     */
    function __construct($name, array $values = [], $exclude = false)
    {
        $this->name    = $name;
        $this->exclude = $exclude;
        $this->setValues($values);
    }

    /**
     * @return string
     */
    function __toString()
    {
        $type = $this->exclude ? 'excludes' : 'includes';

        return sprintf('"%s" %s "%s"', $this->name, $type, implode(', ', $this->values));
    }

    /**
     * @return string
     */
    function toString()
    {
        return $this->__toString();
    }

    /**
     * Binds the filter to a SphinxClient instance
     *
     * @param \SphinxClient $sphinx
     *
     * @return FilterAttribute
     */
    function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->setFilter($this->name, $this->values, $this->exclude);

        return $this;
    }


    /**
     * @param array $values
     *
     * @return FilterAttribute
     */
    public function setValues(array $values)
    {
        foreach ($values as $val) {
            $this->isValidFilterValue($val);
        }

        $this->values = $values;

        return $this;
    }

    /**
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Checks that the filter value is an integer
     *
     * @param integer $value
     *
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function isValidFilterValue($value)
    {
        if (!is_numeric($value) || !is_integer($value)) {
            throw new \InvalidArgumentException(
                sprintf('Filter value must be an integer value, "%s" is not valid', $value)
            );
        }

        return true;
    }
}
