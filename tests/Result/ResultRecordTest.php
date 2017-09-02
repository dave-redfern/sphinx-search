<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Tests\Result;

use Scorpio\SphinxSearch\Result\ResultRecord;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class ResultRecordTest
 *
 * @package    Scorpio\SphinxSearch\Result
 * @subpackage Scorpio\SphinxSearch\Result\ResultRecordTest
 */
class ResultRecordTest extends BaseTest
{

    /**
     * @var ResultRecord
     */
    protected $object;

    protected function _before()
    {
        $this->object = new ResultRecord(12345, ['attrs' => ['attribute1' => 2], 'weight' => 0]);
    }

    protected function _after()
    {
    }

    // tests
    public function testGetId()
    {
        $this->assertEquals(12345, $this->object->getId());
    }

    public function testGetAttributes()
    {
        $this->assertInternalType('array', $this->object->getAttributes());
    }

    public function testGetWeight()
    {
        $this->assertEquals(0, $this->object->getWeight());
    }

    public function testGetAttribute()
    {
        $this->assertEquals(2, $this->object->getAttribute('attribute1'));
    }

    public function testGetAttributeForMissingAttributeReturnsDefault()
    {
        $this->assertNull($this->object->getAttribute('bob'));
        $this->assertFalse($this->object->getAttribute('bob', false));
        $this->assertEquals('bob', $this->object->getAttribute('bob', 'bob'));
    }
}
