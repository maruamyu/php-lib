<?php

namespace Maruamyu\Core\Cipher;

/**
 * signature interface
 */
interface SignatureInterface
{
    /**
     * @param string $message
     * @param mixed $hashAlgorithm
     * @return string signature
     */
    public function makeSignature($message, $hashAlgorithm = null);

    /**
     * @param string $message
     * @param string $signature
     * @param mixed $hashAlgorithm
     * @return boolean true if valid signature
     */
    public function verifySignature($message, $signature, $hashAlgorithm = null);
}
