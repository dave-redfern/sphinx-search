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
 * Class FilterRange
 *
 * Wrapper for a search filter for use with {@link SphinxClient::setFilterRange()}.
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
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\FilterRange
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class FilterRange extends AbstractFilter
{

    /**
     * @var integer
     */
    protected $min;

    /**
     * @var integer
     */
    protected $max;



    /**
     * Constructor.
     *
     * @param string  $name
     * @param integer $min
     * @param integer $max
     * @param boolean $exclude
     */
    public function __construct($name, $min, $max, $exclude = false)
    {
        $this->name    = $name;
        $this->exclude = $exclude;
        $this->setMin($min);
        $this->setMax($max);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $type = $this->exclude ? 'is not' : 'is';

        return sprintf('"%s" %s between %d and %d', $this->name, $type, $this->min, $this->max);
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
     * @return FilterRange
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->setFilterRange($this->name, $this->min, $this->max, $this->exclude);

        return $this;
    }


    /**
     * @param integer $max
     *
     * @return FilterRange
     */
    public function setMax($max)
    {
        $this->max = intval($max);

        return $this;
    }

    /**
     * @return integer
     */
    public function getMax()
    {
        return $this->max;
    }

    /**
     * @param integer $min
     *
     * @return FilterRange
     */
    public function setMin($min)
    {
        $this->min = intval($min);

        return $this;
    }

    /**
     * @return integer
     */
    public function getMin()
    {
        return $this->min;
    }
}
