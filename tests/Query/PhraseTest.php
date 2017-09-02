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

use Scorpio\SphinxSearch\Query\Phrase;
use Scorpio\SphinxSearch\Tests\BaseTest;

/**
 * Class PhraseTest
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\PhraseTest
 */
class PhraseTest extends BaseTest
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
        $phrase = new Phrase('some keywords');
        $this->assertEquals('some keywords', $phrase->getPhrase());
        $this->assertEquals('some keywords', $phrase->getOriginal());
        $this->assertFalse($phrase->isPhrase());
        $this->assertFalse($phrase->isSingleWord());
        $this->assertFalse($phrase->isEmpty());
        $this->assertTrue($phrase->isWordGroup());
        $this->assertEquals(2, $phrase->count());
    }

    public function testConstructorSingleWord()
    {
        $phrase = new Phrase('keyword');
        $this->assertEquals('keyword', $phrase->getPhrase());
        $this->assertEquals('keyword', $phrase->getOriginal());
        $this->assertFalse($phrase->isPhrase());
        $this->assertTrue($phrase->isSingleWord());
        $this->assertFalse($phrase->isEmpty());
        $this->assertFalse($phrase->isWordGroup());
        $this->assertEquals(1, $phrase->count());
    }

    public function testConstructorWithPhrase()
    {
        $phrase = new Phrase('"some keywords"');
        $this->assertEquals('"some keywords"', $phrase->getPhrase());
        $this->assertEquals('"some keywords"', $phrase->getOriginal());
        $this->assertTrue($phrase->isPhrase());
        $this->assertFalse($phrase->isEmpty());
        $this->assertFalse($phrase->isSingleWord());
        $this->assertTrue($phrase->isWordGroup());
        $this->assertEquals(2, $phrase->count());
    }

    public function testConstructorWithEscapeableCharacters()
    {
        $phrase = new Phrase('"some awesome-keywords me@me.com"');
        $this->assertEquals('"some awesome\-keywords me\@me.com"', $phrase->getPhrase());
        $this->assertEquals('"some awesome-keywords me@me.com"', $phrase->getOriginal());
        $this->assertTrue($phrase->isPhrase());
        $this->assertFalse($phrase->isSingleWord());
        $this->assertFalse($phrase->isEmpty());
        $this->assertTrue($phrase->isWordGroup());
        $this->assertEquals(4, $phrase->count());
    }

    public function testConstructorWithEscapeableCharactersNotPhrase()
    {
        $phrase = new Phrase('some "awesome-keywords" me@me.com');
        $this->assertEquals('some \"awesome\-keywords\" me\@me.com', $phrase->getPhrase());
        $this->assertEquals('some "awesome-keywords" me@me.com', $phrase->getOriginal());
        $this->assertFalse($phrase->isPhrase());
        $this->assertFalse($phrase->isSingleWord());
        $this->assertTrue($phrase->isWordGroup());
        $this->assertFalse($phrase->isEmpty());
        $this->assertEquals(4, $phrase->count());
    }

    public function testConvertToPhraseByArguments()
    {
        $phrases = Phrase::convertToPhrase('phrase one', 'phrase two');
        $this->assertCount(2, $phrases);
        foreach ( $phrases as $phrase ) {
            $this->assertInstanceOf(Phrase::class, $phrase);
        }
    }

    public function testConvertToPhraseByArray()
    {
        $phrases = Phrase::convertToPhrase(['phrase one', 'phrase two']);
        $this->assertCount(2, $phrases);
        foreach ( $phrases as $phrase ) {
            $this->assertInstanceOf(Phrase::class, $phrase);
        }
    }

    public function testConvertToPhraseByString()
    {
        $phrases = Phrase::convertToPhrase('phrase one');

        $this->assertCount(1, $phrases);
        foreach ( $phrases as $phrase ) {
            $this->assertInstanceOf(Phrase::class, $phrase);
        }
    }

    public function testIsEmpty()
    {
        $phrase = Phrase::convertToPhrase('')[0];

        $this->assertTrue($phrase->isEmpty());
    }
}
