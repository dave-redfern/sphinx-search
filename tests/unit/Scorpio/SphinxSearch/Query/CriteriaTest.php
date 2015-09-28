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

/**
 * Class CriteriaTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\CriteriaTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class CriteriaTest extends \Codeception\TestCase\Test
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
        $criteria = new Criteria($this->I->createNullQueryField());

        $this->assertInstanceOf(Field::class, $criteria->getField());
    }

    public function testConstructorWithoutArguments()
    {
        $criteria = new Criteria();

        $this->assertNull($criteria->getField());
    }

    public function testEndWithoutFieldRaisesException()
    {
        $criteria = new Criteria();
        $this->setExpectedException('RuntimeException');
        $criteria->end();
    }

    public function testCount()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals(0, $criteria->count());
        $criteria->contains('bob alex');
        $criteria->contains('bob alex');
        $this->assertEquals(2, $criteria->count());
        $criteria->clear();
        $this->assertEquals(0, $criteria->count());
    }

    public function testGetPhrases()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals(0, $criteria->count());
        $criteria->contains('bob', 'alex');
        $criteria->contains('bob alex');
        $this->assertEquals(2, $criteria->count());

        $this->assertInternalType('array', $criteria->getPhrases());
    }

    public function testGetIterator()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals(0, $criteria->count());
        $criteria->contains('bob', 'alex');
        $criteria->contains('bob alex');
        $this->assertEquals(2, $criteria->count());

        $this->assertInternalType('array', $criteria->getPhrases());

        foreach ( $criteria as $string ) {
            $this->assertInternalType('string', $string);
        }
    }

    public function testContains()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('bob alex', (string)$criteria->contains('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('bob alex', (string)$criteria->contains('bob alex'));
        $criteria->clear();
        $this->assertEquals('"bob alex"', (string)$criteria->contains('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('"bob alex" john wendy', (string)$criteria->contains('"bob alex"', 'john', 'wendy'));
    }

    public function testContainsAnyOf()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('bob alex', (string)$criteria->containsAnyOf('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('(bob alex)', (string)$criteria->containsAnyOf('bob alex'));
        $criteria->clear();
        $this->assertEquals('"bob alex"', (string)$criteria->containsAnyOf('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('"bob alex" (john wendy)', (string)$criteria->containsAnyOf('"bob alex"', 'john wendy'));
    }

    public function testContainsAllOf()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('bob alex', (string)$criteria->containsAllOf('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('"bob alex"', (string)$criteria->containsAllOf('bob alex'));
        $criteria->clear();
        $this->assertEquals('"bob alex"', (string)$criteria->containsAllOf('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('"bob alex" "john wendy"', (string)$criteria->containsAllOf('"bob alex"', 'john wendy'));
    }

    public function testContainsOneOf()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('(bob|alex)', (string)$criteria->containsOneOf('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('("bob alex")', (string)$criteria->containsOneOf('bob alex'));
        $criteria->clear();
        $this->assertEquals('("bob alex")', (string)$criteria->containsOneOf('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('("bob alex"|"john wendy")', (string)$criteria->containsOneOf('"bob alex"', 'john wendy'));
    }

    public function testContainsStrictOrderOf()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('bob << alex', (string)$criteria->containsStrictOrderOf('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('"bob alex"', (string)$criteria->containsStrictOrderOf('bob alex'));
        $criteria->clear();
        $this->assertEquals('"bob alex"', (string)$criteria->containsStrictOrderOf('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('"bob alex" << "john wendy"', (string)$criteria->containsStrictOrderOf('"bob alex"', 'john wendy'));
    }

    public function testDoesNotContainKeywords()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('-bob -alex', (string)$criteria->doesNotContainKeywords('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('-bob -alex', (string)$criteria->doesNotContainKeywords('bob alex'));
        $criteria->clear();
        $this->assertEquals('-bob -alex', (string)$criteria->doesNotContainKeywords('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('-bob -alex -john -wendy', (string)$criteria->doesNotContainKeywords('"bob alex"', 'john wendy'));
    }

    public function testDoesNotContainPhrases()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('-(bob) -(alex)', (string)$criteria->doesNotContainPhrases('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('-(bob alex)', (string)$criteria->doesNotContainPhrases('bob alex'));
        $criteria->clear();
        $this->assertEquals('-("bob alex")', (string)$criteria->doesNotContainPhrases('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('-("bob alex") -(john wendy)', (string)$criteria->doesNotContainPhrases('"bob alex"', 'john wendy'));
    }

    public function testHasPhraseNotContainingPhrase()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('-(bob -(alex))', (string)$criteria->hasPhraseNotContainingPhrase('bob', 'alex'));
        $criteria->clear();
        $this->assertEquals('-(bob alex)', (string)$criteria->hasPhraseNotContainingPhrase('bob alex'));
        $criteria->clear();
        $this->assertEquals('-("bob alex")', (string)$criteria->hasPhraseNotContainingPhrase('"bob alex"'));
        $criteria->clear();
        $this->assertEquals('-("bob alex" -(john wendy))', (string)$criteria->hasPhraseNotContainingPhrase('"bob alex"', 'john wendy'));
    }

    public function testContainsKeywordsInQuorum()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('"bob alex"/3', (string)$criteria->containsKeywordsInQuorum('bob alex', 3));
        $criteria->clear();
        $this->assertEquals('"bob alex"/3', (string)$criteria->containsKeywordsInQuorum('"bob alex"', 3));
    }

    public function testContainsKeywordsInProximity()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('"bob alex"~3', (string)$criteria->containsKeywordsInProximity('bob alex', 3));
        $criteria->clear();
        $this->assertEquals('"bob alex"~3', (string)$criteria->containsKeywordsInProximity('"bob alex"', 3));
    }

    public function testStartsWith()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('^bob', (string)$criteria->startsWith('bob'));
        $criteria->clear();
    }

    public function testEndsWith()
    {
        $criteria = $this->I->createCriteria();

        $this->assertEquals('bob$', (string)$criteria->endsWith('bob'));
        $criteria->clear();
    }

    public function testMultiplePhrasesWithMethodChaining()
    {
        $criteria = $this->I->createCriteria();

        $criteria
            ->containsOneOf('jim alex', 'bob')
            ->containsStrictOrderOf('bob', 'alex smith')
            ->doesNotContainKeywords('fred alan jeffrey')
            ->containsKeywordsInProximity('bob smith', 2)
        ;

        $this->assertEquals('("jim alex"|bob) bob << "alex smith" -fred -alan -jeffrey "bob smith"~2', (string)$criteria);
    }
}