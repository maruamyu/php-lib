<?php

namespace Maruamyu\Core\Cipher;

use Maruamyu\Core\Asn1;

/**
 * ECDSA cryptography
 */
class Ecdsa extends PublicKeyCryptography
{
    const ECDSA_PUBLIC_KEY_OBJECT_ID = '1.2.840.10045.2.1';

    const CURVE_OBJECT_ID = [
        'secp112r1' => '1.3.132.0.6',
        'secp112r2' => '1.3.132.0.7',
        'secp128r1' => '1.3.132.0.28',
        'secp128r2' => '1.3.132.0.29',
        'secp160k1' => '1.3.132.0.9',
        'secp160r1' => '1.3.132.0.8',
        'secp160r2' => '1.3.132.0.30',
        'secp192k1' => '1.3.132.0.31',
        'secp192r1' => '1.2.840.10045.3.1.1',  # = prime192v1
        'secp224k1' => '1.3.132.0.32',
        'secp224r1' => '1.3.132.0.33',
        'secp256k1' => '1.3.132.0.10',
        'secp256r1' => '1.2.840.10045.3.1.7',  # = prime256v1
        'secp384r1' => '1.3.132.0.34',
        'secp521r1' => '1.3.132.0.35',
        'prime192v1' => '1.2.840.10045.3.1.1',
        'prime192v2' => '1.2.840.10045.3.1.2',
        'prime192v3' => '1.2.840.10045.3.1.3',
        'prime239v1' => '1.2.840.10045.3.1.4',
        'prime239v2' => '1.2.840.10045.3.1.5',
        'prime239v3' => '1.2.840.10045.3.1.6',
        'prime256v1' => '1.2.840.10045.3.1.7',
        'sect113r1' => '1.3.132.0.4',
        'sect113r2' => '1.3.132.0.5',
        'sect131r1' => '1.3.132.0.22',
        'sect131r2' => '1.3.132.0.23',
        'sect163k1' => '1.3.132.0.1',
        'sect163r1' => '1.3.132.0.2',
        'sect163r2' => '1.3.132.0.15',
        'sect193r1' => '1.3.132.0.24',
        'sect193r2' => '1.3.132.0.25',
        'sect233k1' => '1.3.132.0.26',
        'sect233r1' => '1.3.132.0.27',
        'sect239k1' => '1.3.132.0.3',
        'sect283k1' => '1.3.132.0.16',
        'sect283r1' => '1.3.132.0.17',
        'sect409k1' => '1.3.132.0.36',
        'sect409r1' => '1.3.132.0.37',
        'sect571k1' => '1.3.132.0.38',
        'sect571r1' => '1.3.132.0.39',
        'c2pnb163v1' => '1.2.840.10045.3.0.1',
        'c2pnb163v2' => '1.2.840.10045.3.0.2',
        'c2pnb163v3' => '1.2.840.10045.3.0.3',
        'c2pnb176v1' => '1.2.840.10045.3.0.4',
        'c2tnb191v1' => '1.2.840.10045.3.0.5',
        'c2tnb191v2' => '1.2.840.10045.3.0.6',
        'c2tnb191v3' => '1.2.840.10045.3.0.7',
        'c2onb191v4' => '1.2.840.10045.3.0.8',
        'c2onb191v5' => '1.2.840.10045.3.0.9',
        'c2pnb208w1' => '1.2.840.10045.3.0.10',
        'c2tnb239v1' => '1.2.840.10045.3.0.11',
        'c2tnb239v2' => '1.2.840.10045.3.0.12',
        'c2tnb239v3' => '1.2.840.10045.3.0.13',
        'c2onb239v4' => '1.2.840.10045.3.0.14',
        'c2onb239v5' => '1.2.840.10045.3.0.15',
        'c2pnb272w1' => '1.2.840.10045.3.0.16',
        'c2pnb304w1' => '1.2.840.10045.3.0.17',
        'c2tnb359v1' => '1.2.840.10045.3.0.18',
        'c2pnb368w1' => '1.2.840.10045.3.0.19',
        'c2tnb431r1' => '1.2.840.10045.3.0.20',
        'wap-wsg-idm-ecid-wtls1' => '2.23.43.1.4.1',
        'wap-wsg-idm-ecid-wtls3' => '2.23.43.1.4.3',
        'wap-wsg-idm-ecid-wtls4' => '2.23.43.1.4.4',
        'wap-wsg-idm-ecid-wtls5' => '2.23.43.1.4.5',
        'wap-wsg-idm-ecid-wtls6' => '2.23.43.1.4.6',
        'wap-wsg-idm-ecid-wtls7' => '2.23.43.1.4.7',
        'wap-wsg-idm-ecid-wtls8' => '2.23.43.1.4.8',
        'wap-wsg-idm-ecid-wtls9' => '2.23.43.1.4.9',
        'wap-wsg-idm-ecid-wtls10' => '2.23.43.1.4.10',
        'wap-wsg-idm-ecid-wtls11' => '2.23.43.1.4.11',
        'wap-wsg-idm-ecid-wtls12' => '2.23.43.1.4.12',
        'brainpoolP160r1' => '1.3.36.3.3.2.8.1.1.1',
        'brainpoolP160t1' => '1.3.36.3.3.2.8.1.1.2',
        'brainpoolP192r1' => '1.3.36.3.3.2.8.1.1.3',
        'brainpoolP192t1' => '1.3.36.3.3.2.8.1.1.4',
        'brainpoolP224r1' => '1.3.36.3.3.2.8.1.1.5',
        'brainpoolP224t1' => '1.3.36.3.3.2.8.1.1.6',
        'brainpoolP256r1' => '1.3.36.3.3.2.8.1.1.7',
        'brainpoolP256t1' => '1.3.36.3.3.2.8.1.1.8',
        'brainpoolP320r1' => '1.3.36.3.3.2.8.1.1.9',
        'brainpoolP320t1' => '1.3.36.3.3.2.8.1.1.10',
        'brainpoolP384r1' => '1.3.36.3.3.2.8.1.1.11',
        'brainpoolP384t1' => '1.3.36.3.3.2.8.1.1.12',
        'brainpoolP512r1' => '1.3.36.3.3.2.8.1.1.13',
        'brainpoolP512t1' => '1.3.36.3.3.2.8.1.1.14',
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

        $headerSequenceValue = Asn1::encodeObjectIdentifier(static::ECDSA_PUBLIC_KEY_OBJECT_ID) . Asn1::encodeObjectIdentifier($curveObjectId);
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
