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
use Scorpio\SphinxSearch\SearchIndex;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class BuilderTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\BuilderTest
 */
class BuilderTest extends BaseTest
{

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testFind()
    {
        $builder = Builder::find(new SearchIndex());

        $this->assertInstanceOf(Builder::class, $builder);
    }

    public function testIn()
    {
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->assertInstanceOf(Field::class, $builder->in('field1'));
    }

    public function testInAcceptsNull()
    {
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->assertInstanceOf(Field::class, $builder->in());
    }

    public function testInRaisesExceptionForInvalidFields()
    {
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->expectException('InvalidArgumentException');
        $this->assertInstanceOf(Field::class, $builder->in('bob'));
    }

    public function testNotIn()
    {
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->assertInstanceOf(Field::class, $builder->notIn('field1'));
    }

    public function testNotInAcceptsNull()
    {
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->assertInstanceOf(Field::class, $builder->notIn());
    }

    public function testNotInRaisesExceptionForInvalidFields()
    {
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->expectException('InvalidArgumentException');
        $this->assertInstanceOf(Field::class, $builder->notIn('bob'));
    }

    public function testGetFields()
    {
        $this->assertInternalType('array', Builder::find(new SearchIndex())->getFields());
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $builder->in('field1');
        $builder->in('field2');
        $this->assertEquals(2, $builder->count());
        $this->assertCount(2, $builder->getFields());
    }

    public function testCount()
    {
        $this->assertInternalType('array', Builder::find(new SearchIndex())->getFields());
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $this->assertEquals(0, $builder->count());
        $builder->in('field1');
        $builder->in('field2');
        $this->assertEquals(2, $builder->count());
    }

    public function testCanIterateFields()
    {
        $this->assertInternalType('array', Builder::find(new SearchIndex())->getFields());
        $builder = Builder::find(new SearchIndex('index', ['field1', 'field2']));
        $builder->in('field1');
        $builder->in('field2');

        foreach ( $builder as $field ) {
            $this->assertInstanceOf(Field::class, $field);
        }
    }

    public function testCanCastToString()
    {
        $builder = Builder::find(new SearchIndex('bob', ['name', 'address']))
            ->in('name,address')
                ->whereField()
                    ->containsOneOf('jim alex', 'bob')
                    ->containsStrictOrderOf('bob', 'alex smith')
                ->end()
            ->end()
            ->notIn('address', 30)
                ->whereField()
                    ->contains('plymouth')
                ->end()
            ->end();

        $query = $builder->getQuery();

        $this->assertEquals('(@(name,address) ("jim alex"|bob) bob << "alex smith") (@!address[30] plymouth)', (string)$builder);
        $this->assertEquals('(@(name,address) ("jim alex"|bob) bob << "alex smith") (@!address[30] plymouth)', $query);
    }
}
