<?php

namespace Maruamyu\Core\Cipher;

/**
 * AES cryptography
 */
class Aes implements EncryptionInterface
{
    const DEFAULT_MODE = 'CBC';

    const SUPPORTED_KEY_LENGTH = [128, 192, 256];

    const SUPPORTED_BLOCK_SIZE = [
        128 => 'openssl',
        192 => 'mcrypt',
        256 => 'mcrypt',
    ];

    /** @var string */
    protected $key;

    /** @var integer (bit) */
    protected $keyLength;

    /** @var string */
    protected $mode = self::DEFAULT_MODE;

    /** @var integer */
    protected $blockSize = 128;

    /**
     * @param string $key
     * @param string $mode
     * @param integer $blockSize
     */
    public function __construct($key, $mode = self::DEFAULT_MODE, $blockSize = 128)
    {
        $this->setBlockSize($blockSize);
        $this->setKey($key);
        $this->setMode($mode);
    }

    /**
     * @param string $key
     * @throws \DomainException if invalid key length
     */
    public function setKey($key)
    {
        $keyLength = strlen($key) * 8;
        if (in_array($keyLength, static::SUPPORTED_KEY_LENGTH) == false) {
            $errorMsg = 'unsupported key length = ' . $keyLength
                . ' (expects: ' . join(', ', static::SUPPORTED_KEY_LENGTH) . ')';
            throw new \DomainException($errorMsg);
        }
        $this->key = $key;
        $this->keyLength = $keyLength;
    }

    /**
     * @param string $mode
     * @throws \DomainException if invalid mode
     */
    public function setMode($mode)
    {
        $mode = strtoupper($mode);

        $usingExtension = $this->getUsingExtension();
        if ($usingExtension == 'mcrypt') {
            if (defined('MCRYPT_MODE_' . $mode) == false) {
                $errorMsg = 'unsupported MCRYPT_MODE_' . $mode;
                throw new \DomainException($errorMsg);
            }
        } else {
            $cipherMethod = 'AES-' . $this->keyLength . '-' . $mode;
            if (in_array($cipherMethod, openssl_get_cipher_methods()) == false) {
                $errorMsg = 'unsupported cipher method = ' . $cipherMethod;
                throw new \DomainException($errorMsg);
            }
        }

        $this->mode = $mode;
    }

    /**
     * @param int $blockSize
     * @throws \DomainException if invalid block size
     */
    public function setBlockSize($blockSize)
    {
        $supportedBlockSize = static::SUPPORTED_BLOCK_SIZE;

        if (isset($supportedBlockSize[$blockSize]) == false) {
            $errorMsg = 'unsupported block size = ' . $blockSize
                . ' (expects: ' . join(', ', array_keys($supportedBlockSize)) . ')';
            throw new \DomainException($errorMsg);
        }

        $usingExtension = $supportedBlockSize[$blockSize];
        if (!extension_loaded($usingExtension)) {
            $errorMsg = 'block size = ' . $blockSize . ' required ' . $usingExtension . ' extension.';
            throw new \DomainException($errorMsg);
        }

        $this->blockSize = $blockSize;
    }

    /**
     * @param string $clearText
     * @param string $iv
     * @return string encrypted
     * @throws \Exception if invalid IV length
     */
    public function encrypt($clearText, $iv = '')
    {
        # check IV length
        $ivOctetSize = $this->getIVOctetSize();
        if (strlen($iv) != $ivOctetSize) {
            $errorMsg = 'invalid IV length = ' . strlen($iv) . ' octet (expects: ' . $ivOctetSize . ' octet)';
            throw new \DomainException($errorMsg);
        }

        $usingExtension = $this->getUsingExtension();
        if ($usingExtension == 'mcrypt') {
            $mcryptCipher = $this->getMcryptCipher();
            $mcryptMode = $this->getMcryptMode();
            return rtrim(@mcrypt_encrypt($mcryptCipher, $this->key, $clearText, $mcryptMode, $iv), "\0");
        } else {
            $opensslCipherMethod = $this->getOpensslCipherMethod();
            return openssl_encrypt($clearText, $opensslCipherMethod, $this->key, OPENSSL_RAW_DATA, $iv);
        }
    }

    /**
     * @param string $encrypted
     * @param string $iv
     * @return string clearText
     * @throws \Exception if invalid IV length
     */
    public function decrypt($encrypted, $iv = '')
    {
        # check IV length
        $ivOctetSize = $this->getIVOctetSize();
        if (strlen($iv) != $ivOctetSize) {
            $errorMsg = 'invalid IV length = ' . strlen($iv) . ' octet (expects: ' . $ivOctetSize . ' octet)';
            throw new \DomainException($errorMsg);
        }

        $usingExtension = $this->getUsingExtension();
        if ($usingExtension == 'mcrypt') {
            $mcryptCipher = $this->getMcryptCipher();
            $mcryptMode = $this->getMcryptMode();
            return rtrim(@mcrypt_decrypt($mcryptCipher, $this->key, $encrypted, $mcryptMode, $iv), "\0");
        } else {
            $opensslCipherMethod = $this->getOpensslCipherMethod();
            return openssl_decrypt($encrypted, $opensslCipherMethod, $this->key, OPENSSL_RAW_DATA, $iv);
        }
    }

    /**
     * @return string
     */
    public function makeIV()
    {
        $ivOctetSize = $this->getIVOctetSize();

        # using random_bytes() (for PHP7)
        if (function_exists('random_bytes')) {
            try {
                return random_bytes($ivOctetSize);
            } catch (\Exception $exception) {
                ;
            }
        }

        # using extension's function (for PHP5)
        $usingExtension = $this->getUsingExtension();
        if ($usingExtension == 'mcrypt') {
            return @mcrypt_create_iv($ivOctetSize);
        } else {
            return openssl_random_pseudo_bytes($ivOctetSize);
        }
    }

    /**
     * @return string 'openssl' or 'mcrypt'
     * @internal
     */
    protected function getUsingExtension()
    {
        return static::SUPPORTED_BLOCK_SIZE[$this->blockSize];
    }

    /**
     * @return integer
     * @internal
     */
    protected function getIVOctetSize()
    {
        $usingExtension = $this->getUsingExtension();
        if ($usingExtension == 'mcrypt') {
            $mcryptCipher = $this->getMcryptCipher();
            $mcryptMode = $this->getMcryptMode();
            return @mcrypt_get_iv_size($mcryptCipher, $mcryptMode);
        } else {
            $opensslCipherMethod = $this->getOpensslCipherMethod();
            return openssl_cipher_iv_length($opensslCipherMethod);
        }
    }

    /**
     * @return string (example: 'AES-128-CBC')
     * @internal
     */
    protected function getOpensslCipherMethod()
    {
        return 'AES-' . $this->keyLength . '-' . $this->mode;
    }

    /**
     * @return string MCRYPT_RIJNDAEL_*
     * @internal
     */
    protected function getMcryptCipher()
    {
        return constant('MCRYPT_RIJNDAEL_' . $this->blockSize);
    }

    /**
     * @return string MCRYPT_MODE_*
     * @internal
     */
    protected function getMcryptMode()
    {
        return constant('MCRYPT_MODE_' . $this->mode);
    }
}
