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

use Scorpio\SphinxSearch\Filter\FilterAttribute;
use Scorpio\SphinxSearch\Query\Builder;
use Scorpio\SphinxSearch\Query\GroupBy;
use Scorpio\SphinxSearch\Query\Limits;
use Scorpio\SphinxSearch\Query\SortBy;
use Scorpio\SphinxSearch\SearchIndex;
use Scorpio\SphinxSearch\SearchQuery;

class TestIndex extends SearchIndex
{
    protected function initialise()
    {
        $this->indexName = 'testindex';
        $this->availableFields = [
            'name', 'gender', 'address',
        ];
        $this->availableAttributes = [
            'age', 'gender', 'bob',
        ];
    }
}

/**
 * Class SearchQueryTest
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchQueryTest
 */
class SearchQueryTest extends BaseTest
{

    /**
     * @var SearchQuery
     */
    protected $object;

    protected function _before()
    {
        $this->object = new SearchQuery(new SearchIndex());
    }

    protected function _after()
    {
    }

    // tests
    public function testBindToSphinx()
    {
        $this->object->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertEquals(1, $this->object->getId());
    }

    public function testBindToSphinxWithAllObjects()
    {
        $query = new SearchQuery(new TestIndex(), '', null, [
            new FilterAttribute('age'),
            new FilterAttribute('gender'),
        ], new SortBy(), new GroupBy('age'));

        $query->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertTrue(true);
    }

    public function testCanInstantiateWithFilters()
    {
        $query = new SearchQuery(new TestIndex(), '', null, [
            new FilterAttribute('age'),
            new FilterAttribute('gender'),
        ]);

        $this->assertCount(2, $query->getFilters());
    }

    public function testInstantiatingWithQueryContainingAtSetsMatchAdvanced()
    {
        $query = new SearchQuery(new TestIndex(), '@name keywords', null);

        $this->assertEquals(SearchQuery::RANK_PROXIMITY_BM25, $query->getRankingMode());
    }

    public function testToString()
    {
        $query = new SearchQuery(new TestIndex(), 'bob smith', null, [
            new FilterAttribute('age'),
            new FilterAttribute('gender'),
        ]);

        $this->assertEquals('"bob smith" using index "testindex" where "age" includes "" and "gender" includes ""', $query->toString());
    }

    public function testCanInstantiateWithGroupBy()
    {
        $query = new SearchQuery(new TestIndex(), '', null, [], null, new GroupBy('age'));

        $this->assertInstanceOf(GroupBy::class, $query->getGroupBy());
        $this->assertEquals('age', $query->getGroupBy()->getAttr());
    }

    public function testQueryInFieldsRequiresValidFields()
    {
        $this->expectException('InvalidArgumentException');
        $this->object->queryInFields('bob', 'smith');
    }

    public function testQueryInFields()
    {
        $this->object->setIndex(new TestIndex());
        $this->object->queryInFields('name', 'bob smith');
        $this->assertEquals('@name bob smith', $this->object->getQuery());

        $this->object->queryInFields('name,address,gender', 'bob smith');
        $this->assertEquals('@(name,address,gender) bob smith', $this->object->getQuery());
    }

    public function testAddGroupByRequiresValidFilterInIndex()
    {
        $this->expectException('InvalidArgumentException');
        $this->object->addGroupBy('bob', GroupBy::GROUP_BY_ATTRIBUTE);
    }

    public function testAddGroupBy()
    {
        $this->object->setIndex(new TestIndex());
        $this->object->addGroupBy('bob', GroupBy::GROUP_BY_ATTRIBUTE);
        $this->assertEquals('bob', $this->object->getGroupBy()->getAttr());
    }

    public function testAddSortBy()
    {
        $this->object->addSortBy(SortBy::SORT_BY_EXPRESSION, 'sort by');
        $this->assertEquals(SortBy::SORT_BY_EXPRESSION, $this->object->getSortBy()->getMode());
        $this->assertEquals('sort by', $this->object->getSortBy()->getSortBy());
    }

    public function testLimit()
    {
        $this->object->limit(20, 34, 123);
        $this->assertEquals(20, $this->object->getLimits()->getOffset());
        $this->assertEquals(34, $this->object->getLimits()->getLimit());
        $this->assertEquals(123, $this->object->getLimits()->getMaxResults());
    }

    public function testGetIndex()
    {
        $this->assertInstanceOf(SearchIndex::class, $this->object->getIndex());
    }

    public function testSetQueryBuilder()
    {
        $this->assertNull($this->object->getQueryBuilder());
        $this->object->setQueryBuilder(new Builder($this->object->getIndex()));
        $this->assertInstanceOf(Builder::class, $this->object->getQueryBuilder());
    }

    public function testSetQueryBuilderWithDifferentIndexRaisesException()
    {
        $this->expectException('InvalidArgumentException');
        $this->object->setQueryBuilder(new Builder(new SearchIndex()));
    }

    public function testSetQuery()
    {
        $this->object->setQuery('bob bob bob');
        $this->assertEquals('bob bob bob', $this->object->getQuery());
    }

    public function testCreateWildcardQueryString()
    {
        $this->assertEquals('*bob* *smith*', $this->object->createWildcardQueryString('bob smith')->getQuery());
    }

    public function testSetRankingMode()
    {
        $this->object->setRankingMode(SearchQuery::RANK_WORD_COUNT);
        $this->assertEquals(SearchQuery::RANK_WORD_COUNT, $this->object->getRankingMode());
    }

    public function testRankBy()
    {
        $this->object->rankBy(SearchQuery::RANK_WORD_COUNT);
        $this->assertEquals(SearchQuery::RANK_WORD_COUNT, $this->object->getRankingMode());
    }

    public function testSetGroupBy()
    {
        $this->object->setGroupBy(new GroupBy('attribute'));
        $this->assertInstanceOf(GroupBy::class, $this->object->getGroupBy());
        $this->assertEquals('attribute', $this->object->getGroupBy()->getAttr());
    }

    public function testSetSortBy()
    {
        $this->object->setSortBy(new SortBy(SortBy::SORT_BY_RELEVANCE, 'sort'));
        $this->assertInstanceOf(SortBy::class, $this->object->getSortBy());
        $this->assertEquals('sort', $this->object->getSortBy()->getSortBy());
    }

    public function testSetLimits()
    {
        $this->object->setLimits(new Limits(10, 20, 20));
        $this->assertInstanceOf(Limits::class, $this->object->getLimits());
        $this->assertEquals(10, $this->object->getLimits()->getOffset());
    }

    public function testGetFilters()
    {
        $this->assertInternalType('array', $this->object->getFilters());
    }

    public function testAddFilterOnlyAddsValidatedAttributes()
    {
        $this->object->addFilter(new FilterAttribute('attribute', [34]));
        $this->assertCount(0, $this->object->getFilters());
    }

    public function testAddFilter()
    {
        $this->object->setIndex(new TestIndex());
        $this->object->addFilter(new FilterAttribute('bob', [34]));
        $this->assertCount(1, $this->object->getFilters());
    }

    public function testGetFilter()
    {
        $this->object->setIndex(new TestIndex());
        $this->object->addFilter(new FilterAttribute('bob', [34]));
        $this->assertCount(1, $this->object->getFilters());
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('bob'));
    }

    public function testGetFilterForInvalidAttributeRaisesException()
    {
        $this->expectException('InvalidArgumentException');
        $this->object->getFilter('bob');
    }

    public function testGetFilterForUnsetFilterAutoCreatesIfInIndex()
    {
        $this->object->setIndex(new TestIndex());
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('bob'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('age'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('gender'));
    }

    public function testRemoveFilter()
    {
        $this->object->setIndex(new TestIndex());
        $this->object->addFilter(new FilterAttribute('bob', [34]));
        $this->assertCount(1, $this->object->getFilters());
        $this->object->removeFilter('bob');
        $this->assertCount(0, $this->object->getFilters());
    }

    public function testRemoveFilterByFilterObject()
    {
        $filter = new FilterAttribute('bob', [34]);
        $this->object->setIndex(new TestIndex());
        $this->object->addFilter($filter);
        $this->assertCount(1, $this->object->getFilters());
        $this->object->removeFilter($filter);
        $this->assertCount(0, $this->object->getFilters());
    }

    public function testClearFilters()
    {
        $this->object->setIndex(new TestIndex());
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('bob'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('age'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('gender'));
        $this->assertCount(3, $this->object->getFilters());
        $this->object->clearFilters();
        $this->assertCount(0, $this->object->getFilters());
    }

    public function testCount()
    {
        $this->assertEquals(0, $this->object->count());
        $this->object->setIndex(new TestIndex());
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('bob'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('age'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('gender'));
        $this->assertEquals(3, $this->object->count());
    }

    public function testIteratingSearchQueryIteratesFilters()
    {
        $this->assertEquals(0, $this->object->count());
        $this->object->setIndex(new TestIndex());
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('bob'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('age'));
        $this->assertInstanceOf(FilterAttribute::class, $this->object->getFilter('gender'));

        foreach ( $this->object as $filter ) {
            $this->assertInstanceOf(FilterAttribute::class, $filter);
        }
    }

    public function testCloningQueryDeepClonesFilters()
    {
        $query = new SearchQuery(new TestIndex(), '', null, [
            new FilterAttribute('age'),
            new FilterAttribute('gender'),
        ]);

        $query2 = clone $query;

        foreach ($query->getFilters() as $filter ) {
            $this->assertNotSame($filter, $query2->getFilter($filter->getName()));
        }
    }

    public function testCreateQueryBuilder()
    {
        $this->assertInstanceOf(Builder::class, $this->object->createQueryBuilder());

        $builder = $this->object->createQueryBuilder();
        $this->assertSame($builder, $this->object->getQueryBuilder());
        $this->assertSame($builder->getIndex(), $this->object->getIndex());
    }

    public function testBindToSphinxWillBindQueryBuilderIfNoQuery()
    {
        $builder = $this->object->createQueryBuilder();
        $builder->in()->whereField()->contains('bob alan derek');

        $this->assertNull($this->object->getQuery());

        $this->object->bindToSphinx($this->I->getSphinxClientMock());

        $this->assertEquals('(bob alan derek)', $this->object->getQuery());
    }

    public function testBindToSphinxWillIgnoreQueryBuilderIfQuerySet()
    {
        $builder = $this->object->createQueryBuilder();
        $builder->in()->whereField()->contains('bob alan derek');

        $this->object->setQuery('sue alice colby');

        $this->object->bindToSphinx($this->I->getSphinxClientMock());

        $this->assertNotEquals('(bob alan derek)', $this->object->getQuery());
    }
}
