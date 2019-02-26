<?php

namespace Maruamyu\Core\Cipher;

use Maruamyu\Core\Asn1;

/**
 * ECDSA cryptography
 */
class Ecdsa extends PublicKeyCryptography
{
    const CURVE_OBJECT_ID = [
        'secp112r1' => '1.3.132.0.6',
        'secp112r2' => '1.3.132.0.7',
        'secp128r1' => '1.3.132.0.28',
        'secp128r2' => '1.3.132.0.29',
        'secp160k1' => '1.3.132.0.9',
        'secp160r1' => '1.3.132.0.8',
        'secp160r2' => '1.3.132.0.30',
        'secp192k1' => '1.3.132.0.31',
        'secp224k1' => '1.3.132.0.32',
        'secp224r1' => '1.3.132.0.33',
        'secp256k1' => '1.3.132.0.10',
        'secp256r1' => '1.2.840.10045.3.1.7',
        'secp384r1' => '1.3.132.0.34',
        'secp521r1' => '1.3.132.0.35',
        'brainpoolP160r1' => '1.3.36.3.3.2.8.1.1.1',
        'brainpoolP192r1' => '1.3.36.3.3.2.8.1.1.3',
        'brainpoolP224r1' => '1.3.36.3.3.2.8.1.1.5',
        'brainpoolP256r1' => '1.3.36.3.3.2.8.1.1.7',
        'brainpoolP320r1' => '1.3.36.3.3.2.8.1.1.9',
        'brainpoolP384r1' => '1.3.36.3.3.2.8.1.1.11',
        'brainpoolP512r1' => '1.3.36.3.3.2.8.1.1.13',
    ];

    /**
     * @param string|resource $publicKey
     * @return resource|null
     */
    public static function fetchPublicKey($publicKey)
    {
        $publicKey = parent::fetchPublicKey($publicKey);
        if (!$publicKey) {
            return null;
        }
        $detail = @openssl_pkey_get_details($publicKey);
        if (
            $detail
            && ($detail['type'] === OPENSSL_KEYTYPE_EC)
            && isset($detail['ec'])
            && (isset($detail['ec']['d']) == false)
        ) {
            return $publicKey;
        } else {
            return null;
        }
    }

    /**
     * @param string|resource $privateKey
     * @param string $passphrase
     * @return resource|null
     */
    public static function fetchPrivateKey($privateKey, $passphrase = null)
    {
        $privateKey = parent::fetchPrivateKey($privateKey, $passphrase);
        if (!$privateKey) {
            return null;
        }
        $detail = @openssl_pkey_get_details($privateKey);
        if (
            $detail
            && ($detail['type'] === OPENSSL_KEYTYPE_EC)
            && isset($detail['ec'])
            && isset($detail['ec']['d'])
        ) {
            return $privateKey;
        } else {
            return null;
        }
    }

    /**
     * @param string $curveName
     * @param string $x (binary)
     * @param string $y (binary)
     * @return resource|null public key resource, null if invalid key
     */
    public static function publicKeyFromCurveXY($curveName, $x, $y)
    {
        $curveObjectIds = static::CURVE_OBJECT_ID;
        if (isset($curveObjectIds[$curveName]) == false) {
            $errorMsg = 'curveName = ' . $curveName . ' is not supported.';
            throw new \InvalidArgumentException($errorMsg);
        }
        $curveObjectId = $curveObjectIds[$curveName];

        $headerSequenceValue = Asn1::encodeObjectIdentifier('1.2.840.10045.2.1')
            . Asn1::encodeObjectIdentifier($curveObjectId);
        $headerSequence = chr(0x30) . Asn1::toLengthBinary(strlen($headerSequenceValue)) . $headerSequenceValue;

        $xyBitString = Asn1::encodeBitString(chr(0x04) . $x . $y);

        $publicKeySequenceValue = $headerSequence . $xyBitString;
        $publicKeySequence = chr(0x30) . Asn1::toLengthBinary(strlen($publicKeySequenceValue)) . $publicKeySequenceValue;

        $publicKeyPem = '-----BEGIN PUBLIC KEY-----' . "\r\n" . chunk_split(base64_encode($publicKeySequence)) . '-----END PUBLIC KEY-----';
        return openssl_pkey_get_public($publicKeyPem);
    }

    /**
     * @param string $curveName
     * @param string $x (binary)
     * @param string $y (binary)
     * @param string $d (binary)
     * @return resource|null public key resource, null if invalid key
     */
    public static function privateKeyFromCurveXYD($curveName, $x, $y, $d)
    {
        $curveObjectIds = static::CURVE_OBJECT_ID;
        if (isset($curveObjectIds[$curveName]) == false) {
            $errorMsg = 'curveName = ' . $curveName . ' is not supported.';
            throw new \InvalidArgumentException($errorMsg);
        }
        $curveObjectId = $curveObjectIds[$curveName];

        $privateOctetString = Asn1::encodeOctetString($d);

        $curveObjectIdValue = Asn1::encodeObjectIdentifier($curveObjectId);
        $structure0 = chr(0xA0) . Asn1::toLengthBinary(strlen($curveObjectIdValue)) . $curveObjectIdValue;

        $xyBitString = Asn1::encodeBitString(chr(0x04) . $x . $y);
        $structure1 = chr(0xA1) . Asn1::toLengthBinary(strlen($xyBitString)) . $xyBitString;

        $privateKeySequenceValue = Asn1::encodeInteger(1) . $privateOctetString . $structure0 . $structure1;
        $privateKeySequence = chr(0x30) . Asn1::toLengthBinary(strlen($privateKeySequenceValue)) . $privateKeySequenceValue;

        $privateKeyPem = '-----BEGIN EC PRIVATE KEY-----' . "\r\n" . chunk_split(base64_encode($privateKeySequence)) . '-----END EC PRIVATE KEY-----';
        return openssl_pkey_get_private($privateKeyPem);
    }
}
