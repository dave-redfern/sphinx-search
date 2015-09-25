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
 * Class AbstractFilterTest
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\AbstractFilterTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class AbstractFilterTest extends \Codeception\TestCase\Test
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var AbstractFilter
     */
    protected $filter;

    protected function _before()
    {
        $this->filter = Stub::make(AbstractFilter::class);
    }

    protected function _after()
    {
    }

    // tests
    public function testSetName()
    {
        $this->filter->setName('foobar');
        $this->assertEquals('foobar', $this->filter->getName());
    }

    public function testSetExclude()
    {
        $this->filter->setExclude(true);
        $this->assertTrue($this->filter->getExclude());
    }
}