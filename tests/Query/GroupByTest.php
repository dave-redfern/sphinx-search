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

use Scorpio\SphinxSearch\Query\GroupBy;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class GroupByTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\GroupByTest
 */
class GroupByTest extends BaseTest
{

    /**
     * @var GroupBy
     */
    protected $object;

    protected function _before()
    {
        $this->object = new GroupBy();
    }

    protected function _after()
    {
    }

    // tests
    public function testSetAttr()
    {
        $this->object->setAttr('bob');
        $this->assertEquals('bob', $this->object->getAttr());
    }

    public function testSetFunc()
    {
        $this->object->setFunc(GroupBy::GROUP_BY_ATTRIBUTE_PAIR);
        $this->assertEquals(GroupBy::GROUP_BY_ATTRIBUTE_PAIR, $this->object->getFunc());
    }

    public function testSetFuncToUnknownValueRaisesException()
    {
        $this->expectException('InvalidArgumentException');
        $this->object->setFunc('bob');
    }

    public function testSetGroupBy()
    {
        $this->object->setGroupBy('@field desc');
        $this->assertEquals('@field desc', $this->object->getGroupBy());
    }

    public function testBindToSphinx()
    {
        $this->object->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertTrue(true);
    }
}
