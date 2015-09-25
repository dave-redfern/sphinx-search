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

use Scorpio\SphinxSearch\Filter\FilterInterface;
use Scorpio\SphinxSearch\Filter\FilterAttribute;
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
 * $oQuery->setMatchMode(SearchQuery::MATCH_ADVANCED);
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
 * $oSphinx = new SearchManager(new \SphinxClient('localhost', '9312'));
 * $oSphinx->addQuery($oQuery);
 * $results = $oSphinx->search();
 * </code>
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchQuery
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SearchQuery
{

    const MATCH_ALL      = SPH_MATCH_ALL;
    const MATCH_ANY      = SPH_MATCH_ANY;
    const MATCH_ADVANCED = SPH_MATCH_EXTENDED2;
    const MATCH_PHRASE   = SPH_MATCH_PHRASE;

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
     * @var string
     */
    private $query = '';

    /**
     * @var integer
     */
    private $matchMode = self::MATCH_ADVANCED;

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
     * @param string            $query     Valid Sphinx query e.g. "keywords" or @<field> "keywords"
     * @param integer           $matchMode SPH_MATCH_* constant, default extended2
     * @param FilterInterface[] $filters   An array of filter objects
     * @param SortBy            $sortBy    The sorting to apply
     * @param GroupBy           $groupBy   How to group the query
     * @param Limits            $limits    Query limits
     */
    public function __construct(
        SearchIndex $index, $query = '', $matchMode = null, array $filters = [],
        SortBy $sortBy = null, GroupBy $groupBy = null, Limits $limits = null
    )
    {
        $this->id = 0;
        $this->setIndex($index)->setQuery($query)->setMatchMode($matchMode);

        if (false !== strpos($query, '@')) {
            $this->setMatchMode(self::MATCH_ADVANCED);
        }

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
     * @return SearchQuery
     */
    public function bindToSphinx(\SphinxClient $sphinx)
    {
        $sphinx->resetFilters();
        $sphinx->resetGroupBy();

        $sphinx->setMatchMode($this->matchMode);

        if ($this->groupBy instanceof GroupBy) {
            $this->groupBy->bindToSphinx($sphinx);
        }

        $this->sortBy->bindToSphinx($sphinx);
        $this->limits->bindToSphinx($sphinx);

        /* @var FilterInterface $filter */
        foreach ($this->filters as $filter) {
            $filter->bindToSphinx($sphinx);
        }

        $this->id = $sphinx->addQuery($this->query, $this->index->getName());

        return $this;
    }

    /**
     * Creates a search query for specific fields in the index using the keywords.
     *
     * Note: this method automatically sets the matchmode to ADVANCED (SPH_MATCH_EXTENDED2).
     * Fields is either a comma separated list (field,field2,field3) or an array. Keywords
     * should be the standard string.
     *
     * @param string|array $fields
     * @param string       $keywords
     *
     * @return SearchQuery
     */
    public function queryInFields($fields, $keywords)
    {
        $this->setMatchMode(self::MATCH_ADVANCED);
        $this->setQuery($this->getIndex()->createFieldQueryString($fields, $keywords));

        return $this;
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
     * @return SearchQuery
     * @throws \InvalidArgumentException
     */
    public function addGroupBy($attribute, $function, $groupBy = '@group desc')
    {
        if (!$this->index->isValidFilter($attribute)) {
            throw new \InvalidArgumentException(
                sprintf('Group by filter attribute "%s" is not valid for index "%s', $attribute, $this->index->getName())
            );
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
     * @return SearchQuery
     */
    public function addSortBy($mode, $sortBy)
    {
        $this->setSortBy(new SortBy($mode, $sortBy));

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
     * @return SearchQuery
     */
    public function setIndex(SearchIndex $index)
    {
        $this->index = $index;

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
     * @param string $query
     *
     * @return SearchQuery
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return integer
     */
    public function getMatchMode()
    {
        return $this->matchMode;
    }

    /**
     * @param integer $mode
     *
     * @return SearchQuery
     */
    public function setMatchMode($mode)
    {
        if (null !== $mode && is_integer($mode)) {
            $this->matchMode = $mode;
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
     * @return SearchQuery
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
     * @return SearchQuery
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
            if ($this->index->isValidFilter($attribute)) {
                return $this->filters[$attribute] = new FilterAttribute($attribute);
            } else {
                throw new \InvalidArgumentException(
                    sprintf('Filter "%s" is not valid for index "%s"', $attribute, $this->index->getName())
                );
            }
        }
    }

    /**
     * Add a search filter for a specific attribute
     *
     * @param FilterInterface $filter
     *
     * @return SearchQuery
     */
    public function addFilter(FilterInterface $filter)
    {
        if ($this->index->isValidFilter($filter->getName())) {
            $this->filters[$filter->getName()] = $filter;
        }

        return $this;
    }

    /**
     * @param string|FilterInterface $filter
     *
     * @return SearchQuery
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
     * @return SearchQuery
     */
    public function clearFilters()
    {
        $this->filters = [];

        return $this;
    }
}