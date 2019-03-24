<?php

namespace Maruamyu\Core\Cipher;

/**
 * Digest utils
 */
class Digest
{
    /**
     * shorthand of SHA-1 raw output
     *
     * @param string $message
     * @return string
     * @see sha1()
     */
    public static function sha1($message)
    {
        return sha1($message, true);
    }

    /**
     * shorthand of SHA256 raw output
     *
     * @param string $message
     * @return string
     * @see hash()
     */
    public static function sha256($message)
    {
        return hash('sha256', $message, true);
    }

    /**
     * shorthand of SHA512 raw output
     *
     * @param string $message
     * @return string
     * @see hash()
     */
    public static function sha512($message)
    {
        return hash('sha512', $message, true);
    }
}
