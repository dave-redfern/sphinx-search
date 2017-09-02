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

use Scorpio\SphinxSearch\Query\Builder;
use Scorpio\SphinxSearch\Query\Field;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class FieldTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\FieldTest
 */
class FieldTest extends BaseTest
{

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
