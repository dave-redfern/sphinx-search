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

use Scorpio\SphinxSearch\Filter\FilterAttribute;

/**
 * Class Utils
 *
 * @package    Scorpio\SphinxSearch
 * @subpackage Scorpio\SphinxSearch\Utils
 * @author     Dave Redfern <dave@scorpioframework.com>
 */
class Utils
{

    private function __construct() {}
    private function __clone() {}

    /**
     * Converts the string to an array, exploding a list via $separator if it exists
     *
     * @param string $string
     * @param string $separator (optional) Default comma (,)
     *
     * @return array
     */
    public static function convertStringToArray($string, $separator = ',')
    {
        if (strpos($string, $separator) !== false) {
            $value = explode($separator, trim($string));
        } else {
            $value = [$string];
        }

        return $value;
    }

    /**
     * Applies escaping to the keywords string
     *
     * @param string $string
     *
     * @return string
     */
    public static function escapeQueryString($string)
    {
        /*
         * Taken from the sphinxapi.php since SphinxClient::escapeString doesn't escape $ signs
         */
        $from = array('\\', '(', ')', '|', '-', '!', '@', '~', '"', '&', '/', '^', '$', '=');
        $to   = array('\\\\', '\(', '\)', '\|', '\-', '\!', '\@', '\~', '\"', '\&', '\/', '\^', '\$', '\=');

        return str_replace($from, $to, $string);
    }

    /**
     * Creates an array of Sphinx filters from the provided criteria
     *
     * $criteria is an associative array of keys and values supplied from
     * the search form. The keys should be mapped in the {@link self::$mappings}
     * array.
     *
     * Each criteria is then processed and converted into an attribute filter
     * object for a Sphinx search. This only creates int filters and not ranges
     * or float ranges.
     *
     * The attributeMap is an array for converting string values from a form to
     * a valid Sphinx integer value e.g. genders, statuses types etc.
     *
     * @param array $criteria
     * @param array $mappings     Array of mappings for form field to Sphinx attribute
     * @param array $attributeMap Array of mappings for the keys to an attribute value
     *
     * @return array(original_key => Scorpio\SphinxSearch\Filter\FilterInterface)
     */
    public static function createFiltersFromCriteria(array $criteria, array $mappings = [], array $attributeMap = [])
    {
        $filters = [];

        foreach ($criteria as $key => $value) {
            if (isset($mappings[$key])) {
                $tmp  = self::convertStringToArray($value);
                $vars = [];

                foreach ($tmp as $val) {
                    $val = self::prepareAttributeValue($val, $key, $attributeMap);
                    if (null !== $val) {
                        $vars[] = $val;
                    }
                }

                if (count($vars) > 0) {
                    $filters[$key] = new FilterAttribute($mappings[$key], $vars);
                }
            }
        }

        return $filters;
    }

    /**
     * Converts strings to integers
     *
     * If the attribute map and the key is set, the value will be converted
     * from the string representation to the mapped attribute integer value.
     * The $attributeMap should be an associative array containing the form
     * keys and then each string value and it's attribute equivalent e.g.:
     *
     * <code>
     * // example attributeMap
     * $attributeMap = [];
     * $attributeMap['gender']['male'] = 1;
     * $attributeMap['gender']['female'] = 2;
     * </code>
     *
     * @param string $value
     * @param string $key          The form element key
     * @param array  $attributeMap Array of attribute values for a string field
     *
     * @return integer|null
     */
    public static function prepareAttributeValue($value, $key = null, array $attributeMap = [])
    {
        $val = trim($value);

        if (null !== $key && is_array($attributeMap) && array_key_exists($key, $attributeMap)) {
            if (array_key_exists($value, $attributeMap[$key])) {
                $val = $attributeMap[$key][$value];
            } else {
                $val = null;
            }
        }

        if (strlen($val) > 0) {
            return (int)$val;
        } else {
            return null;
        }
    }
}