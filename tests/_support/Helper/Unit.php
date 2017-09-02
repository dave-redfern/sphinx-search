<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Helper;

use PHPUnit\Framework\TestCase;
use Scorpio\SphinxSearch\Query\Builder;
use Scorpio\SphinxSearch\Query\Criteria;
use Scorpio\SphinxSearch\Query\Field;
use Scorpio\SphinxSearch\SearchIndex;
use Scorpio\SphinxSearch\SearchQuery;

require_once dirname(dirname(dirname(__DIR__))) . '/src/Resources/stubs/SphinxClient.php';

/**
 * Class Unit
 *
 * @package    Helper
 * @subpackage Helper\Unit
 */
class Unit extends TestCase
{

    /**
     * @param array $methods
     *
     * @return \SphinxClient
     */
    public function getSphinxClientMock(array $methods = [])
    {
        $methods = array_merge($this->_getBaseMethods(), $methods);

        /**
         * Note: most SphinxClient calls require a valid sphinx connection, so we simply stub it out.
         */
        $mock = $this->createMock(\SphinxClient::class);

        foreach ($methods as $method => $return) {
            $mock->expects($this->any())->method($method)->will($this->returnCallback($return));
        }

        return $mock;
    }

    /**
     * @return Builder
     */
    public function createQueryBuilder()
    {
        return new Builder(new SearchIndex('index', ['field1', 'field2']));
    }

    /**
     * @return Field
     */
    public function createNullQueryField()
    {
        return new Field($this->createQueryBuilder(), null);
    }

    /**
     * @return Field
     */
    public function createNamedQueryField()
    {
        return new Field($this->createQueryBuilder(), 'field1');
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
     * @return Criteria
     */
    public function createCriteria()
    {
        return new Criteria($this->createNamedQueryField());
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
     * @return array
     */
    protected function _getBaseMethods()
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
            'setRankingMode'      => function () {
                return true;
            },
            'addQuery'            => function () {
                static $i = 0;

                return ++$i;
            },
        ];
    }

    /**
     * @return \SphinxClient
     */
    public function createSphinxClientWithResults()
    {
        $mock = $this->getSphinxClientMock([
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
        ]);

        return $mock;
    }

    /**
     * @return \SphinxClient
     */
    public function createSphinxClientWithIncompleteResults()
    {
        $mock = $this->getSphinxClientMock([
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
        ]);

        return $mock;
    }

    /**
     * @return \SphinxClient
     */
    public function createSphinxClientWithNoResultsAndError()
    {
        $mock = $this->getSphinxClientMock([
            'runQueries'          => function () {
                return false;
            },
            'getLastError'        => function () {
                return 'no results';
            },
        ]);

        return $mock;
    }
}


class TestIndexName extends SearchIndex
{
    protected function initialise()
    {
        $this->indexName = 'testindexname';

        $this->availableFields  = [
            'first_name', 'last_name', 'full_name',
        ];
        $this->availableAttributes = [

        ];
    }
}


class TestIndexAddress extends SearchIndex
{
    protected function initialise()
    {
        $this->indexName = 'testindexaddress';

        $this->availableFields  = [
            'address', 'address_line_1', 'address_line_2', 'city', 'state',
        ];
        $this->availableAttributes = [
            'time_at_address', 'house_type',
        ];
    }
}
