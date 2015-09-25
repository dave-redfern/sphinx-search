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
 * Class SortBy
 *
 * Sort by controls the ordering of the returned results. It requires the use of
 * a specific mode that is a Sphinx constant, wrapped to a class constant. The
 * default mode and most useful is relevance order, however any other sorting
 * can be used by setting the appropriate mode and sortBy field criteria.
 *
 * Note: that only fields in the index can be used for sorting. Relevancy is a
 * calculated field.
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\SortBy
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SortBy
{

    const SORT_ADVANCED          = SPH_SORT_EXTENDED;
    const SORT_BY_ATTRIBUTE_ASC  = SPH_SORT_ATTR_ASC;
    const SORT_BY_ATTRIBUTE_DESC = SPH_SORT_ATTR_DESC;
    const SORT_BY_EXPRESSION     = SPH_SORT_EXPR;
    const SORT_BY_RELEVANCE      = SPH_SORT_RELEVANCE;

    /**
     * @var integer
     */
    private $mode;

    const SORT_FIELD_ID           = '@id';
    const SORT_FIELD_WEIGHT       = '@weight';
    const SORT_FIELD_RANK         = '@rank';
    const SORT_FIELD_RELEVANCE    = '@relevance';
    const SORT_FIELD_RANDOM       = '@random';

    /**
     * @var string
     */
    private $sortBy;



    /**
     * Constructor.
     *
     * @param integer $mode
     * @param string  $sortBy
     */
    public function __construct($mode = self::SORT_BY_RELEVANCE, $sortBy = '')
    {
        $this->mode   = $mode;
        $this->sortBy = $sortBy;
    }

    /**
     * @param \SphinxClient $sphinx
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->setSortMode($this->getMode(), $this->getSortBy());
    }

    /**
     * @return integer
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param integer $mode
     *
     * @return $this
     */
    public function setMode($mode)
    {
        $valid = [
            self::SORT_ADVANCED,
            self::SORT_BY_ATTRIBUTE_ASC,
            self::SORT_BY_ATTRIBUTE_DESC,
            self::SORT_BY_EXPRESSION,
            self::SORT_BY_RELEVANCE,
        ];
        if ( !in_array($mode, $valid, true) ) {
            throw new \InvalidArgumentException(sprintf('Invalid sort by mode "%s"', $mode));
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * @param string $sortBy
     *
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }
}