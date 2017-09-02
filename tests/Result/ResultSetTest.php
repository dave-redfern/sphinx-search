<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Tests\Result;

use Scorpio\SphinxSearch\Result\ResultRecord;
use Scorpio\SphinxSearch\Result\ResultSet;
use Scorpio\SphinxSearch\SearchIndex;
use Scorpio\SphinxSearch\SearchQuery;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class ResultSetTest
 *
 * @package    Scorpio\SphinxSearch\Result
 * @subpackage Scorpio\SphinxSearch\Result\ResultSetTest
 */
class ResultSetTest extends BaseTest
{

    /**
     * @var ResultSet
     */
    protected $object;

    protected function _before()
    {
        $mock = $this->createMock(SearchQuery::class);
        $mock->expects($this->any())->method('getFilters')->willReturn([]);

        $this->object = new ResultSet($mock, $this->_getMatchesData());
    }

    protected function _getMatchesData()
    {
        return [
            'time'        => 1111,
            'words'       => [
                'word'    => ['docs' => 1, 'hits' => 1],
                'another' => ['docs' => 1, 'hits' => 1],
            ],
            'total_found' => 1111,
            'matches'     => [
                12345 => [
                    'attrs'  => ['attribute' => 2],
                    'weight' => 1,
                ],
                54321 => [
                    'attrs'  => ['attribute' => 234],
                    'weight' => 0,
                ],
            ]
        ];
    }

    protected function _after()
    {
    }

    // tests
    public function testHasResults()
    {
        $this->assertTrue($this->object->hasResults());
    }

    public function testGetActiveFilters()
    {
        $this->assertInternalType('array', $this->object->getActiveFilters());
    }

    public function testGetDocumentsIdsReturnsEmptyArray()
    {
        $mock = $this->createMock(SearchQuery::class);
        $mock->expects($this->any())->method('getFilters')->willReturn([]);

        $this->object = new ResultSet($mock, []);

        $this->assertSame([], $this->object->getDocumentIds());
    }

    public function testGetDocumentsIdsReturnsDocumentIds()
    {
        $this->assertSame([12345, 54321], $this->object->getDocumentIds());
    }

    public function testGetAttributeFromDocuments()
    {
        $ret = $this->object->getAttributeFromDocuments('attribute');

        $this->assertInternalType('array', $ret);
        $this->assertCount(2, $ret);
        $this->assertSame([12345,54321], array_keys($ret));
        $this->assertSame([2,234], array_values($ret));
    }

    public function testGetAttributeFromDocumentsFlattensToUniqueValues()
    {
        $ret = $this->object->getAttributeFromDocuments('attribute', true);

        $this->assertInternalType('array', $ret);
        $this->assertCount(2, $ret);
        $this->assertSame([2,234], $ret);
    }

    public function testGetAttributeFromDocumentsReturnsEmptyArrayForUnknownAttributes()
    {
        $ret = $this->object->getAttributeFromDocuments('bob');

        $this->assertInternalType('array', $ret);
        $this->assertCount(0, $ret);
    }

    public function testGetExecutionTime()
    {
        $this->assertEquals(1111, $this->object->getExecutionTime());
    }

    public function testGetQuery()
    {
        $this->assertInstanceOf(SearchQuery::class, $this->object->getQuery());
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf(\ArrayIterator::class, $this->object->getIterator());
    }

    public function testGetMatchStatistics()
    {
        $this->assertInternalType('array', $this->object->getMatchStatistics());
    }

    public function testCount()
    {
        $this->assertEquals(2, $this->object->count());
        $this->assertEquals(2, $this->object->getCount());
    }

    public function testGetTotalResults()
    {
        $this->assertEquals(1111, $this->object->getTotalResults());
    }

    public function testCanIterateResults()
    {
        foreach ( $this->object as $result ) {
            $this->assertInstanceOf(ResultRecord::class, $result);
        }
    }

    public function testCanSetResultClassInIndex()
    {
        $query = new SearchQuery(new TestMappedIndex());

        $results = new ResultSet($query, $this->_getMatchesData());

        foreach ( $results as $result ) {
            $this->assertInstanceOf(MyResultRecord::class, $result);
        }
    }

    public function testResultClassMustExtendResultRecord()
    {
        $query = new SearchQuery(new TestBadlyMappedIndex());

        $results = new ResultSet($query, $this->_getMatchesData());

        $this->expectException('RuntimeException');
        foreach ( $results as $result ) {
            // should fail.
        }
    }
}


class TestMappedIndex extends SearchIndex
{
    protected function initialise()
    {
        $this->resultClass = MyResultRecord::class;
    }
}

class TestBadlyMappedIndex extends SearchIndex
{
    protected function initialise()
    {
        $this->resultClass = MyBrokenResultRecord::class;
    }
}

class MyResultRecord extends ResultRecord
{

}

class MyBrokenResultRecord
{

}
