<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Filter;

use Codeception\Util\Stub;

/**
 * Class FilterRangeTest
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\FilterRangeTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class FilterRangeTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var \Helper\Unit
     */
    protected $I;

    /**
     * @var FilterRange
     */
    protected $filter;

    protected function _inject(\Helper\Unit $I)
    {
        $this->I = $I;
    }

    protected function _before()
    {
        $this->filter = new FilterRange('foobar', 0, 5);
    }

    protected function _after()
    {
    }

    public function testToString()
    {
        $this->assertInternalType('string', (string)$this->filter);
        $this->assertEquals('"foobar" is between 0 and 5', $this->filter->toString());
        $this->filter->setExclude(true);
        $this->assertEquals('"foobar" is not between 0 and 5', $this->filter->toString());
    }

    public function testSetMax()
    {
        $this->filter->setMax(20);
        $this->assertEquals(20, $this->filter->getMax());
        $this->filter->setMax("20");
        $this->assertEquals(20, $this->filter->getMax());
        $this->assertEquals('"foobar" is between 0 and 20', $this->filter->toString());
    }

    public function testSetMin()
    {
        $this->filter->setMin(20);
        $this->assertEquals(20, $this->filter->getMin());
        $this->filter->setMin("20");
        $this->assertEquals(20, $this->filter->getMin());
        $this->assertEquals('"foobar" is between 20 and 5', $this->filter->toString());
    }

    public function testBindToSphinx()
    {
        $this->filter->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertTrue(true);
    }
}