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

use Scorpio\SphinxSearch\Filter\AbstractFilter;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class AbstractFilterTest
 *
 * @package    Scorpio\SphinxSearch\Filter
 * @subpackage Scorpio\SphinxSearch\Filter\AbstractFilterTest
 */
class AbstractFilterTest extends BaseTest
{

    /**
     * @var AbstractFilter
     */
    protected $filter;

    protected function _before()
    {
        $this->filter = $this->getMockForAbstractClass(AbstractFilter::class);
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
