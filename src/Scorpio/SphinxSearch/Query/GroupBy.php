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
 * Class GroupBy
 *
 * Represents the group by for a Sphinx query. Grouping is performed by specifying how
 * grouping should occur. This is by the function call, which is one of the defined
 * Sphinx functions that have been wrapped to named constants.
 *
 * Note: you can only group by fields defined in the index.
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\GroupBy
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class GroupBy
{

    const GROUP_BY_ATTRIBUTE      = SPH_GROUPBY_ATTR;
    const GROUP_BY_ATTRIBUTE_PAIR = SPH_GROUPBY_ATTRPAIR;
    const GROUP_BY_DAY            = SPH_GROUPBY_DAY;
    const GROUP_BY_MONTH          = SPH_GROUPBY_MONTH;
    const GROUP_BY_WEEK           = SPH_GROUPBY_WEEK;
    const GROUP_BY_YEAR           = SPH_GROUPBY_YEAR;

    /**
     * @var string
     */
    private $attr;

    /**
     * @var integer
     */
    private $func;

    /**
     * @var string
     */
    private $groupBy;



    /**
     * Constructor.
     *
     * @param string  $attr
     * @param integer $func
     * @param string  $groupBy
     */
    public function __construct($attr = '', $func = self::GROUP_BY_ATTRIBUTE, $groupBy = '@group DESC')
    {
        $this->attr    = $attr;
        $this->func    = $func;
        $this->groupBy = $groupBy;
    }

    /**
     * @param \SphinxClient $sphinx
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->setGroupBy($this->getAttr(), $this->getFunc(), $this->getGroupBy());
    }

    /**
     * @return string
     */
    public function getAttr()
    {
        return $this->attr;
    }

    /**
     * @param string $attr
     *
     * @return $this
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;

        return $this;
    }

    /**
     * @return int
     */
    public function getFunc()
    {
        return $this->func;
    }

    /**
     * @param int $func
     *
     * @return $this
     */
    public function setFunc($func)
    {
        $valid = [
            self::GROUP_BY_ATTRIBUTE, self::GROUP_BY_ATTRIBUTE_PAIR, self::GROUP_BY_DAY, self::GROUP_BY_MONTH,
            self::GROUP_BY_WEEK, self::GROUP_BY_YEAR,
        ];
        if (!in_array($func, $valid, true)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid group by function "%s"', $func)
            );
        }

        $this->func = $func;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * @param string $groupBy
     *
     * @return $this
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }
}