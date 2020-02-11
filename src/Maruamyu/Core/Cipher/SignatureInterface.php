<?php

namespace Maruamyu\Core\Cipher;

/**
 * signature interface
 */
interface SignatureInterface
{
    /**
     * @return bool true if enable makeSignature()
     */
    public function canMakeSignature();

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
     * @return bool true if valid signature
     */
    public function verifySignature($message, $signature, $hashAlgorithm = null);
}
