<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Tests\Filter;

use Scorpio\SphinxSearch\Filter\FilterFloatRange;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class FilterFloatRangeTest
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\FilterFloatRangeTest
 */
class FilterFloatRangeTest extends BaseTest
{

    /**
     * @var FilterFloatRange
     */
    protected $filter;

    protected function _before()
    {
        $this->filter = new FilterFloatRange('foobar', 0, 0.5);
    }

    protected function _after()
    {
    }

    public function testToString()
    {
        $this->assertInternalType('string', (string)$this->filter);
        $this->assertEquals('"foobar" is between 0 and 0.5', $this->filter->toString());
        $this->filter->setExclude(true);
        $this->assertEquals('"foobar" is not between 0 and 0.5', $this->filter->toString());
    }

    public function testSetMax()
    {
        $this->filter->setMax(20.1);
        $this->assertEquals(20.1, $this->filter->getMax());
        $this->assertEquals('"foobar" is between 0 and 20.1', $this->filter->toString());
    }

    public function testSetMin()
    {
        $this->filter->setMin(20.1);
        $this->assertEquals(20.1, $this->filter->getMin());
        $this->assertEquals('"foobar" is between 20.1 and 0.5', $this->filter->toString());
    }

    public function testBindToSphinx()
    {
        $this->filter->bindToSphinx($this->I->getSphinxClientMock());
        $this->assertTrue(true);
    }
}
