<?php

namespace Maruamyu\Core;

/**
 * ASN.1 utils (minimum)
 */
class Asn1
{
    const TAG_VALUES = [
        'BOOLEAN' => 0x01,
        'INTEGER' => 0x02,
        'BIT_STRING' => 0x03,
        'OCTET_STRING' => 0x04,
        'NULL' => 0x05,
        'OBJECT_IDENTIFIER' => 0x06,
        'ObjectDescriptor' => 0x07,
        'EXTERNAL' => 0x08,
        'REAL' => 0x09,
        'ENUMERATED' => 0x0A,
        # 'reserved' => 0x0B,
        'UTF8String' => 0x0C,
        # 'reserved' => 0x0D,
        # 'reserved' => 0x0E,
        # 'reserved' => 0x0F,
        'SEQUENCE' => 0x10,
        'SET' => 0x11,
        'NumericString' => 0x12,
        'PrintableString' => 0x13,
        'TeletexString' => 0x14,
        'VideotexString' => 0x15,
        'IA5String' => 0x16,
        'UTCTime' => 0x17,
        'GeneralizedTime' => 0x18,
        'GraphicString' => 0x19,
        'VisibleString' => 0x1A,
        'GeneralString' => 0x1B,
        'CharacterString' => 0x1C,
    ];

    /**
     * @param bool $bool
     * @return string
     */
    public static function encodeBoolean($bool)
    {
        $value = ($bool) ? 0x01 : 0x00;
        return chr(static::TAG_VALUES['BOOLEAN']) . chr(0x01) . chr($value);
    }

    /**
     * @param int $value
     * @return string
     */
    public static function encodeInteger($value)
    {
        if ($value > 4294967295 || $value < -4294967296) {  # 64bit
            $binary = pack('q', $value);
        } elseif ($value > 65535 || $value < -65536) {  # 32bit
            $binary = pack('l', $value);
        } elseif ($value > 65535 || $value < -65536) {  # 16bit
            $binary = pack('s', $value);
        } else {  # 8bit
            $binary = pack('c', $value);
        }
        return static::encodeIntegerBinary($binary);
    }

    /**
     * @param string $binary
     * @param bool $forcePositive add 0x00 to head if true and standed MSB
     * @return string
     */
    public static function encodeIntegerBinary($binary, $forcePositive = false)
    {
        if ($forcePositive) {
            $mostSignificantOctet = ord(substr($binary, 0, 1));
            if ($mostSignificantOctet & 0x80) {
                $binary = chr(0x00) . $binary;
            }
        }
        return chr(static::TAG_VALUES['INTEGER']) . Asn1::toLengthBinary(strlen($binary)) . $binary;
    }

    /**
     * @return string
     */
    public static function encodeNull()
    {
        return chr(static::TAG_VALUES['NULL']) . chr(0x00);
    }

    /**
     * @param string $string
     * @return string
     * @todo fixed unuse bit = 0
     */
    public static function encodeBitString($string)
    {
        # fixed unuse bit = 0
        $unUseBit = 0;
        $length = strlen($string) + 1;
        return chr(static::TAG_VALUES['BIT_STRING']) . Asn1::toLengthBinary($length) . chr($unUseBit) . $string;
    }

    /**
     * @param string $string
     * @return string
     */
    public static function encodeOctetString($string)
    {
        return chr(static::TAG_VALUES['OCTET_STRING']) . Asn1::toLengthBinary(strlen($string)) . $string;
    }

    /**
     * @param string $objectIdentifier
     * @return string
     */
    public static function encodeObjectIdentifier($objectIdentifier)
    {
        $binary = static::toObjectIdentifierBinary($objectIdentifier);
        return chr(static::TAG_VALUES['OBJECT_IDENTIFIER']) . Asn1::toLengthBinary(strlen($binary)) . $binary;
    }

    /**
     * @param string $objectIdentifier
     * @return string
     */
    public static function toObjectIdentifierBinary($objectIdentifier)
    {
        $binary = '';

        $parts = explode('.', $objectIdentifier);

        $first1 = array_shift($parts);
        $first2 = array_shift($parts);
        $ord = (intval($first1, 10) * 40) + intval($first2, 10);
        $binary .= chr($ord);

        foreach ($parts as $part) {
            $part = abs(intval($part, 10));
            if ($part == 0) {
                $binary .= chr(0);
                continue;
            }
            $bitParts = [];
            do {
                $bitParts[] = ($part & 0x7F);
                $part = ($part >> 7);
            } while ($part > 0);
            $bitParts = array_reverse($bitParts);
            $bitPartsCount = count($bitParts);
            for ($idx = 0; $idx < ($bitPartsCount - 1); $idx++) {
                $bitParts[$idx] = $bitParts[$idx] | 0x80;
            }
            foreach ($bitParts as $ord) {
                $binary .= chr($ord);
            }
        }
        return $binary;
    }

    /**
     * @param int $length data length
     * @return string ASN.1 encoded binary
     */
    public static function toLengthBinary($length)
    {
        if ($length < 0x80) {
            return chr($length);
        } else {
            $bytes = ltrim(pack('N', $length), chr(0x00));
            return chr(0x80 | strlen($bytes)) . $bytes;
        }
    }
}
