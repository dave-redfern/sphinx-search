<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helper;

use Codeception\Util\Stub;
use Scorpio\SphinxSearch\SearchIndex;
use Scorpio\SphinxSearch\SearchQuery;

require_once dirname(dirname(dirname(__DIR__))) . '/src/Scorpio/SphinxSearch/Resources/stubs/SphinxClient.php';

/**
 * Class Unit
 *
 * @package    Helper
 * @subpackage Helper\Unit
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class Unit extends \Codeception\Module
{

    /**
     * @return \SphinxClient
     */
    public function getSphinxClientMock()
    {
        /**
         * Note: most SphinxClient calls require a valid sphinx connection, so we simply stub it out.
         */
        return Stub::make(\SphinxClient::class, $this->_getBaseMethods());
    }

    /**
     * @return SearchQuery
     */
    public function createNameSearchQuery()
    {
        return new SearchQuery(
            new TestIndexName(), '', SearchQuery::RANK_PROXIMITY_BM25, []
        );
    }

    /**
     * @return SearchQuery
     */
    public function createAddressSearchQuery()
    {
        return new SearchQuery(
            new TestIndexAddress(), '', SearchQuery::RANK_PROXIMITY_BM25, []
        );
    }

    /**
     * @param string $copyMethod
     *
     * @return array
     */
    protected function _getBaseMethods($copyMethod = 'getSphinxClientMock')
    {
        return [
            'setServer'           => function () {
                return true;
            },
            'setMaxQueryTime'     => function () {
                return true;
            },
            'setFilter'           => function () {
                return true;
            },
            'setFilterFloatRange' => function () {
                return true;
            },
            'setFilterRange'      => function () {
                return true;
            },
            'setLimits'           => function () {
                return true;
            },
            'setSortMode'         => function () {
                return true;
            },
            'setGroupBy'          => function () {
                return true;
            },
            'resetFilters'        => function () {
                return true;
            },
            'resetGroupBy'        => function () {
                return true;
            },
            'setRankingMode'        => function () {
                return true;
            },
            'addQuery'            => function () {
                static $i;

                return ++$i;
            },
        ];
    }

    /**
     * @return \SphinxClient
     */
    public function createSphinxClientWithResults()
    {
        return Stub::make(\SphinxClient::class, array_merge($this->_getBaseMethods('createSphinxClientWithResults'), [
            'runQueries'          => function () {
                return [
                    1 => [
                        'matches'     => [
                            12    => [
                                'weight' => 34,
                                'attrs'  => ['time_at_address' => 23, 'house_type' => 3],
                            ],
                            15432 => [
                                'weight' => 32,
                                'attrs'  => ['time_at_address' => 1, 'house_type' => 1],
                            ],
                            1532  => [
                                'weight' => 12,
                                'attrs'  => ['time_at_address' => 4, 'house_type' => 6],
                            ],
                        ],
                        'time'        => 123,
                        'total_found' => 23,
                    ],
                    2 => [
                        'matches'     => [
                            12    => [
                                'weight' => 34,
                                'attrs'  => ['time_at_address' => 23, 'house_type' => 3],
                            ],
                            15432 => [
                                'weight' => 32,
                                'attrs'  => ['time_at_address' => 1, 'house_type' => 1],
                            ],
                            1532  => [
                                'weight' => 12,
                                'attrs'  => ['time_at_address' => 4, 'house_type' => 6],
                            ],
                        ],
                        'time'        => 123,
                        'total_found' => 23,
                    ],
                ];
            },
        ]));
    }

    /**
     * @return \SphinxClient
     */
    public function createSphinxClientWithIncompleteResults()
    {
        return Stub::make(\SphinxClient::class, array_merge($this->_getBaseMethods('createSphinxClientWithIncompleteResults'), [
            'runQueries'          => function () {
                return [
                    1 => [
                        'matches'     => [
                            12    => [
                                'weight' => 34,
                                'attrs'  => ['time_at_address' => 23, 'house_type' => 3],
                            ],
                            15432 => [
                                'weight' => 32,
                                'attrs'  => ['time_at_address' => 1, 'house_type' => 1],
                            ],
                            1532  => [
                                'weight' => 12,
                                'attrs'  => ['time_at_address' => 4, 'house_type' => 6],
                            ],
                        ],
                        'time'        => 123,
                        'total_found' => 23,
                    ],
                    2 => [],
                ];
            },
        ]));
    }

    /**
     * @return \SphinxClient
     */
    public function createSphinxClientWithNoResultsAndError()
    {
        return Stub::make(\SphinxClient::class, array_merge($this->_getBaseMethods('createSphinxClientWithNoResultsAndError'), [
            'runQueries'          => function () {
                return false;
            },
            'getLastError'        => function () {
                return 'no results';
            },
        ]));
    }
}


class TestIndexName extends SearchIndex
{
    protected function initialise()
    {
        $this->name = 'testindexname';

        $this->availableFields  = [
            'first_name', 'last_name', 'full_name',
        ];
        $this->availableFilters = [

        ];
    }

    public function enableWildcards()
    {
        $this->supportsWildcard    = true;
        $this->useWildcardKeywords = true;
    }
}


class TestIndexAddress extends SearchIndex
{
    protected function initialise()
    {
        $this->name = 'testindexaddress';

        $this->availableFields  = [
            'address', 'address_line_1', 'address_line_2', 'city', 'state',
        ];
        $this->availableFilters = [
            'time_at_address', 'house_type',
        ];
    }

    public function enableWildcards()
    {
        $this->supportsWildcard    = true;
        $this->useWildcardKeywords = true;
    }
}