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
 * Class FilterFloatRange
 *
 * Wrapper for a search filter for use with {@link SphinxClient::setFilterFloatRange()}.
 * Filters are defined attributes that have been setup on an index. Only
 * defined attributes on the index can be used as filters.
 *
 * This filter is for searching a range in a float attribute.
 *
 * The optional exclude property is used to indicate if this filter should be
 * excluded from the results or not. For example: if the primary document id
 * is added as a separate attribute it is possible to exclude previous search
 * results from a new search.
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\FilterFloatRange
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class FilterFloatRange extends AbstractFilter
{

    /**
     * @var float
     */
    protected $min;

    /**
     * @var float
     */
    protected $max;

    

    /**
     * Constructor.
     *
     * @param string  $name
     * @param float   $min
     * @param float   $max
     * @param boolean $exclude
     */
    public function __construct($name, $min, $max, $exclude = false)
    {
        $this->name    = $name;
        $this->exclude = $exclude;
        $this->min     = (float)$min;
        $this->max     = (float)$max;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $type = $this->exclude ? 'is not' : 'is';

        return sprintf('"%s" %s between %s and %s', $this->name, $type, $this->min, $this->max);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * Binds the filter to a SphinxClient instance
     *
     * @param \SphinxClient $sphinx
     *
     * @return FilterFloatRange
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->setFilterFloatRange($this->name, $this->min, $this->max, $this->exclude);

        return $this;
    }



    /**
     * @param float $max
     *
     * @return FilterFloatRange
     */
    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return float
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param float $min
     *
     * @return FilterFloatRange
     */
    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @return float
     */
    public function getMin()
    {
        return $this->min;
    }
}
