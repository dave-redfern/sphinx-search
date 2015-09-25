<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Query;

/**
 * Class Limits
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\Limits
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class Limits
{

    /**
     * @var integer
     */
    private $offset;

    /**
     * @var integer
     */
    private $limit;

    /**
     * @var integer
     */
    private $maxResults;



    /**
     * Constructor.
     *
     * @param integer $offset
     * @param integer $limit
     * @param integer $maxResults
     */
    public function __construct($offset = 0, $limit = 50, $maxResults = 5000)
    {
        $this->setOffset($offset);
        $this->setLimit($limit);
        $this->setMaxResults($maxResults);
    }

    /**
     * @param \SphinxClient $sphinx
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->setLimits($this->getOffset(), $this->getLimit(), $this->getMaxResults());
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     *
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->offset = intval($offset);

        return $this;
    }

    /**
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     *
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->limit = intval($limit);

        return $this;
    }

    /**
     * @return int
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param int $maxResults
     *
     * @return $this
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = intval($maxResults);

        return $this;
    }
}