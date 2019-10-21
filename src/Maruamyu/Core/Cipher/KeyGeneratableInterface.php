<?php

namespace Maruamyu\Core\Cipher;

interface KeyGeneratableInterface
{
    /**
     * @param string|null $passphrase
     * @return static
     * @throws \Exception if error
     */
    public static function generateKey($passphrase = null);
}
