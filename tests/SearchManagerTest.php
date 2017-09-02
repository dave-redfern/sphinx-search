<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Tests;

use Scorpio\SphinxSearch\Result\ResultSet;
use Scorpio\SphinxSearch\SearchManager;
use Scorpio\SphinxSearch\SearchQuery;
use Scorpio\SphinxSearch\ServerSettings;

/**
 * Class SearchManagerTest
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchManagerTest
 */
class SearchManagerTest extends BaseTest
{

    /**
     * @var SearchManager
     */
    protected $object;

    protected function _before()
    {
        $helper = $this->I;

        $settings = $this->createMock(ServerSettings::class);
        $settings->expects($this->any())->method('connect')
            ->willReturnCallback(function () use ($helper) {
                return $helper->createSphinxClientWithResults();
            });

        $this->object = new SearchManager($settings);
    }

    protected function _after()
    {
    }

    // tests
    public function testGetSettings()
    {
        $this->object->setSettings(new ServerSettings('localhost', 3232, 320, 'bob'));
        $this->assertInstanceOf(ServerSettings::class, $this->object->getSettings());
    }

    public function testGetCurrentConnection()
    {
        $conn = $this->object->getCurrentConnection();

        $this->assertTrue(is_object($conn));
    }

    public function testThrowException()
    {
        $this->expectException('RuntimeException');
        $this->object->throwException('bob');
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->object->count());
    }

    /**
     * @group current
     */
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
        $this->expectException('RuntimeException');
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

        $this->assertEquals(0, $this->object->count());
    }

    public function testSearchWillCreateMissingArrayKeys()
    {
        $helper   = $this->I;

        $settings = $this->createMock(ServerSettings::class);
        $settings->expects($this->any())->method('connect')
            ->willReturnCallback(function () use ($helper) {
                return $helper->createSphinxClientWithIncompleteResults();
            });

        $this->object = new SearchManager($settings);

        $this->object->addQuery($this->I->createAddressSearchQuery());
        $this->object->addQuery($this->I->createNameSearchQuery());

        $results = $this->object->search();

        $this->assertEquals(0, $results[2]->getTotalResults());
        $this->assertEquals(0, $results[2]->getExecutionTime());
        $this->assertEquals(0, $results[2]->getCount());
    }

    public function testSearchWithFailedResultsRaisesException()
    {
        $helper = $this->I;

        $settings = $this->createMock(ServerSettings::class);
        $settings->expects($this->any())->method('connect')
            ->willReturnCallback(function () use ($helper) {
                return $helper->createSphinxClientWithNoResultsAndError();
            });

        $this->object = new SearchManager($settings);

        $this->object->addQuery($this->I->createAddressSearchQuery());
        $this->object->addQuery($this->I->createNameSearchQuery());

        $this->expectException('RuntimeException');
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




