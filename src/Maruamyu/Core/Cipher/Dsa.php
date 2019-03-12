<?php

namespace Maruamyu\Core\Cipher;

use Maruamyu\Core\Asn1;

/**
 * DSA (not EC) cryptography
 */
class Dsa extends PublicKeyCryptography
{
    const DSA_OBJECT_ID = '1.2.840.10040.4.1';

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
            && ($detail['type'] === OPENSSL_KEYTYPE_DSA)
            && isset($detail['dsa'])
            && (isset($detail['dsa']['priv_key']) == false)
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
            && ($detail['type'] === OPENSSL_KEYTYPE_DSA)
            && isset($detail['dsa'])
            && isset($detail['dsa']['priv_key'])
        ) {
            return $privateKey;
        } else {
            return null;
        }
    }

    /**
     * @param string[] $parameters DSA public key parameters (binary)
     *   p
     *   q
     *   g
     *   pub_key
     * @return resource|null public key resource, null if invalid key
     */
    public static function publicKeyFromParameters(array $parameters)
    {
        $parametersSequenceValue = Asn1::encodeIntegerBinary($parameters['p'], true)
            . Asn1::encodeIntegerBinary($parameters['q'], true)
            . Asn1::encodeIntegerBinary($parameters['g'], true);
        $parametersSequence = chr(0x30) . Asn1::toLengthBinary(strlen($parametersSequenceValue)) . $parametersSequenceValue;

        $headerSequenceValue = Asn1::encodeObjectIdentifier(static::DSA_OBJECT_ID) . $parametersSequence;
        $headerSequence = chr(0x30) . Asn1::toLengthBinary(strlen($headerSequenceValue)) . $headerSequenceValue;

        $pubBitStringValue = Asn1::encodeIntegerBinary($parameters['pub_key'], true);

        $publicKeySequenceValue = $headerSequence . Asn1::encodeBitString($pubBitStringValue);
        $publicKeySequence = chr(0x30) . Asn1::toLengthBinary(strlen($publicKeySequenceValue)) . $publicKeySequenceValue;

        $publicKeyPem = '-----BEGIN PUBLIC KEY-----' . "\r\n" . chunk_split(base64_encode($publicKeySequence)) . '-----END PUBLIC KEY-----';
        return openssl_pkey_get_public($publicKeyPem);
    }

    /**
     * @param string[] $parameters DSA private key parameters (binary)
     *   p
     *   q
     *   g
     *   pub_key
     *   priv_key
     * @return resource|null public key resource, null if invalid key
     */
    public static function privateKeyFromParameters(array $parameters)
    {
        $parameterValues = [
            Asn1::encodeInteger(0),  # version = 0
            Asn1::encodeIntegerBinary($parameters['p'], true),
            Asn1::encodeIntegerBinary($parameters['q'], true),
            Asn1::encodeIntegerBinary($parameters['g'], true),
            Asn1::encodeIntegerBinary($parameters['pub_key'], true),
            Asn1::encodeIntegerBinary($parameters['priv_key'], true),
        ];
        $privateKeySequenceValue = join('', $parameterValues);
        $privateKeySequence = chr(0x30) . Asn1::toLengthBinary(strlen($privateKeySequenceValue)) . $privateKeySequenceValue;
        $privateKeyPem = '-----BEGIN DSA PRIVATE KEY-----' . "\r\n" . chunk_split(base64_encode($privateKeySequence)) . '-----END DSA PRIVATE KEY-----';
        return openssl_pkey_get_private($privateKeyPem);
    }
}
