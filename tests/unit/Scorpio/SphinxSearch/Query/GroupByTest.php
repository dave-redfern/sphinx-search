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
 * Class GroupByTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\GroupByTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class GroupByTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var GroupBy
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
        $this->setExpectedException('InvalidArgumentException');
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