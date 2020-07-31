<?php

namespace Maruamyu\Core;

/**
 * PascalCase, camelCase, snake_case, kebab-case
 */
class CaseConverter
{
    /**
     * @param string $snake_or_kebab
     * @return string camelCase
     */
    public static function toCamelCase($snake_or_kebab)
    {
        $pascal = static::toPascalCase($snake_or_kebab);
        return strtolower(substr($pascal, 0, 1)) . substr($pascal, 1);
    }

    /**
     * @param string $snake_or_kebab
     * @return string PascalCase
     */
    public static function toPascalCase($snake_or_kebab)
    {
        return str_replace(['_', '-'], ['', ''], ucwords($snake_or_kebab, '_-'));
    }

    /**
     * @param string $studlyCaps_or_kebab
     * @return string snake_case
     */
    public static function toSnakeCase($studlyCaps_or_kebab)
    {
        $snake = strtr($studlyCaps_or_kebab, '-', '_');
        $snake = strtolower(preg_replace('/([A-Z])/u', '_\1', $snake));
        if (substr($snake, 0, 1) === '_') {
            $snake = substr($snake, 1);
        }
        return $snake;
    }

    /**
     * @param string $studlyCaps_or_kebab
     * @return string snake_case
     * @see toSnakeCase()
     */
    public static function to_snake_case($studlyCaps_or_kebab)
    {
        return static::toSnakeCase($studlyCaps_or_kebab);
    }

    /**
     * @param string $studlyCaps_or_kebab
     * @return string snake_case
     */
    public static function toKebabCase($studlyCaps_or_kebab)
    {
        return str_replace('_', '-', static::toSnakeCase($studlyCaps_or_kebab));
    }
}
