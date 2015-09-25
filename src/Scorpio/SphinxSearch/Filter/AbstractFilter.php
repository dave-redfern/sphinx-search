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
 * Class AbstractFilter
 *
 * Base filter class that is extended to support the filter. This class provides
 * the name and exclude properties of the interface definition.
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\AbstractFilter
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
abstract class AbstractFilter implements FilterInterface
{

    /**
     * @var string
     */
    protected $name;

    /**
     * @var boolean
     */
    protected $exclude = false;



    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return FilterRange
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getExclude()
    {
        return $this->exclude;
    }

    /**
     * @param boolean $exclude
     *
     * @return FilterRange
     */
    public function setExclude($exclude)
    {
        $this->exclude = (bool)$exclude;

        return $this;
    }
}