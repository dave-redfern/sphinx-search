<?php

/*
 * This file is part of the Scorpio SphinxSearch Bundle.
 *
 * (c) Dave Redfern <info@somnambulist.tech>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scorpio\SphinxSearch\Query;

/**
 * Class Criteria
 *
 * Criteria covers many of the keyword matching options Sphinx has. This will attempt
 * to convert the parameters into the most logically structured query while trying to
 * honour word groups and phrases.
 *
 * @package    Scorpio\SphinxSearch\Query
 * @subpackage Scorpio\SphinxSearch\Query\Criteria
 * @author     Dave Redfern <info@somnambulist.tech>
 */
class Criteria implements \IteratorAggregate, \Countable
{

    /**
     * @var array
     */
    private $phrases = [];

    /**
     * @var Field
     */
    private $field;



    /**
     * Constructor.
     *
     * @param Field|null $field
     */
    public function __construct(Field $field = null)
    {
        $this->field = $field;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(' ', $this->phrases);
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->phrases);
    }

    /**
     * @return integer
     */
    public function count()
    {
        return count($this->phrases);
    }

    /**
     * Removes all phrases in this Criteria
     *
     * @return $this
     */
    public function clear()
    {
        $this->phrases = [];

        return $this;
    }

    /**
     * @return array
     */
    public function getPhrases()
    {
        return $this->phrases;
    }

    /**
     * @return Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return Field
     * @throws \RuntimeException if no Field is set
     */
    public function end()
    {
        if ( !$this->field ) {
            throw new \RuntimeException('end() cannot be called on Criteria as no Field was provided');
        }

        return $this->field;
    }



    /**
     * Escapes a phrase with () or "" depending on type
     *
     * @param Phrase[]    $phrases
     * @param null|string $type (any|all|null) any = (my phrase), all = "my phrase", null = do nothing
     *
     * @return array
     */
    private function escapePhrasesForExpression(array $phrases, $type = null)
    {
        $return  = [];

        switch ( $type ) {
            case 'any': $s = '(%s)'; break;
            case 'all': $s = '"%s"'; break;
            default:    $s = '%s';
        }

        foreach ($phrases as $phrase) {
            if (!$phrase instanceof Phrase) {
                $phrase = new Phrase($phrase);
            }
            if ($phrase->isEmpty()) {
                continue;
            }

            if ($phrase->isWordGroup() && !$phrase->isPhrase()) {
                $return[] = sprintf($s, $phrase);
            } else {
                $return[] = (string)$phrase;
            }
        }

        return $return;
    }

    /**
     * @param string       $s    sprintf expression e.g. '"%s"'
     * @param string       $sep  separator to place between phrases
     * @param array        $args passed arguments from calling function
     * @param null|string  $type how to escape the passed arguments (any|all|null)
     *
     * @return $this
     */
    private function implodeStrings($s, $sep, array $args, $type = null)
    {
        $this->phrases[] = sprintf($s, implode($sep, $this->escapePhrasesForExpression($args, $type)));

        return $this;
    }

    /**
     * Find matches containing any of the phrases anywhere
     *
     * e.g.: 'jim', 'alex', 'bob' -> 'jim alex bob'
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function contains()
    {
        return $this->implodeStrings('%s', ' ', func_get_args());
    }

    /**
     * Find matches that contain any of the words in each phrase
     *
     * Word groups are treated as distinct choices and are enclosed in ()
     *
     * e.g.: 'jim alex bob' -> '(jim alex bob)'
     *       'jim alex', 'bob' -> '(jim alex) bob'
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function containsAnyOf()
    {
        return $this->implodeStrings('%s', ' ', func_get_args(), 'any');
    }

    /**
     * Find matches containing the phrases exactly
     *
     * Word groups are treated as distinct match phrases and are enclosed in ""
     *
     * e.g.: 'jim alex bob' -> '"jim alex bob"'
     *       'jim', 'alex bob' -> 'jim "alex bob"'
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function containsAllOf()
    {
        return $this->implodeStrings('%s', ' ', func_get_args(), 'all');
    }

    /**
     * Find matches where the field contains at least one of the phrases
     *
     * e.g.: 'jim', 'alex', 'bob' -> '(jim|alex|bob)'
     *       'jim alex', 'bob' -> '("jim alex"|bob)'
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function containsOneOf()
    {
        return $this->implodeStrings('(%s)', '|', func_get_args(), 'all');
    }

    /**
     * Find matches where phrases occur in exactly the order specified
     *
     * e.g.: 'bob', 'smith' -> bob << smith
     *       'bob', 'alex smith' -> bob << "alex smith"
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function containsStrictOrderOf()
    {
        return $this->implodeStrings('%s', ' << ', func_get_args(), 'all');
    }

    /**
     * Find matches that do not contain the keywords, expands phrases into separate keywords
     *
     *
     * e.g.: 'jim alex bob' -> '-jim -alex -bob'
     *       'jim', 'alex bob' -> '-jim -alex -bob'
     *
     * @param string $keyword,...
     *
     * @return $this
     */
    public function doesNotContainKeywords()
    {
        $words = [];

        foreach ( func_get_args() as $phrase ) {
            $phrase = strtr($phrase, [
                '"' => '', "'" => '', ',' => ' ', ':' => ' ', ';' => ' ', '|' => ' ',
            ]);
            $words = array_merge($words, explode(' ', $phrase));
        }
        $words = array_unique($words);


        return $this->implodeStrings('-%s', ' -', $words, 'any');
    }

    /**
     * Find matches that do not contain any of the words in the phrases
     *
     * e.g.: 'jim', 'alex bob' -> '-(jim) -(alex bob)'
     *       'jim alex bob' -> '-(jim alex bob)'
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function doesNotContainPhrases()
    {
        return $this->implodeStrings('-(%s)', ') -(', func_get_args());
    }

    /**
     * Find matches that do not contain any of the phrases with left most being the first match
     *
     * e.g.: 'aaa', 'bbb', 'ccc ddd' gives -(aaa -(bbb -(ccc ddd)))
     *
     * @param string $phrase,...
     *
     * @return $this
     */
    public function hasPhraseNotContainingPhrase()
    {
        $string = '';

        foreach (array_reverse(func_get_args()) as $phrase) {
            $string = sprintf('-(%s%s)', $phrase, ($string ? ' ' . $string : ''));
        }

        $this->phrases[] = $string;

        return $this;
    }

    /**
     * Find matches where at least X words from keywords are found
     *
     * @param string  $keywords
     * @param integer $threshold
     *
     * @return $this
     */
    public function containsKeywordsInQuorum($keywords, $threshold)
    {
        $this->phrases[] = sprintf('"%s"/%s', str_replace('"', '', trim($keywords)), $threshold);

        return $this;
    }

    /**
     * Find matches where the keywords are within X number of words of one another
     *
     * From Sphinx: http://sphinxsearch.com/docs/latest/extended-syntax.html
     * Proximity distance is specified in words, adjusted for word count, and applies to all words
     * within quotes. For instance, "cat dog mouse"~5 query means that there must be less than 8-word
     * span which contains all 3 words, ie. "CAT aaa bbb ccc DOG eee fff MOUSE" document will not
     * match this query, because this span is exactly 8 words long.
     *
     * @param string  $keywords
     * @param integer $proximity
     *
     * @return $this
     */
    public function containsKeywordsInProximity($keywords, $proximity)
    {
        $this->phrases[] = sprintf('"%s"~%s', str_replace('"', '', trim($keywords)), $proximity);

        return $this;
    }

    /**
     * Find matches where the field beings with phrase
     *
     * @param string $phrase
     *
     * @return $this
     */
    public function startsWith($phrase)
    {
        $this->phrases[] = sprintf('^%s', $phrase);

        return $this;
    }

    /**
     * Find matches where the field ends with the phrase
     *
     * @param string $phrase
     *
     * @return $this
     */
    public function endsWith($phrase)
    {
        $this->phrases[] = sprintf('%s$', $phrase);

        return $this;
    }
}
