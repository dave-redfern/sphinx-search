<?php

/*
 * This file is part of the Scorpio SphinxSearch Bundle.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Query;

/**
 * Class Field
 *
 * A search within a single field, or multiple fields at once, or not in the
 * field, or within the first X words of a single field.
 *
 * Note: within word count only works with single fields.
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\Field
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class Field
{

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var string
     */
    private $field;

    /**
     * @var integer
     */
    private $within;

    /**
     * @var boolean
     */
    private $not;

    /**
     * @var boolean
     */
    private $isMultiField = false;

    /**
     * @var Criteria
     */
    private $criteria;


    /**
     * Constructor.
     *
     * @param Builder $builder
     * @param string  $field  (required) the name of the field/fields
     * @param integer $within (optional) querying is limited to this many words in field
     * @param boolean $not    (optional) set to true to exclude these words
     */
    public function __construct(Builder $builder, $field, $within = null, $not = false)
    {
        $this->builder      = $builder;
        $this->field        = $field;
        $this->within       = (null !== $within ? intval($within) : null);
        $this->not          = $not;
        $this->isMultiField = (strpos($field, ',') !== false);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $not = ($this->not ? '!' : '');

        if (!$this->field) {
            return (string)$this->criteria;
        }

        if (!$this->isMultiField && $this->within) {
            return sprintf('@%s%s[%s] %s', $not, $this->field, $this->within, $this->criteria);
        }

        return sprintf('@%s(%s) %s', $not, $this->field, $this->criteria);
    }

    /**
     * @return Criteria
     */
    public function whereField()
    {
        $this->criteria = new Criteria($this);

        return $this->criteria;
    }

    /**
     * @return Builder
     */
    public function end()
    {
        return $this->builder;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return integer
     */
    public function getWithin()
    {
        return $this->within;
    }

    /**
     * @return boolean
     */
    public function isNot()
    {
        return $this->not;
    }

    /**
     * @return boolean
     */
    public function isMultiField()
    {
        return $this->isMultiField;
    }
}
