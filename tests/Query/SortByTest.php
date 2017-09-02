<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Tests\Query;

use Scorpio\SphinxSearch\Query\SortBy;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class SortByTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\SortByTest
 */
class SortByTest extends BaseTest
{

    /**
     * @var SortBy
     */
    protected $object;

    protected function _before()
    {
        $this->object = new SortBy();
    }

    protected function _after()
    {
    }

    // tests
    public function testSetMode()
    {
        $this->object->setMode(SortBy::SORT_BY_RELEVANCE);
        $this->assertEquals(SortBy::SORT_BY_RELEVANCE, $this->object->getMode());
    }

    public function testSetModeRaisesExceptionIfNotSupported()
    {
        $this->expectException('InvalidArgumentException');
        $this->object->setMode('bob');
    }

    public function testSetSortBy()
    {
        $this->object->setSortBy('@field');
        $this->assertEquals('@field', $this->object->getSortBy());
    }

    public function testBindToSphinx()
    {
        $this->object->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertTrue(true);
    }
}
