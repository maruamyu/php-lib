<?php

namespace Maruamyu\Core;

trait ArrayDetectionTrait
{
    /**
     * 純粋な配列かどうか判定する.
     *
     * @param array $array 配列
     * @return boolean 純粋な配列ならtrue, それ以外(連想配列等)はfalse
     */
    protected static function isVector($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $idx = 0;
        foreach ($array as $key => $value) {
            if ($key !== $idx) {
                return false;
            }
            $idx++;
        }
        return true;
    }

    /**
     * 連想配列かどうか判定する.
     *
     * @param array $array 配列
     * @return boolean 連想配列ならtrue, それ以外はfalse
     */
    protected static function isAssoc($array)
    {
        if (!is_array($array)) {
            return false;
        }
        return !(static::isVector($array));
    }
}
