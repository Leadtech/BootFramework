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
     * @param string $string
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
}