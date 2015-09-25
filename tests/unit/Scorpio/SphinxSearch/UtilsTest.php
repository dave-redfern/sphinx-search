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
use Scorpio\SphinxSearch\Filter\FilterAttribute;

/**
 * Class UtilsTest
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\UtilsTest
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class UtilsTest extends \Codeception\TestCase\Test
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
    public function test()
    {

    }

    public function testEscapeQueryString()
    {
        $this->assertEquals('\$50', Utils::escapeQueryString('$50'));
        $this->assertEquals('\&50', Utils::escapeQueryString('&50'));
        $this->assertEquals('\(\$50 between \@monday \& tuesday\) \= wednesday', Utils::escapeQueryString('($50 between @monday & tuesday) = wednesday'));
    }

    public function testCreateFiltersFromCriteria()
    {
        $criteria = [
            'get_gender'    => 'male',
            'get_age_range' => '18_30',
        ];
        $mappings = [
            'get_gender'    => 'gender',
            'get_age_range' => 'age_range',
        ];
        $attributeMap = [
            'gender' => [
                'male'          => 1,
                'female'        => 2,
                'undisclosed'   => 3,
                'transgendered' => 4,
            ],
            'age_range' => [
                '0_18'  => 101,
                '18_30' => 102,
            ]
        ];

        $filters = Utils::createFiltersFromCriteria($criteria, $mappings, $attributeMap);

        $this->assertCount(2, $filters);
        foreach ( $filters as $filter ) {
            $this->assertInstanceOf(FilterAttribute::class, $filter);
        }
    }

    public function testCreateFiltersFromCriteriaCanConvertStringsOfValues()
    {
        $criteria = [
            'get_gender'    => 'male,female,transgendered',
        ];
        $mappings = [
            'get_gender'    => 'gender',
            'get_age_range' => 'age_range',
        ];
        $attributeMap = [
            'gender' => [
                'male'          => 1,
                'female'        => 2,
                'undisclosed'   => 3,
                'transgendered' => 4,
            ],
            'age_range' => [
                '0_18'  => 101,
                '18_30' => 102,
            ]
        ];

        $filters = Utils::createFiltersFromCriteria($criteria, $mappings, $attributeMap);
        $filter  = reset($filters);
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(FilterAttribute::class, $filter);
        $this->assertCount(3, $filter->getValues());
    }

    public function testConvertStringToArray()
    {
        $this->assertInternalType('array', Utils::convertStringToArray('1,2,3,4'));
        $this->assertCount(4, Utils::convertStringToArray('1,2,3,4'));
    }

    public function testPrepareAttributeValueWhenUsingMapReturnsNullForUnmappedValues()
    {
        $this->assertNull(Utils::prepareAttributeValue(''));
        $this->assertEquals(0, Utils::prepareAttributeValue(0));

        $attributeMap = [
            'gender'    => [
                'male'          => 1,
                'female'        => 2,
                'undisclosed'   => 3,
                'transgendered' => 4,
            ],
            'age_range' => [
                '0_18'  => 101,
                '18_30' => 102,
            ]
        ];

        $this->assertNull(Utils::prepareAttributeValue('23', 'gender', $attributeMap));
    }

    public function testPrepareAttributeValueReturnsMappedValue()
    {
        $attributeMap = [
            'gender'    => [
                'male'          => 1,
                'female'        => 2,
                'undisclosed'   => 3,
                'transgendered' => 4,
            ],
            'age_range' => [
                '0_18'  => 101,
                '18_30' => 102,
            ]
        ];

        $this->assertEquals(1, Utils::prepareAttributeValue('male', 'gender', $attributeMap));
        $this->assertEquals(102, Utils::prepareAttributeValue('18_30', 'age_range', $attributeMap));
    }

    public function testPrepareAttributeValueReturnsIntegerValueIfNoEntryInMap()
    {
        $attributeMap = [
            'gender'    => [
                'male'          => 1,
                'female'        => 2,
                'undisclosed'   => 3,
                'transgendered' => 4,
            ],
            'age_range' => [
                '0_18'  => 101,
                '18_30' => 102,
            ]
        ];

        $this->assertInternalType('int', Utils::prepareAttributeValue('male', 'bob', $attributeMap));
    }
}