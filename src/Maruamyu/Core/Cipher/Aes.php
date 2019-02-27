<?php

namespace Maruamyu\Core\Cipher;

/**
 * AES cryptography
 */
class Aes implements EncryptionInterface
{
    const SUPPORTED_KEY_LENGTH = [128, 192, 256];

    const SUPPORTED_BLOCK_SIZE = [
        128 => 'openssl',
        192 => 'mcrypt',
        256 => 'mcrypt',
    ];

    /** @var string */
    protected $key;

    /** @var integer */
    protected $keyLength;

    /** @var integer */
    protected $blockSize;

    /**
     * @param string $key
     * @param integer $blockSize
     */
    public function __construct($key, $blockSize = 128)
    {
        $this->setKey($key);
        $this->setBlockSize($blockSize);
    }

    /**
     * @param string $key
     * @throws \InvalidArgumentException if invalid key length
     */
    public function setKey($key)
    {
        $keyLength = strlen($key) * 8;
        if (in_array($keyLength, static::SUPPORTED_KEY_LENGTH) == false) {
            $errorMsg = 'unsupported key length = ' . $keyLength
                . ' (expects: ' . join(', ', static::SUPPORTED_KEY_LENGTH) . ')';
            throw new \InvalidArgumentException($errorMsg);
        }
        $this->key = $key;
        $this->keyLength = $keyLength;
    }

    /**
     * @param int $blockSize
     * @throws \InvalidArgumentException if invalid block size
     */
    public function setBlockSize($blockSize)
    {
        $supportedBlockSize = static::SUPPORTED_BLOCK_SIZE;

        if (isset($supportedBlockSize[$blockSize]) == false) {
            $errorMsg = 'unsupported block size = ' . $blockSize
                . ' (expects: ' . join(', ', array_keys($supportedBlockSize)) . ')';
            throw new \InvalidArgumentException($errorMsg);
        }

        $usingExtension = $supportedBlockSize[$blockSize];
        if (!extension_loaded($usingExtension)) {
            $errorMsg = 'block size = ' . $blockSize . ' required ' . $usingExtension . ' extension.';
            throw new \InvalidArgumentException($errorMsg);
        }

        $this->blockSize = $blockSize;
    }

    /**
     * @param string $clearText
     * @param string $iv
     * @return string encrypted
     */
    public function encrypt($clearText, $iv = '')
    {
        if (strlen($iv) > 0) {
            # check IV length
            $ivLength = strlen($iv) * 8;
            if ($ivLength != $this->blockSize) {
                $errorMsg = 'invalid IV length = ' . $ivLength . ' (expects: ' . $this->blockSize . ')';
                throw new \InvalidArgumentException($errorMsg);
            }
        } else {
            # IV create from key (insecure!!)
            $iv = substr($this->key, 0, ($this->blockSize / 8));
        }

        $usingExtension = static::SUPPORTED_BLOCK_SIZE[$this->blockSize];
        if ($usingExtension == 'mcrypt') {
            # using mcrypt
            $cipher = constant('MCRYPT_RIJNDAEL_' . $this->blockSize);
            return rtrim(@mcrypt_encrypt($cipher, $this->key, $clearText, MCRYPT_MODE_CBC, $iv), "\0");
        } else {
            # using openssl
            $cipherMethod = 'AES-' . $this->keyLength . '-CBC';
            return openssl_encrypt($clearText, $cipherMethod, $this->key, OPENSSL_RAW_DATA, $iv);
        }
    }

    /**
     * @param string $encrypted
     * @param string $iv
     * @return string clearText
     */
    public function decrypt($encrypted, $iv = '')
    {
        if (strlen($iv) > 0) {
            # check IV length
            $ivLength = strlen($iv) * 8;
            if ($ivLength != $this->blockSize) {
                $errorMsg = 'invalid IV length = ' . $ivLength . ' (expects: ' . $this->blockSize . ')';
                throw new \InvalidArgumentException($errorMsg);
            }
        } else {
            # IV create from key (insecure!!)
            $iv = substr($this->key, 0, ($this->blockSize / 8));
        }

        $usingExtension = static::SUPPORTED_BLOCK_SIZE[$this->blockSize];
        if ($usingExtension == 'mcrypt') {
            # using mcrypt
            $cipher = constant('MCRYPT_RIJNDAEL_' . $this->blockSize);
            return rtrim(@mcrypt_decrypt($cipher, $this->key, $encrypted, MCRYPT_MODE_CBC, $iv), "\0");
        } else {
            # using openssl
            $cipherMethod = 'AES-' . $this->keyLength . '-CBC';
            return openssl_decrypt($encrypted, $cipherMethod, $this->key, OPENSSL_RAW_DATA, $iv);
        }
    }
}
