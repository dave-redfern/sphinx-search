<?php

/*
 * This file is part of the Scorpio SphinxSearch Bundle.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Query;

use Scorpio\SphinxSearch\SearchIndex;

/**
 * Class Builder
 *
 * A simple query builder for composing Sphinx queries against the API. Allows
 * adding multiple field queries which will be converted into a single query
 * string.
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\Builder
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class Builder implements \IteratorAggregate, \Countable
{

    /**
     * @var SearchIndex
     */
    private $index;

    /**
     * @var Field[]
     */
    private $fields = [];



    /**
     * Constructor.
     *
     * @param SearchIndex $index
     */
    public function __construct(SearchIndex $index)
    {
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $return = [];

        foreach ($this->fields as $criteria) {
            $return[] = (string)$criteria;
        }

        return '(' . implode(') (', $return) . ')';
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->fields);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->fields);
    }

    /**
     * @param SearchIndex $index
     *
     * @return Builder
     */
    public static function find(SearchIndex $index)
    {
        return new Builder($index);
    }

    /**
     * Creates the field criteria and validates the supplied field
     *
     * @param string  $field
     * @param integer $within
     * @param boolean $not
     *
     * @return Field
     */
    protected function createField($field, $within, $not = false)
    {
        if (null !== $field && !$this->index->isValidField($field)) {
            $m = 'Specified fields "%s" are not valid for the index "%s"';
            $i = $this->index->getIndexName();

            throw new \InvalidArgumentException(sprintf($m, $field, $i));
        }

        $this->fields[] = $field = new Field($this, $field, $within, $not);

        return $field;
    }

    /**
     * Creates an inclusive Field for matching keywords
     *
     * If field is null, all fields will be matched on. If field is not valid for the
     * current index an exception will be raised.
     *
     * @param string  $field
     * @param integer $within
     *
     * @return Field
     */
    public function in($field = null, $within = null)
    {
        return $this->createField($field, $within);
    }

    /**
     * Creates an exclusive field for matching keywords
     *
     * If field is null, all fields will be matched on. If field is not valid for the
     * current index an exception will be raised.
     *
     * @param string  $field
     * @param integer $within
     *
     * @return Field
     */
    public function notIn($field = null, $within = null)
    {
        return $this->createField($field, $within, true);
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->__toString();
    }

    /**
     * @return SearchIndex
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return Field[]
     */
    public function getFields()
    {
        return $this->fields;
    }
}
