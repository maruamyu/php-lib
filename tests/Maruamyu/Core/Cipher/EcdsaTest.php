<?php

namespace Maruamyu\Core\Cipher;

class EcdsaTest extends \PHPUnit\Framework\TestCase
{
    const PRIVATE_KEY = <<<__EOS__
-----BEGIN EC PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: AES-128-CBC,5E2A5402435E3F3DC54B75AE1CB3628A

TZq+Bm/DVDgCRlwGhxJtzRw4P+Zhtd+gtmdMOHp0fbA9IpZxnXNGgTPwzBdu1fN5
XDLfKyVNY3fDuuUnn/J4Sa/5B/dTjmRsmONhlCmQk0RqcTWiZ6fExs1/Gk+yoJhI
lY+TAIApd2ySw5fs/xC658m2bDt9Ci6KgmtD0PiqIy4=
-----END EC PRIVATE KEY-----
__EOS__;

    const PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MFYwEAYHKoZIzj0CAQYFK4EEAAoDQgAEOW68BONog32Eb+E6SCkU2H65uFLENevz
ZsrfvXtfm8dtAy2L9rnRkV4WirZXCT4d+PFn/74peb6jroj0Kgqazg==
-----END PUBLIC KEY-----
__EOS__;

    const PASSPHRASE = 'passphrase';

    /*
    # Ecdsa encryption is not supported
    public function test_encrypt_decrypt()
    {
        $public = new Dsa(self::PUBLIC_KEY);
        $private = new Dsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $cleartext = '秘密のメモリーズ / 四条貴音, 豊川風花';
        $encrypted = $public->encrypt($cleartext);
        $this->assertEquals($cleartext, $private->decrypt($encrypted));
    }
    */

    public function test_sign_verify()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        $public = new Ecdsa(self::PUBLIC_KEY);
        $private = new Ecdsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message);
        $this->assertTrue($public->verifySignature($message, $signature));
    }

    public function test_sign_verify_algo()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        $public = new Ecdsa(self::PUBLIC_KEY);
        $private = new Ecdsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message, OPENSSL_ALGO_SHA512);
        $this->assertTrue($public->verifySignature($message, $signature, OPENSSL_ALGO_SHA512));
    }

    public function test_public_key_toString()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        $public = new Ecdsa(self::PUBLIC_KEY);
        $publicKeyPem = strval($public);

        $expects = trim(self::PUBLIC_KEY);
        $actual = trim($publicKeyPem);
        $this->assertEquals($expects, $actual);
    }

    public function test_private_key_toString()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        $private = new Ecdsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        # compare old format - new format
        $privateKeyPem = strval($private);
        $privateKey = openssl_pkey_get_private($privateKeyPem, self::PASSPHRASE);
        $privateKeyDetail = openssl_pkey_get_details($privateKey);

        $this->assertEquals($private->getPrivateKeyDetail(), $privateKeyDetail);
    }

    public function test_invalid_key()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        # wrong format (public)
        try {
            $public = new Ecdsa(RsaTest::PUBLIC_KEY);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        # wrong format (private)
        try {
            $private = new Ecdsa(RsaTest::PRIVATE_KEY, RsaTest::PASSPHRASE);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        # wrong passphrase
        try {
            $private = new Ecdsa(self::PRIVATE_KEY);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_publicKeyFromCurveXY()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $publicKeyDetails = openssl_pkey_get_details($publicKey);

        $genPublicKey = Ecdsa::publicKeyFromCurveXY($publicKeyDetails['ec']['curve_name'],
            $publicKeyDetails['ec']['x'], $publicKeyDetails['ec']['y']);
        $genDetails = openssl_pkey_get_details($genPublicKey);
        $this->assertEquals($publicKeyDetails, $genDetails);
    }

    public function test_privateKeyFromCurveXYD()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') < 0) {
            $this->assertTrue(true);
            return;
        }

        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateKeyDetails = openssl_pkey_get_details($privateKey);

        $genPrivateKey = Ecdsa::privateKeyFromCurveXYD($privateKeyDetails['ec']['curve_name'],
            $privateKeyDetails['ec']['x'], $privateKeyDetails['ec']['y'], $privateKeyDetails['ec']['d']);
        $genDetails = openssl_pkey_get_details($genPrivateKey);
        $this->assertEquals($privateKeyDetails, $genDetails);
    }
}
