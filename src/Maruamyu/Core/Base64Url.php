<?php

namespace Maruamyu\Core;

/**
 * Base64-URL codec
 */
class Base64Url
{
    /**
     * @param string $src source string
     * @param bool $strict true if padding
     * @return string Base64-URL encoded string
     */
    public static function encode($src, $strict = false)
    {
        $padded = strtr(base64_encode($src), '+/', '-_');
        if ($strict) {
            return $padded;
        } else {
            return rtrim($padded, '=');
        }
    }

    /**
     * @param string $src Base64-URL encoded string
     * @return string decoded string
     */
    public static function decode($src)
    {
        $src .= str_repeat('=', (strlen($src) % 4));
        return base64_decode(strtr($src, '-_', '+/'));
    }

    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
