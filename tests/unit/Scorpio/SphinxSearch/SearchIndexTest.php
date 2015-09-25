<?php

/*
 * This file is part of the Scorpio Sphinx-Search package.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch;

use Codeception\Util\Stub;
use Scorpio\SphinxSearch\Result\ResultRecord;
use Scorpio\SphinxSearch\Result\ResultSet;

class TestIndex2 extends SearchIndex
{
    protected function initialise()
    {
        $this->name = 'testindex';

        $this->availableFields  = [
            'name', 'gender', 'address',
        ];
        $this->availableFilters = [
            'age', 'gender', 'bob',
        ];
    }

    public function enableWildcards()
    {
        $this->supportsWildcard = true;
        $this->useWildcardKeywords = true;
    }
}

/**
 * Class SearchIndexTest
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\SearchIndexTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class SearchIndexTest extends \Codeception\TestCase\Test
{

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var TestIndex2
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
        $this->object = new TestIndex2();
    }

    protected function _after()
    {
    }

    // tests
    public function testGetName()
    {
        $this->assertEquals('testindex', $this->object->getName());
        $this->assertEquals('testindex', $this->object->toString());
        $this->assertEquals('testindex', (string)$this->object);
    }

    public function testGetResultSetClass()
    {
        $this->assertEquals(ResultSet::class, $this->object->getResultSetClass());
    }

    public function testGetResultClass()
    {
        $this->assertEquals(ResultRecord::class, $this->object->getResultClass());
    }

    public function testGetAvailableFields()
    {
        $this->assertInternalType('array', $this->object->getAvailableFields());
    }

    public function testGetAvailableFilters()
    {
        $this->assertInternalType('array', $this->object->getAvailableFilters());
    }

    public function testIsValidField()
    {
        $this->assertFalse($this->object->isValidField('bob'));
        $this->assertTrue($this->object->isValidField('name'));
    }

    public function testIsValidFilter()
    {
        $this->assertFalse($this->object->isValidFilter('name'));
        $this->assertTrue($this->object->isValidFilter('bob'));
    }

    public function testSetUseWildcardKeywords()
    {
        $this->assertFalse($this->object->useWildcardKeywords());
        $this->object->setUseWildcardKeywords(true);
        $this->assertTrue($this->object->useWildcardKeywords());
    }

    public function testIsWildCardSupported()
    {
        $this->assertFalse($this->object->isWildcardSupported());
    }

    public function testCreateWildcardQueryStringOnlyAppliesIfWildcardsEnabled()
    {
        $this->assertEquals('bob smith', $this->object->createWildcardQueryString('bob smith'));
        $this->object->enableWildcards();
        $this->assertEquals('*bob* *smith*', $this->object->createWildcardQueryString('bob smith'));
    }

    public function testCreateFieldQueryStringRaisesExceptionForInvalidField()
    {
        $this->setExpectedException('InvalidArgumentException');
        $this->object->createFieldQueryString('attribute', 'keywords');
    }

    public function testCreateFieldQueryString()
    {
        $this->assertEquals('@name keywords', $this->object->createFieldQueryString('name', 'keywords'));
        $this->assertEquals('@(name,address) keywords', $this->object->createFieldQueryString('name,address', 'keywords'));
    }
}