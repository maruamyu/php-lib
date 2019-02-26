<?php

namespace Maruamyu\Core\Cipher;

/**
 * encryption interface
 */
interface EncryptionInterface
{
    /**
     * @param string $clearText
     * @return string encrypted
     */
    public function encrypt($clearText);

    /**
     * @param string $encrypted
     * @return string clearText
     */
    public function decrypt($encrypted);
}
