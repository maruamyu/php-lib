<?php

namespace Maruamyu\Core;

/**
 * 乱数生成系のユーティリティ
 */
class Randomizer
{
    /**
     * @param int $min
     * @param int $max
     * @return int
     * @see random_int()
     * @see mt_rand()
     */
    public static function randomInt($min, $max)
    {
        if (function_exists('random_int')) {
            try {
                return random_int($min, $max);
            } catch (\Exception $exception) {
                # do nothing...
            }
        }
        return mt_rand($min, $max);
    }

    /**
     * @param int $length
     * @return string
     * @see random_bytes()
     * @see openssl_random_pseudo_bytes()
     */
    public static function randomBytes($length)
    {
        if (function_exists('random_bytes')) {
            try {
                return random_bytes($length);
            } catch (\Exception $exception) {
                # do nothing...
            }
        }

        if (function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        }

        $randomBytes = '';
        for ($i = 0; $i < $length; $i++) {
            $randomBytes .= chr(mt_rand(0, 255));
        }
        return $randomBytes;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateToken($length = 32)
    {
        $randomBytes = static::randomBytes($length);
        return substr(strtr(base64_encode($randomBytes), '+/', '-_'), 0, $length);
    }

    /**
     * @return string
     */
    public static function generateUuid()
    {
        return Uuid::generate();
    }


    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
