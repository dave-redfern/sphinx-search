<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch;

use Codeception\Util\Stub;
use Scorpio\SphinxSearch\Result\ResultSet;

/**
 * Class SearchManagerTest
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchManagerTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SearchManagerTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var SearchManager
     */
    protected $object;

    /**
     * @var \Helper\Unit
     */
    protected $I;

    protected function _inject(\Helper\Unit $I)
    {
        $this->I = $I;
    }

    protected function _before()
    {
        $this->object = new SearchManager();
        $this->object->setSphinx(
            Stub::make(\SphinxClient::class, [
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
                'setMatchMode'        => function () {
                    return true;
                },
                'addQuery'            => function () {
                    static $i;
                    return ++$i;
                },
                'runQueries' => function () {
                    return [
                        1 => [
                            'matches' => [
                                12 => [
                                    'weight' => 34,
                                    'attrs' => ['time_at_address' => 23, 'house_type' => 3],
                                ],
                                15432 => [
                                    'weight' => 32,
                                    'attrs' => ['time_at_address' => 1, 'house_type' => 1],
                                ],
                                1532 => [
                                    'weight' => 12,
                                    'attrs' => ['time_at_address' => 4, 'house_type' => 6],
                                ],
                            ],
                            'time' => 123,
                            'total_found' => 23,
                        ],
                        2 => [
                            'matches' => [
                                12 => [
                                    'weight' => 34,
                                    'attrs' => ['time_at_address' => 23, 'house_type' => 3],
                                ],
                                15432 => [
                                    'weight' => 32,
                                    'attrs' => ['time_at_address' => 1, 'house_type' => 1],
                                ],
                                1532 => [
                                    'weight' => 12,
                                    'attrs' => ['time_at_address' => 4, 'house_type' => 6],
                                ],
                            ],
                            'time' => 123,
                            'total_found' => 23,
                        ],
                    ];
                },
            ])
        );
    }

    protected function _after()
    {
    }

    // tests
    public function testGetSphinx()
    {
        $this->assertInstanceOf(\SphinxClient::class, $this->object->getSphinx());
    }

    public function testSetServer()
    {
        $this->object->setServer('123.26.21.23', '9898', '50');
        $this->assertTrue(true);
    }

    public function testSetServerWithNoSphinxRaisesException()
    {
        $this->object->reset();
        $this->setExpectedException('RuntimeException');
        $this->object->setServer('123.26.21.23', '9898', '50');
    }

    public function testThrowException()
    {
        $this->setExpectedException('RuntimeException');
        $this->object->throwException('bob');
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->object->count());
    }

    public function testAddQuery()
    {
        $this->object->addQuery($this->I->createNameSearchQuery()->queryInFields('full_name', 'bob smith'));
        $this->assertEquals(1, $this->object->count());
    }

    public function testGetQuery()
    {
        $query = $this->I->createAddressSearchQuery();
        $this->object->addQuery($query);

        $this->assertSame($query, $this->object->getQuery(1));
    }

    public function testSearchWithoutQueriesRaisesException()
    {
        $this->setExpectedException('RuntimeException');
        $this->object->search();
    }

    public function testQuery()
    {
        $results = $this->object->query($this->I->createAddressSearchQuery());

        $this->assertInstanceOf(ResultSet::class, $results);
    }

    public function testSearch()
    {
        $this->object->addQuery($this->I->createAddressSearchQuery());
        $results = $this->object->search();

        $this->assertInternalType('array', $results);
        $this->assertCount(1, $results);
    }

    public function testSearchWithMultipleQueries()
    {
        $this->object->addQuery($this->I->createAddressSearchQuery());
        $this->object->addQuery($this->I->createNameSearchQuery());
        $results = $this->object->search();

        $this->assertInternalType('array', $results);
        $this->assertCount(2, $results);
    }

    public function testReset()
    {
        $this->object->reset();

        $this->assertNull($this->object->getSphinx());
        $this->assertEquals(0, $this->object->count());
    }

    public function testSearchWillCreateMissingArrayKeys()
    {
        $sphinx = $this->I->createSphinxClientWithIncompleteResults();

        $this->object->setSphinx($sphinx);
        $this->object->addQuery($this->I->createAddressSearchQuery());
        $this->object->addQuery($this->I->createNameSearchQuery());

        $results = $this->object->search();

        $this->assertEquals(0, $results[2]->getTotalResults());
        $this->assertEquals(0, $results[2]->getExecutionTime());
        $this->assertEquals(0, $results[2]->getCount());
    }

    public function testSearchWithFailedResultsRaisesException()
    {
        $sphinx = $this->I->createSphinxClientWithNoResultsAndError();

        $this->object->setSphinx($sphinx);
        $this->object->addQuery($this->I->createAddressSearchQuery());
        $this->object->addQuery($this->I->createNameSearchQuery());

        $this->setExpectedException('RuntimeException');
        $this->object->search();
    }

    public function testGetIterator()
    {
        $this->object->addQuery($this->I->createAddressSearchQuery());
        $this->object->addQuery($this->I->createNameSearchQuery());

        foreach ( $this->object->getIterator() as $query ) {
            $this->assertInstanceOf(SearchQuery::class, $query);
        }

    }
}




