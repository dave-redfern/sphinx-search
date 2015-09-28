<?php

/*
 * This file is part of the Scorpio SphinxSearch Bundle.
 *
 * (c) Dave Redfern <dave@scorpioframework.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Query;

use Scorpio\SphinxSearch\Utils;

/**
 * Class Phrase
 *
 * Represents a set of keywords that should be treated as a single phrase in Criteria.
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\Phrase
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class Phrase implements \Countable
{

    /**
     * @var string
     */
    private $original;

    /**
     * @var string
     */
    private $phrase;

    /**
     * @var integer
     */
    private $words;

    /**
     * @var boolean
     */
    private $isPhrase;


    /**
     * Constructor.
     *
     * @param string $phrase
     */
    public function __construct($phrase)
    {
        $this->original = $phrase;
        $this->isPhrase = (preg_match('/^(["\']).*\1$/m', $phrase) != 0);
        $this->phrase   = $this->escape(trim($phrase));
        $this->words    = ($this->isEmpty() ? 0 : str_word_count(trim($phrase), 0, '@'));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->phrase;
    }

    /**
     * Escapes characters in string that hold meaning in Sphinx; except starting and ending double quotes
     *
     * @param string $string
     *
     * @return string
     */
    private function escape($string)
    {
        $s = '%s';
        if ( $this->isPhrase ) {
            $string = rtrim(ltrim($string, '"'), '"');
            $s      = '"%s"';
        }

        return sprintf($s, Utils::escapeQueryString($string));
    }

    /**
     * Converts the passed words to an array of Phrase objects
     *
     * Either pass an array of phrases, or each phrase as an argument:
     *
     * <code>
     * Phrase::convertToPhrase(['one', 'two', 'three four']);
     * Phrase::convertToPhrase('one', 'two', 'three four');
     * </code>
     *
     * @param string|array $words
     * @param string       $word,...
     *
     * @return Phrase[]
     */
    public static function convertToPhrase($words)
    {
        $return = [];

        if (func_num_args() > 1) {
            $words = func_get_args();
        }
        if (!is_array($words)) {
            $words = [$words];
        }

        foreach ($words as $word) {
            $return[] = new Phrase((string)$word);
        }

        return $return;
    }

    /**
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->phrase);
    }

    /**
     * @return boolean
     */
    public function isSingleWord()
    {
        return ($this->words == 1);
    }

    /**
     * @return boolean
     */
    public function isWordGroup()
    {
        return ($this->words > 1);
    }

    /**
     * @return boolean
     */
    public function isPhrase()
    {
        return $this->isPhrase;
    }

    /**
     * @return string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * @return string
     */
    public function getPhrase()
    {
        return $this->phrase;
    }

    /**
     * @return integer
     */
    public function count()
    {
        return $this->words;
    }
}