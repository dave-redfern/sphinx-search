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
use Scorpio\SphinxSearch\SearchIndex;

/**
 * Class FieldTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\FieldTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class FieldTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;
    
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
    }

    protected function _after()
    {
    }

    // tests
    public function testConstructor()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1');
        $this->assertEquals('field1', $field->getField());
    }

    public function testConstructorWithAllArguments()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1', 23, true);
        $this->assertEquals('field1', $field->getField());
        $this->assertEquals(23, $field->getWithin());
        $this->assertTrue($field->isNot());
    }

    public function testIsMultiField()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1', 23, true);
        $this->assertFalse($field->isMultiField());

        $field = new Field($this->I->createQueryBuilder(), 'field1,field2', 23, true);
        $this->assertTrue($field->isMultiField());
    }

    public function testCastToString()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1');

        $this->assertEquals('@(field1) ', (string) $field);
    }

    public function testCastToStringWithNoField()
    {
        $field = new Field($this->I->createQueryBuilder(), null);

        $this->assertEquals('', (string) $field);
    }

    public function testCastToStringWithMultipleFields()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1,field2');

        $this->assertEquals('@(field1,field2) ', (string) $field);
    }

    public function testCastToStringWithLimit()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1', 23);

        $this->assertEquals('@field1[23] ', (string) $field);
    }

    public function testCastToStringWithLimitIsIgnoredWithMultipleFields()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1,field2', 23);

        $this->assertEquals('@(field1,field2) ', (string) $field);
    }

    public function testCastToStringNegated()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1', 23, true);

        $this->assertEquals('@!field1[23] ', (string) $field);
    }

    public function testEndReturnsBuilder()
    {
        $field = new Field($this->I->createQueryBuilder(), 'field1', 23, true);

        $this->assertInstanceOf(Builder::class, $field->end());
    }
}