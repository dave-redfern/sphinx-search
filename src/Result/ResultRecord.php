<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Result;

/**
 * Class ResultRecord
 *
 * Wrapper around a Sphinx element match. This class encapsulates the
 * array of data returned by Sphinx. It can be used by the result set
 * to provide a declared interface to the properties in the data array.
 *
 * @package    Scorpio\SphinxSearch\Result
 * @subpackage Scorpio\SphinxSearch\Result\ResultRecord
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class ResultRecord
{

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var array
     */
    protected $data = [];



    /**
     * Constructor.
     *
     * @param integer $id
     * @param array   $data
     */
    function __construct($id, $data)
    {
        $this->id   = $id;
        $this->data = $data;
    }

    /**
     * Returns the document id (primary key) of the result
     *
     * @return integer
     */
    function getId()
    {
        return $this->id;
    }

    /**
     * Returns the fulltext weighting for this result
     *
     * @return integer
     */
    function getWeight()
    {
        return $this->data['weight'];
    }

    /**
     * Returns all attributes in the attrs array key
     *
     * @return array
     */
    function getAttributes()
    {
        return $this->data['attrs'];
    }

    /**
     * Returns the attribute named $attribute, if not found returns $default
     *
     * @param string     $attribute
     * @param mixed|null $default
     *
     * @return mixed
     */
    function getAttribute($attribute, $default = null)
    {
        if (array_key_exists($attribute, $this->data['attrs'])) {
            return $this->data['attrs'][$attribute];
        } else {
            return $default;
        }
    }
}
