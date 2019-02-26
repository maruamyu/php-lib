<?php

namespace Maruamyu\Core\Cipher;

/**
 * HMAC utils
 */
class Hmac implements SignatureInterface
{
    /**
     * @var string
     * @see hash_algos()
     */
    const DEFAULT_HASH_ALGORITHM = 'sha256';

    /** @var string */
    protected $commonKey;

    /**
     * @param string $commonKey
     */
    public function __construct($commonKey)
    {
        $this->commonKey = $commonKey;
    }

    /**
     * @param string $message
     * @param string|null $hashAlgorithm
     * @return string signature
     */
    public function makeSignature($message, $hashAlgorithm = null)
    {
        if (is_null($hashAlgorithm)) {
            $hashAlgorithm = static::DEFAULT_HASH_ALGORITHM;
        }
        return hash_hmac($hashAlgorithm, $message, $this->commonKey, true);
    }

    /**
     * @param string $message
     * @param string $signature
     * @param string|null $hashAlgorithm
     * @return boolean true if valid signature
     */
    public function verifySignature($message, $signature, $hashAlgorithm = null)
    {
        return (strcmp($signature, $this->makeSignature($message, $hashAlgorithm)) == 0);
    }
}
