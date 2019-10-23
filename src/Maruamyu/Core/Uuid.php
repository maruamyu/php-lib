<?php

namespace Maruamyu\Core;

/**
 * UUID util class
 */
class Uuid
{
    /**
     * @return string UUID "XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX"
     * @see generateV4()
     */
    public static function generate()
    {
        return static::generateV4();
    }

    /**
     * @return string UUIDv4
     */
    public static function generateV4()
    {
        $randomizer = (function_exists('random_int')) ? 'random_int' : 'mt_rand';
        $random1 = $randomizer(0, 0xFFFF);
        $random2 = $randomizer(0, 0xFFFF);
        $random3 = $randomizer(0, 0xFFFF);
        $random4 = $randomizer(0, 0x0FFF) | 0x4000;  # 0100xxxx xxxxxxxx (version = 4)
        $random5 = $randomizer(0, 0x3FFF) | 0x8000;  # 10xxxxxx xxxxxxxx (variant = RFC4122)
        $random6 = $randomizer(0, 0xFFFF);
        $random7 = $randomizer(0, 0xFFFF);
        $random8 = $randomizer(0, 0xFFFF);
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            $random1, $random2, $random3, $random4, $random5, $random6, $random7, $random8);
    }

    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
