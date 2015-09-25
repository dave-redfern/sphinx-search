<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Query;

use Codeception\Util\Stub;

/**
 * Class SortByTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\SortByTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SortByTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var SortBy
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
        $this->setExpectedException('InvalidArgumentException');
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