<?php

namespace Boot\Utils;

/**
 * Class StringUtils
 *
 * This utility class provides common string functions.
 *
 * @package Boot\Utils
 */
class StringUtils
{
    /**
     * Converts camel case to snake case
     *
     * @param string $string
     * @return string
     */
    public static function snakeCase($string)
    {
        return preg_replace_callback(
            '/([A-Z])/',
            create_function('$c', 'return "_" . strtolower($c[1]);'),
            lcfirst($string)
        );
    }

    /**
     * Converts snake case to camel case
     *
     * @param string  $string
     * @param boolean $capitalized
     *
     * @return string
     */
    public static function camelCase($string, $capitalized = true)
    {
        return preg_replace_callback(
            '/_([a-z])/',
            create_function('$c', 'return strtoupper($c[1]);'),
            ($capitalized) ? ucfirst($string) : lcfirst($string)
        );
    }

    /**
     * Gets an array containing all strings in the subject that are enclosed within the start and end character.
     * For example, to get all route parameters enclosed with curly braces we could do:
     * extractStringsEnclosedWith("my-url/home/{countryCode}/{category}").
     * This method should return the following array:   ['countryCode', 'category]
     *
     * @param string $haystack
     * @param string $openChar
     * @param string $endChar
     *
     * @return string[]
     */
    public static function extractStringsEnclosedWith($haystack, $openChar, $endChar)
    {
        if (empty($openChar) || empty($endChar)) {
            throw new \InvalidArgumentException("The opening and end character cannot be empty!");
        }

        $openChar = preg_quote((string)$openChar[0]);
        $endChar   = preg_quote((string)$endChar[0]);

        $matches = [];
        if ( preg_match_all("/{$openChar}([^{$endChar}]*){$endChar}/", $haystack, $matches)) {
            // Returns matches without curly braces
            return $matches[1];
        }

        return[];
    }

    /**
     * Verifies that a string start with the provided sub-string.
     *
     * @param string $haystack
     * @param string $substring
     *
     * @return bool
     */
    public static function startWith($haystack, $substring)
    {
        if (!is_scalar($haystack) || !is_scalar($substring)) {
            throw new \InvalidArgumentException("Both the needle and haystack need to be of a scalar data type!");
        }

        return strpos((string)$haystack, (string)$substring) === 0;
    }
}