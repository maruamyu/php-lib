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
        if (PHP_INT_SIZE >= 8) {
            return static::generateV4_64bit();
        } else {
            return static::generateV4_32bit();
        }
    }

    /**
     * @return string UUIDv4
     */
    private static function generateV4_32bit()
    {
        $random1 = Randomizer::randomInt(0, 0xFFFF);
        $random2 = Randomizer::randomInt(0, 0xFFFF);
        $random3 = Randomizer::randomInt(0, 0xFFFF);
        $random4 = Randomizer::randomInt(0, 0x0FFF) | 0x4000;  # 0100xxxx xxxxxxxx (version = 4)
        $random5 = Randomizer::randomInt(0, 0x3FFF) | 0x8000;  # 10xxxxxx xxxxxxxx (variant = RFC4122)
        $random6 = Randomizer::randomInt(0, 0xFFFF);
        $random7 = Randomizer::randomInt(0, 0xFFFF);
        $random8 = Randomizer::randomInt(0, 0xFFFF);
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            $random1, $random2, $random3, $random4, $random5, $random6, $random7, $random8);
    }

    /**
     * @return string UUIDv4
     */
    private static function generateV4_64bit()
    {
        $random1 = Randomizer::randomInt(0, 0xFFFFFFFF);
        $random234 = Randomizer::randomInt(0, 0xFFFFFFFFFFFF);
        $random5 = Randomizer::randomInt(0, 0xFFFFFFFFFFFF);

        $random2 = ($random234) & 0xFFFF;
        $random3 = (($random234 >> 16) & 0x0FFF) | 0x4000;  # 0100xxxx xxxxxxxx (version = 4)
        $random4 = (($random234 >> 32) & 0x3FFF) | 0x8000;  # 10xxxxxx xxxxxxxx (variant = RFC4122)

        return sprintf('%08X-%04X-%04X-%04X-%012X', $random1, $random2, $random3, $random4, $random5);
    }

    /**
     * constructor (private)
     */
    private function __construct()
    {
    }
}
