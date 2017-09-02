<?php

/*
 * This file is part of the Scorpio SphinxSearch Bundle.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Scorpio\SphinxSearch;


/**
 * Class SearchIndexInterface
 *
 * Search index interface.
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchIndex
 * @author     Dave Redfern <info@somnambulist.tech>
 */
interface SearchIndexInterface
{

    /**
     * Returns the index this search will use
     *
     * @return string
     */
    public function getIndexName();

    /**
     * Returns the result set class to use with searching
     *
     * @return string
     */
    public function getResultSetClass();

    /**
     * Returns the individual result class to use with searching
     *
     * @return string
     */
    public function getResultClass();

    /**
     * Returns the defined fields for the current index
     *
     * @return array
     */
    public function getAvailableFields();

    /**
     * Returns the available attributes for the current index
     *
     * @return array
     */
    public function getAvailableAttributes();

    /**
     * Returns true if field is part of the full text index
     *
     * @param string $field
     *
     * @return boolean
     */
    public function isValidField($field);

    /**
     * Returns true if the attribute is valid
     *
     * @param string $attr
     *
     * @return boolean
     */
    public function isValidAttribute($attr);
}
