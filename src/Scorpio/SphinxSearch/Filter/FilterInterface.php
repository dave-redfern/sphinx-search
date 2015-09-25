<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Filter;

/**
 * Interface FilterInterface
 *
 * Interface for a search filter to be used with {@link Scorpio\SphinxSearch\SearchQuery}
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\FilterInterface
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
interface FilterInterface
{

    /**
     * Returns a description of this filter as a string
     *
     * @return string
     */
    public function toString();

    /**
     * Binds the filter to a SphinxClient instance
     *
     * @param \SphinxClient $sphinx
     *
     * @return FilterInterface
     */
    public function bindToSphinx(\SphinxClient $sphinx);

    /**
     * Returns the attribute this filter will act on
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the attribute the filter will act on
     *
     * @param string $name
     *
     * @return FilterInterface
     */
    public function setName($name);

    /**
     * Returns true if filter will exclude results
     *
     * @return boolean
     */
    public function getExclude();

    /**
     * Set if the filter should exclude results
     *
     * @param boolean $exclude
     *
     * @return FilterInterface
     */
    public function setExclude($exclude);
}