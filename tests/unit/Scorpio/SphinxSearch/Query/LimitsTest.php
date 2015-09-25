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
 * Class LimitsTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\LimitsTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class LimitsTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var Limits
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
        $this->object = new Limits();
    }

    protected function _after()
    {
    }

    // tests
    public function testSetOffset()
    {
        $this->object->setOffset(30);
        $this->assertEquals(30, $this->object->getOffset());
        $this->object->setOffset("30");
        $this->assertEquals(30, $this->object->getOffset());
        $this->object->setOffset("bob");
        $this->assertEquals(0, $this->object->getOffset());
    }
    
    public function testSetLimit()
    {
        $this->object->setLimit(30);
        $this->assertEquals(30, $this->object->getLimit());
        $this->object->setLimit("30");
        $this->assertEquals(30, $this->object->getLimit());
        $this->object->setLimit("bob");
        $this->assertEquals(0, $this->object->getLimit());
    }
    
    public function testSetMaxResults()
    {
        $this->object->setMaxResults(30);
        $this->assertEquals(30, $this->object->getMaxResults());
        $this->object->setMaxResults("30");
        $this->assertEquals(30, $this->object->getMaxResults());
        $this->object->setMaxResults("bob");
        $this->assertEquals(0, $this->object->getMaxResults());
    }

    public function testBindToSphinx()
    {
        $this->object->bindToSphinx($this->I->getSphinxClientMock());
    }
}