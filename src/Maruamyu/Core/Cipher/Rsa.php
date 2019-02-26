<?php

namespace Maruamyu\Core\Cipher;

use Maruamyu\Core\Asn1;

/**
 * RSA cryptography
 */
class Rsa extends PublicKeyCryptography
{
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
            && ($detail['type'] === OPENSSL_KEYTYPE_RSA)
            && isset($detail['rsa'])
            && (isset($detail['rsa']['d']) == false)
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
            && ($detail['type'] === OPENSSL_KEYTYPE_RSA)
            && isset($detail['rsa'])
            && isset($detail['rsa']['d'])
        ) {
            return $privateKey;
        } else {
            return null;
        }
    }

    /**
     * @param string $modulus RSA Public key modulus (binary)
     * @param string $exponent RSA Public key exponent (binary)
     * @return resource|null public key resource, null if invalid key
     */
    public static function publicKeyFromModulusAndExponent($modulus, $exponent)
    {
        $parametersSequenceValue = Asn1::encodeIntegerBinary(chr(0x00) . $modulus)
            . Asn1::encodeIntegerBinary($exponent);
        $parametersSequence = chr(0x30) . Asn1::toLengthBinary(strlen($parametersSequenceValue)) . $parametersSequenceValue;

        $parametersSequenceBitString = Asn1::encodeBitString($parametersSequence);

        $headerSequenceValue = Asn1::encodeObjectIdentifier('1.2.840.113549.1.1.1') . Asn1::encodeNull();
        $headerSequence = chr(0x30) . Asn1::toLengthBinary(strlen($headerSequenceValue)) . $headerSequenceValue;

        $publicKeySequenceValue = $headerSequence . $parametersSequenceBitString;
        $publicKeySequence = chr(0x30) . Asn1::toLengthBinary(strlen($publicKeySequenceValue)) . $publicKeySequenceValue;

        $publicKeyPem = '-----BEGIN PUBLIC KEY-----' . "\r\n" . chunk_split(base64_encode($publicKeySequence)) . '-----END PUBLIC KEY-----';
        return openssl_pkey_get_public($publicKeyPem);
    }

    /**
     * @param string[] $parameters RSA private key parameters (binary)
     *   n - modulus
     *   e - publicExponent
     *   d - privateExponent
     *   p - prime1
     *   q - prime2
     *   dmp1 - exponent1, d mod (p-1)
     *   dmq1 - exponent2, d mod (q-1)
     *   iqmp - coefficient, (inverse of q) mod p
     * @return resource|null public key resource, null if invalid key
     */
    public static function privateKeyFromParameters(array $parameters)
    {
        $parameterValues = [
            Asn1::encodeInteger(0),  # version = 0
            Asn1::encodeIntegerBinary($parameters['n']),  # modulus
            Asn1::encodeIntegerBinary($parameters['e']),  # publicExponent
            Asn1::encodeIntegerBinary($parameters['d']),  # privateExponent
            Asn1::encodeIntegerBinary($parameters['p']),  # prime1
            Asn1::encodeIntegerBinary($parameters['q']),  # prime2
            Asn1::encodeIntegerBinary($parameters['dmp1']),  # exponent1
            Asn1::encodeIntegerBinary($parameters['dmq1']),  # exponent2
            Asn1::encodeIntegerBinary($parameters['iqmp']),  # coefficient
        ];
        $privateKeySequenceValue = join('', $parameterValues);
        $privateKeySequence = chr(0x30) . Asn1::toLengthBinary(strlen($privateKeySequenceValue)) . $privateKeySequenceValue;
        $privateKeyPem = '-----BEGIN RSA PRIVATE KEY-----' . "\r\n" . chunk_split(base64_encode($privateKeySequence)) . '-----END RSA PRIVATE KEY-----';
        return openssl_pkey_get_private($privateKeyPem);
    }
}
