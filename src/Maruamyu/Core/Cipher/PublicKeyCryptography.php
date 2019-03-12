<?php

namespace Maruamyu\Core\Cipher;

/**
 * Public-key cryptography
 */
class PublicKeyCryptography implements SignatureInterface, EncryptionInterface
{
    /**
     * @var integer OPENSSL_ALGO_*
     * @see https://secure.php.net/manual/ja/openssl.signature-algos.php
     */
    const DEFAULT_HASH_ALGORITHM = OPENSSL_ALGO_SHA256;

    /** @var resource */
    protected $privateKey;

    /** @var resource */
    protected $publicKey;

    /**
     * @param string|resource $publicKey
     * @param string|resource $privateKey
     * @param string $passphrase
     * @throws \Exception if invalid keys
     */
    public function __construct($publicKey, $privateKey = null, $passphrase = null)
    {
        if ($privateKey) {
            # initialize with private key
            # enable: encrypt, decrypt, makeSignature, verifySignature
            $privateKeyResource = static::fetchPrivateKey($privateKey, $passphrase);
            if (!$privateKeyResource) {
                throw new \DomainException('invalid private key.');
            }
            $this->privateKey = $privateKeyResource;
            # if given private key, then get public key from private key
            $detail = openssl_pkey_get_details($privateKeyResource);
            $this->publicKey = static::fetchPublicKey($detail['key']);
        } else {
            # initialize with public key only
            # enable: encrypt, verifySignature
            $publicKeyResource = static::fetchPublicKey($publicKey);
            if (!$publicKeyResource) {
                throw new \DomainException('invalid public key.');
            }
            $this->publicKey = $publicKeyResource;
        }
    }

    /**
     * @return boolean
     */
    public function hasPrivateKey()
    {
        return isset($this->privateKey);
    }

    /**
     * @return array
     * @see openssl_pkey_get_details()
     */
    public function getPublicKeyDetail()
    {
        return openssl_pkey_get_details($this->publicKey);
    }

    /**
     * @return array
     * @throws \Exception if private key not set yet
     * @see openssl_pkey_get_details()
     */
    public function getPrivateKeyDetail()
    {
        if (!($this->hasPrivateKey())) {
            throw new \RuntimeException('private key required.');
        }
        return openssl_pkey_get_details($this->privateKey);
    }

    /**
     * @param string $message
     * @param integer|null $hashAlgorithm OPENSSL_ALGO_*
     *   (if null, using DEFAULT_HASH_ALGORITHM)
     * @return string signature
     * @throws \Exception if failed or private key not set yet
     */
    public function makeSignature($message, $hashAlgorithm = null)
    {
        if (!($this->hasPrivateKey())) {
            throw new \RuntimeException('private key required.');
        }
        if (is_null($hashAlgorithm)) {
            $hashAlgorithm = static::DEFAULT_HASH_ALGORITHM;
        }
        $signature = null;
        $succeeded = openssl_sign($message, $signature, $this->privateKey, $hashAlgorithm);
        if (!$succeeded) {
            throw new \RuntimeException('sign failed.');
        }
        return $signature;
    }

    /**
     * @param string $message
     * @param string $signature
     * @param integer|null $hashAlgorithm OPENSSL_ALGO_*
     *   (if null, using DEFAULT_HASH_ALGORITHM)
     * @return boolean
     */
    public function verifySignature($message, $signature, $hashAlgorithm = null)
    {
        if (is_null($hashAlgorithm)) {
            $hashAlgorithm = static::DEFAULT_HASH_ALGORITHM;
        }
        $verified = openssl_verify($message, $signature, $this->publicKey, $hashAlgorithm);
        return ($verified == 1);
    }

    /**
     * @param string $clearText
     * @return string encrypted
     * @throws \Exception if failed
     */
    public function encrypt($clearText)
    {
        $encrypted = null;
        $succeeded = openssl_public_encrypt($clearText, $encrypted, $this->publicKey);
        if (!$succeeded) {
            throw new \RuntimeException('encrypt failed.');
        }
        return $encrypted;
    }

    /**
     * @param string $encrypted
     * @return string clearText
     * @throws \Exception if failed
     */
    public function decrypt($encrypted)
    {
        if (!($this->hasPrivateKey())) {
            throw new \RuntimeException('private key required.');
        }
        $clearText = null;
        $succeeded = openssl_private_decrypt($encrypted, $clearText, $this->privateKey);
        if (!$succeeded) {
            throw new \RuntimeException('decrypt failed.');
        }
        return $clearText;
    }

    /**
     * @param string|resource $publicKey
     * @return resource|null
     */
    public static function fetchPublicKey($publicKey)
    {
        try {
            $resource = openssl_pkey_get_public($publicKey);
            if ($resource) {
                return $resource;
            } else {
                return null;
            }
        } catch (\Exception $exception) {
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
        try {
            $resource = openssl_pkey_get_private($privateKey, $passphrase);
            if ($resource) {
                return $resource;
            } else {
                return null;
            }
        } catch (\Exception $exception) {
            return null;
        }
    }
}
