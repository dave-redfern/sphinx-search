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
 * Class FilterAttributeTest
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\FilterAttributeTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class FilterAttributeTest extends \Codeception\TestCase\Test
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
     * @var FilterAttribute
     */
    protected $filter;

    protected function _inject(\Helper\Unit $I)
    {
        $this->I = $I;
    }

    protected function _before()
    {
        $this->filter = new FilterAttribute('foobar');
    }

    protected function _after()
    {
    }

    public function testToString()
    {
        $this->assertInternalType('string', (string)$this->filter);
        $this->assertEquals('"foobar" includes ""', $this->filter->toString());
        $this->filter->setExclude(true);
        $this->assertEquals('"foobar" excludes ""', $this->filter->toString());
    }

    public function testSetValues()
    {
        $this->filter->setValues([1,2,3]);
        $this->assertEquals([1,2,3], $this->filter->getValues());
        $this->assertEquals('"foobar" includes "1, 2, 3"', $this->filter->toString());
    }

    public function testIsValidFilterValue()
    {
        $this->assertTrue($this->filter->isValidFilterValue(3));
    }

    public function testIsValidFilterValueErrorsForNumbersAsStrings()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->assertTrue($this->filter->isValidFilterValue('3'));
    }

    public function testIsValidFilterValueErrorsForStrings()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->filter->isValidFilterValue('bob');
    }

    public function testBindToSphinx()
    {
        $this->filter->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertTrue(true);
    }
}