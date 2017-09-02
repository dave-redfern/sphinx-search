<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch;

use Scorpio\SphinxSearch\Filter\FilterInterface;
use Scorpio\SphinxSearch\Filter\FilterAttribute;
use Scorpio\SphinxSearch\Query\Builder;
use Scorpio\SphinxSearch\Query\GroupBy;
use Scorpio\SphinxSearch\Query\Limits;
use Scorpio\SphinxSearch\Query\SortBy;

/**
 * Class SearchQuery
 *
 * Wrapper representing a Sphinx search query including all filters, sorting
 * and limits. This allows multiple searches to be defined and all the criteria
 * kept so that they can be linked back to the search results.
 *
 * Every query instance must be mapped to a valid {@link Scorpio\SphinxSearch\SearchIndex} definition
 * that defines the index properties, fields etc. This ensures that the results can
 * be correctly formatted and transformed and allows for error checking when building
 * field searches or adding filters to the query.
 *
 * Multiple filters can be applied to the search query so long as the filter implements
 * the {@link Scorpio\SphinxSearch\Filter\FilterInterface}. There are currently 3 filter types:
 *
 * 1. {@link Scorpio\SphinxSearch\Filter\FilterAttribute} - Standard attribute filter
 * 2. {@link Scorpio\SphinxSearch\Filter\FilterFloatRange} - Float range filter
 * 3. {@link Scorpio\SphinxSearch\Filter\FilterRange} - Integer range filter
 *
 * Example usage:
 * <code>
 * use Scorpio\SphinxSearch;
 * use Scorpio\SphinxSearch\Filter;
 *
 * $oQuery = new SearchQuery(new SomeIndex());
 * $oQuery->setRankingMode(SearchQuery::RANK_PROXIMITY_BM25);
 * $oQuery->setQuery(
 *      $oQuery->getIndex()->createFieldQueryString(
 *          array(
 *              SomeIndex::FIELD_NAME, SomeIndex::FIELD_NAME_2,
 *          ), $oQuery->getIndex()->createWildcardQueryString($keywords)
 *      )
 * );
 * $oQuery->addFilter(new Filter\FilterAttribute(SomeIndex::FILTER_ATTRIBUTE_1, array(1, 2, 3)));
 * $oQuery->addFilter(new Filter\FilterAttribute(SomeIndex::FILTER_ATTRIBUTE_2, array(SomeIndex::SOME_VALUE)));
 * $oQuery->addFilter(new Filter\FilterAttribute(SomeIndex::FILTER_ATTRIBUTE_3, array(101)));
 * $oQuery->setLimit(100);
 *
 * $oSphinx = new SearchManager(new ServerSettings('localhost', '9312'));
 * $oSphinx->addQuery($oQuery);
 * $results = $oSphinx->search();
 * </code>
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchQuery
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class SearchQuery implements \IteratorAggregate, \Countable
{

    const RANK_NONE           = SPH_RANK_NONE;           // ranker just assigns every document weight to 1
    const RANK_WORD_COUNT     = SPH_RANK_WORDCOUNT;      // counts keyword matches, multiplies by user field weights
    const RANK_PROXIMITY_BM25 = SPH_RANK_PROXIMITY_BM25; // the default SphinxQL ranker
    const RANK_BM25           = SPH_RANK_BM25 ;          // ranker sums user weights of the matched fields and BM25

    /**
     * @var integer
     */
    private $id;

    /**
     * @var SearchIndex
     */
    private $index;

    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * @var Builder
     */
    private $builder;

    /**
     * @var string
     */
    private $query;

    /**
     * @var integer
     */
    private $rankingMode = self::RANK_PROXIMITY_BM25;

    /**
     * @var GroupBy
     */
    private $groupBy;

    /**
     * @var SortBy
     */
    private $sortBy;

    /**
     * @var Limits
     */
    private $limits;



    /**
     * Constructor
     *
     * @param SearchIndex       $index
     * @param string            $query    Valid Sphinx query e.g. "keywords" or @<field> "keywords"
     * @param integer           $rankMode SPH_RANK_* constant, default SPH_RANK_PROXIMIT_BM25
     * @param FilterInterface[] $filters  An array of filter objects
     * @param SortBy            $sortBy   The sorting to apply
     * @param GroupBy           $groupBy  How to group the query
     * @param Limits            $limits   Query limits
     */
    public function __construct(
        SearchIndex $index, $query = null, $rankMode = null, array $filters = [],
        SortBy $sortBy = null, GroupBy $groupBy = null, Limits $limits = null
    )
    {
        $this->id = 0;
        $this->setIndex($index)->setQuery($query)->setRankingMode($rankMode);
        $this->setRankingMode((null !== $rankMode ? $rankMode : self::RANK_PROXIMITY_BM25));

        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }

        if ( null !== $groupBy ) {
            $this->setGroupBy($groupBy);
        }

        $this->setSortBy((null !== $sortBy ? $sortBy : new SortBy()));
        $this->setLimits((null !== $limits ? $limits : new Limits()));
    }

    /**
     * Allow deep cloning of filters
     */
    public function __clone()
    {
        foreach ($this->filters as $attribute => $filter) {
            $this->filters[$attribute] = clone $filter;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $filters = [];

        /* @var FilterInterface $filter */
        foreach ($this->filters as $filter) {
            $filters[] = $filter->toString();
        }

        $filters = (count($filters) > 0 ? 'where ' : '') . implode(' and ', $filters);

        return sprintf('"%s" using index "%s" %s', $this->getQuery(), $this->getIndex(), $filters);
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->filters);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->filters);
    }



    /**
     * Adds this query to the SphinxClient $sphinx
     *
     * Adding the query resets the passed SphinxClient so that no existing
     * filters or group bys or sort order etc are inherited by this query.
     * The query details are then injected into Sphinx, and the resulting
     * id passed back to this query allowing the results to be mapped to
     * the query.
     *
     * @param \SphinxClient $sphinx
     *
     * @return $this
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->resetFilters();
        $sphinx->resetGroupBy();

        $sphinx->setRankingMode($this->rankingMode);

        if ($this->groupBy instanceof GroupBy) {
            $this->groupBy->bindToSphinx($sphinx);
        }

        $this->sortBy->bindToSphinx($sphinx);
        $this->limits->bindToSphinx($sphinx);

        /* @var FilterInterface $filter */
        foreach ($this->filters as $filter) {
            $filter->bindToSphinx($sphinx);
        }

        if ( $this->builder instanceof Builder && !$this->query ) {
            $this->query = $this->builder->getQuery();
        }

        $this->id = $sphinx->addQuery($this->query, $this->index->getIndexName());

        return $this;
    }



    /**
     * Creates a search query for specific fields in the index using the keywords.
     *
     * Fields is either a comma separated list (field,field2,field3) or an array. Keywords
     * should be the standard string.
     *
     * @param string|array $fields
     * @param string       $keywords
     *
     * @return $this
     */
    public function queryInFields($fields, $keywords)
    {
        $this->setQuery($this->getIndex()->createFieldQueryString($fields, $keywords));

        return $this;
    }

    /**
     * Creates a new query builder linking it to the current SearchIndex
     *
     * Note: there can only be one query builder per search query
     *
     * @return Builder
     */
    public function createQueryBuilder()
    {
        $this->builder = Builder::find($this->index);

        return $this->builder;
    }

    /**
     * Sets the grouping to be used on the results
     *
     * $attribute is the name of a valid attribute on the current index.
     * $function is either a {@link self::GROUP_BY_*} constant or Sphinx SPH_GROUPBY_* constant
     * $groupBy is the actual grouping query, defaulting to @group desc. Supports @id, @weight,
     * @relevance, @random etc.
     *
     * @param string  $attribute
     * @param integer $function
     * @param string  $groupBy
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addGroupBy($attribute, $function, $groupBy = '@group desc')
    {
        if (!$this->index->isValidAttribute($attribute)) {
            $i = $this->index->getIndexName();
            $a = $attribute;
            $m = 'Group by filter attribute "%s" is not valid for index "%s';

            throw new \InvalidArgumentException(sprintf($m, $a, $i));
        }

        $this->setGroupBy(new GroupBy($attribute, $function, $groupBy));

        return $this;
    }

    /**
     * Sets the sorting criteria, overwriting any existing setting
     *
     * $mode is any valid Sphinx SPH_SORT_* constant value.
     * $sortBy is a valid sort string for the sort mode e.g. @relevance DESC for SPH_SORT_EXTENDED/2
     *
     * @param integer $mode
     * @param string  $sortBy
     *
     * @return $this
     */
    public function addSortBy($mode, $sortBy)
    {
        $this->setSortBy(new SortBy($mode, $sortBy));

        return $this;
    }

    /**
     * Alias of setRankingMode
     *
     * @param integer $mode
     *
     * @return $this
     */
    public function rankBy($mode)
    {
        $this->setRankingMode($mode);

        return $this;
    }

    /**
     * Set a new Limits instance
     *
     * @param integer $offset
     * @param integer $limit
     * @param integer $maxResults
     *
     * @return $this
     */
    public function limit($offset, $limit, $maxResults = 5000)
    {
        $this->setLimits(new Limits($offset, $limit, $maxResults));

        return $this;
    }



    /**
     * Returns the result index id that this query matches
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the index being used for searching
     *
     * @return SearchIndex
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param SearchIndex $index
     *
     * @return $this
     */
    public function setIndex(SearchIndex $index)
    {
        $this->index = $index;

        return $this;
    }

    /**
     * @return Builder
     */
    public function getQueryBuilder()
    {
        return $this->builder;
    }

    /**
     * Set a pre-built Builder instance
     *
     * Raises exception if the Builder index is not the same as the query index.
     *
     * @param Builder $builder
     *
     * @return $this
     */
    public function setQueryBuilder(Builder $builder)
    {
        if ( $this->index !== $builder->getIndex() ) {
            throw new \InvalidArgumentException(
                sprintf('Index mismatch in builder "%s" vs query "%s"',
                    $builder->getIndex()->getIndexName(), $this->index->getIndexName()
                )
            );
        }

        $this->builder = $builder;

        return $this;
    }

    /**
     * Returns the current query string
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Creates a wildcard search query for all keywords in the supplied string
     *
     * @param string $keywords
     *
     * @return $this
     */
    public function createWildcardQueryString($keywords)
    {
        $this->query = '*' . str_replace(' ', '* *', $keywords) . '*';

        return $this;
    }

    /**
     * Set a valid Sphinx search query string which could be just keywords
     *
     * @param string $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return integer
     */
    public function getRankingMode()
    {
        return $this->rankingMode;
    }

    /**
     * @param integer $mode
     *
     * @return $this
     */
    public function setRankingMode($mode)
    {
        if (null !== $mode && is_integer($mode)) {
            $this->rankingMode = $mode;
        }

        return $this;
    }

    /**
     * @return GroupBy
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * Set the group by mode, requires a GroupBy instance
     *
     * @param GroupBy $groupBy
     *
     * @return $this
     */
    public function setGroupBy(GroupBy $groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @return SortBy
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set the sort mode, requires a SortBy instance
     *
     * @param SortBy $sortBy
     *
     * @return $this
     */
    public function setSortBy(SortBy $sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * @return Limits
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Set the query limits, requires a Limits instance
     *
     * @param Limits $limits
     *
     * @return $this
     */
    public function setLimits(Limits $limits)
    {
        $this->limits = $limits;

        return $this;
    }



    /**
     * Returns all filters in the query
     *
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Returns the filter for $attribute
     *
     * If the filter is not currently in the bound filters, a new instance
     * of {@link Scorpio\SphinxSearch\Filter\FilterAttribute} will be created for
     * the specified attribute and that object returned. If the attribute is not
     * valid for the current index, an exception will be raised.
     *
     * @param string $attribute
     *
     * @return FilterInterface
     * @throws \InvalidArgumentException
     */
    public function getFilter($attribute)
    {
        if (array_key_exists($attribute, $this->filters)) {
            return $this->filters[$attribute];
        } else {
            if ($this->index->isValidAttribute($attribute)) {
                return $this->filters[$attribute] = new FilterAttribute($attribute);
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Filter "%s" is not valid for index "%s"', $attribute, $this->index->getIndexName())
                );
            }
        }
    }

    /**
     * Add a search filter for a specific attribute
     *
     * @param FilterInterface $filter
     *
     * @return $this
     */
    public function addFilter(FilterInterface $filter)
    {
        if ($this->index->isValidAttribute($filter->getName())) {
            $this->filters[$filter->getName()] = $filter;
        }

        return $this;
    }

    /**
     * @param string|FilterInterface $filter
     *
     * @return $this
     */
    public function removeFilter($filter)
    {
        if ($filter instanceof FilterInterface) {
            $filter = $filter->getName();
        }

        if (array_key_exists($filter, $this->filters)) {
            unset($this->filters[$filter]);
        }

        return $this;
    }

    /**
     * Removes all filters from this query
     *
     * @return $this
     */
    public function clearFilters()
    {
        $this->filters = [];

        return $this;
    }
}
