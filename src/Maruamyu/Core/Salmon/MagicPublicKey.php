<?php

namespace Maruamyu\Core\Salmon;

use Maruamyu\Core\Base64Url;
use Maruamyu\Core\Cipher\Digest;
use Maruamyu\Core\Cipher\Rsa;
use Maruamyu\Core\Cipher\SignatureInterface;

/**
 * Salmon magic-public-key
 */
class MagicPublicKey implements SignatureInterface
{
    const CONTENT_TYPE = 'application/magic-public-key';

    /** @var resource from openssl_pkey_get_public */
    protected $publicKey;

    /** @var string */
    protected $keyId;

    /**
     * @param string|resource $publicKey public key
     * @param string $keyId
     * @throws \UnexpectedValueException given invalid key
     */
    public function __construct($publicKey, $keyId = '')
    {
        $publicKeyResource = static::fetchPublicKey($publicKey);
        if (!$publicKeyResource) {
            throw new \UnexpectedValueException('invalid public key.');
        }
        $this->publicKey = $publicKeyResource;
        $this->keyId = $keyId;
    }

    /**
     * @return string magic-public-key
     */
    public function __toString()
    {
        $detail = openssl_pkey_get_details($this->publicKey);
        $modulus = $detail['rsa']['n'];
        $exponent = $detail['rsa']['e'];
        return 'RSA.' . Base64Url::encode($modulus, true) . '.' . Base64Url::encode($exponent, true);
    }

    /**
     * @return string SHA256 hash of public key (base64)
     * @throws \RuntimeException if invalid public key
     */
    public function getKeyId()
    {
        if (strlen($this->keyId) < 1) {
            $this->keyId = static::calcKeyId($this->publicKey);
        }
        return $this->keyId;
    }

    /**
     * @param string $message (decoded binary)
     * @param string $signature (decoded binary)
     * @param int $hashAlgorithm
     * @return bool
     */
    public function verifySignature($message, $signature, $hashAlgorithm = OPENSSL_ALGO_SHA256)
    {
        $verified = openssl_verify($message, $signature, $this->publicKey, $hashAlgorithm);
        return ($verified === 1);
    }

    /**
     * @param string $message
     * @param int $hashAlgorithm
     * @throws \BadFunctionCallException this is not has private key
     */
    public function makeSignature($message, $hashAlgorithm = null)
    {
        throw new \BadFunctionCallException('makeSignature() was disabled.');
    }

    /**
     * @return bool false this is not has private key
     */
    public function canMakeSignature()
    {
        return false;
    }

    /**
     * @param string|resource $publicKey
     * @return string SHA256 hash of public key (base64)
     * @throws \RangeException if invalid public key
     */
    public static function calcKeyId($publicKey)
    {
        $publicKey = Rsa::fetchPublicKey($publicKey);

        $detail = openssl_pkey_get_details($publicKey);
        $publicKeyPem = $detail['key'];

        $beginToken = '-----BEGIN PUBLIC KEY-----';
        $endToken = '-----END PUBLIC KEY-----';

        $pos0 = strpos($publicKeyPem, $beginToken);
        if ($pos0 < 0) {
            throw new \RuntimeException('invalid public key.');
        }
        $pos1 = $pos0 + strlen($beginToken);
        $pos2 = strpos($publicKeyPem, $endToken, $pos1);
        if ($pos2 < 0) {
            throw new \RuntimeException('invalid public key.');
        }
        $publicKeyBase64 = substr($publicKeyPem, $pos1, ($pos2 - $pos1));

        $publicKeyRaw = base64_decode($publicKeyBase64);
        return base64_encode(Digest::sha256($publicKeyRaw));
    }

    /**
     * @param string|resource $publicKey public key
     * @return resource|null public key resource, null if invalid key
     */
    protected static function fetchPublicKey($publicKey)
    {
        if (is_string($publicKey)) {
            if (
                (strpos($publicKey, 'RSA.') === 0)
                || (strpos($publicKey, 'data:' . static::CONTENT_TYPE . ',') === 0)
            ) {
                # Salmon magic-public-key string
                list(, $modulus, $exponent) = explode('.', $publicKey);
                $modulus = Base64Url::decode($modulus);
                $exponent = Base64Url::decode($exponent);
                return Rsa::publicKeyFromModulusAndExponent($modulus, $exponent);
            }
        }
        # PEM string or resource or else
        return Rsa::fetchPublicKey($publicKey);
    }
}
