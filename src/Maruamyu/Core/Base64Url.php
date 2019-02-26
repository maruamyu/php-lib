<?php

namespace Maruamyu\Core;

/**
 * Base64-URL codec
 */
class Base64Url
{
    /**
     * @param string $src source string
     * @return string Base64-URL encoded string
     */
    public static function encode($src)
    {
        return str_replace(
            ['+', '/', '=', "\r", "\n"],
            ['-', '_', '', '', ''],
            base64_encode($src)
        );
    }

    /**
     * @param string $src Base64-URL encoded string
     * @return string decoded string
     */
    public static function decode($src)
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $src));
    }

    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
