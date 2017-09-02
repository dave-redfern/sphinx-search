<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

if ( !extension_loaded('sphinx') ) {

    define('SEARCHD_OK', 1);
    define('SEARCHD_ERROR', 2);
    define('SEARCHD_RETRY', 3);
    define('SEARCHD_WARNING', 4);
    define('SPH_MATCH_ALL', 5);
    define('SPH_MATCH_ANY', 6);
    define('SPH_MATCH_PHRASE', 7);
    define('SPH_MATCH_BOOLEAN', 8);
    define('SPH_MATCH_EXTENDED', 9);
    define('SPH_MATCH_FULLSCAN', 10);
    define('SPH_MATCH_EXTENDED2', 11);
    define('SPH_RANK_PROXIMITY_BM25', 12);
    define('SPH_RANK_BM25', 13);
    define('SPH_RANK_NONE', 14);
    define('SPH_RANK_WORDCOUNT', 15);
    define('SPH_SORT_RELEVANCE', 16);
    define('SPH_SORT_ATTR_DESC', 17);
    define('SPH_SORT_ATTR_ASC', 18);
    define('SPH_SORT_TIME_SEGMENTS', 19);
    define('SPH_SORT_EXTENDED', 20);
    define('SPH_SORT_EXPR', 21);
    define('SPH_FILTER_VALUES', 22);
    define('SPH_FILTER_RANGE', 23);
    define('SPH_FILTER_FLOATRANGE', 24);
    define('SPH_ATTR_INTEGER', 25);
    define('SPH_ATTR_TIMESTAMP', 26);
    define('SPH_ATTR_ORDINAL', 27);
    define('SPH_ATTR_BOOL', 28);
    define('SPH_ATTR_FLOAT', 29);
    define('SPH_ATTR_MULTI', 30);
    define('SPH_GROUPBY_DAY', 31);
    define('SPH_GROUPBY_WEEK', 32);
    define('SPH_GROUPBY_MONTH', 33);
    define('SPH_GROUPBY_YEAR', 34);
    define('SPH_GROUPBY_ATTR', 35);
    define('SPH_GROUPBY_ATTRPAIR', 36);

    /**
     * Class SphinxClient
     *
     * This is for IDE auto-completion as by default the Sphinx extension is not available.
     *
     * @link http://ca3.php.net/manual/en/book.sphinx.php
     * @link http://ca3.php.net/manual/en/sphinx.constants.php
     */
    class SphinxClient
    {

        /**
         * @param        $query
         * @param string $index
         * @param string $comment
         *
         * @return int
         */
        function addQuery($query, $index = '*', $comment = '')
        {
        }

        function buildExcerpts(array $docs, $index, $words, array $opts)
        {
        }

        function buildKeywords($query, $index, $hits)
        {
        }

        function close()
        {
        }

        function escapeString($string)
        {
        }

        /**
         * @return string
         */
        function getLastError()
        {
        }

        function getLastWarning()
        {
        }

        function open()
        {
        }

        function query($query, $index = '*', $comment = '')
        {
        }

        function resetFilters()
        {
        }

        function resetGroupBy()
        {
        }

        /**
         * @return array
         */
        function runQueries()
        {
        }

        function setArrayResult($array_results = false)
        {
        }

        function setConnectTimeout($timeout)
        {
        }

        function setFieldWeights(array $weights)
        {
        }

        function setFilter($attribute, array $values, $exclude = false)
        {
        }

        function setFilterFloatRange($attribute, $min, $max, $exclude = false)
        {
        }

        function setFilterRange($attribute, $min, $max, $exclude = false)
        {
        }

        function setGeoAnchor($attrlat, $attrlong, $latitude, $longitude)
        {
        }

        function setGroupBy($attribute, $func, $groupsort = '@group desc')
        {
        }

        function setGroupDistinct($attribute)
        {
        }

        function setIDRange($min, $max)
        {
        }

        function setIndexWeights(array $weights)
        {
        }

        function setLimits($offset, $limit, $max_matches = 0, $cut_off = 0)
        {
        }

        function setMatchMode($mode)
        {
        }

        function setMaxQueryTime($qtime)
        {
        }

        function setOverride($attribute, $type, array $values)
        {
        }

        function setRankingMode($ranker)
        {
        }

        function setRetries($count, $delay = 0)
        {
        }

        function setSelect($clause)
        {
        }

        function setServer($server, $port)
        {
        }

        function setSortMode($mode, $sortBy)
        {
        }

        function status()
        {
        }

        function updateAttributes($index, array $attributes, array $values, $mva = false)
        {
        }
    }
}
