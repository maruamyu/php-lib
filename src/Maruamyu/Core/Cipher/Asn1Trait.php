<?php

namespace Maruamyu\Core\Cipher;

/**
 * ASN.1 utils
 */
trait Asn1Trait
{
    /**
     * @param int $length data length
     * @return string ASN.1 encoded string
     */
    protected static function encodeAsn1Length($length)
    {
        if ($length < 0x80) {
            return chr($length);
        } else {
            $bytes = ltrim(pack('N', $length), chr(0x00));
            return chr(0x80 | strlen($bytes)) . $bytes;
        }
    }
}
