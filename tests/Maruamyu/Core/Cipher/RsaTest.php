<?php

namespace Maruamyu\Core\Cipher;

class RsaTest extends \PHPUnit\Framework\TestCase
{
    const PRIVATE_KEY = <<<__EOS__
-----BEGIN RSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-CBC,801A15CFA3CA5D28

AF+y0kJOMEcQJefHMGvwds8Wgy5a3eLDY4FAxEG4d7uWGkVRaPPSqATBhBFMrvId
43fAv0BDUHq/DpEq5HIjqalPr+xKGpcigCzeiZMRnpK984FFSAYVOH6B0z/QdU2a
BiPv/PuRG1/tLF6xQufTzoM09E7F1a3Paei1oTJkPVZ04paekgoJBBVTAfVAc1Vt
rLNOKcV6UiHtQVob43c+AzzBYxrPqGi6m6Tk+LaR78WCRq81PC0ib6t85VjtdbDX
qRZqEjozsBYwITuyYu64qjWebEwPszIISCl2yzsPE4w6V0CQ3PSAZnXpJ/NdDDXD
SPkDssKVisGtXTIOtatjpDbRFNDy69MT9C3BVQJiBWoWUPeI3ze68j0EOwXQOlng
Yr6AzLN+YoSYoU/RJbMGpXHW0Cht4XSZ19gRKIkg/WJqBhslReWASik1obBRJBTL
Jfar3+IG1usUX40e4/s7VfiAH3+BsfBmiId/esbVdwMSMnRYikNWWl6ALdqRFDU9
C54NpoM5xMGqCmxZ0JccNS7/+dCQvfHtk382KzX376eOunuzPwA0hDRn4KZblR3Z
Zzrlu1/za21wmbQKzXsGWHsOX7GecQq32enjWytQ7N9ElYYe1fws7vli0t+DopqS
WCWgjFr1aRCavQjFGnfIQ5XQgaHmwQm4VYGIYK7jjACOTdKF3cg9RcBYdg12OF2X
N06MitxISY6Sxr++51fEvln/XookTrOXf24wiop5kEURJtk1PF4sUePL0pzKmRbJ
ipxUU8aGKxAt87cFeyVCncE+nRj6lUkddkLAe/c0dLftOddYh2vAdAq5M2nWrg4f
EJzRAFl3M6JUGmK3HqoHofoeccDg5/+T9TzfTFKtChFbinYWQs2LrEjNyR4mVSNg
vC/1xRmQzVJbRT6io3lkGpGxu6doxUKn15fVZhNjlo5UnbmQrh95n5f3fh46Ix5B
rR7U65VpXRuxuutVbpHfHvOl6zhAoVsE2+rVPbXnGLSDNz/3qea/0jN33K7Oea1y
AiNryf1A4wgAl9VU1aPMaLjHN2WTrXpAm0Na9pcQxN6fez6Dw9zrVhgiGnPsuCC2
u/jKWLcZt6e7/pEPr5oMvwBb85GBBUn78oRdU5F1zyvQR2Mx3qsow628XBe/5Qxt
Cm6bpUu2nr91ZSZANscXOrr42X0F71f7GohbjjrVWd6C8edHD8Ir13IhS7dhX+Ao
bcbRVP43fCUhWb7fhjfYRLoCuDTVjKuKSRJ36alOj8uTpd2jYgQj9ca/2S46/vhO
zcdyj7Ud7Zxpn7jXhGnRkC4iJZWBVxCtrTOZQVBj6R70UpWqh66XYqhdUDro+ywZ
If6lATgFujeqzpW4nJAbgjC2I9Fn/mSW0EsE1AQwy5XM627ygOHWBaS/MPX/Z6o9
T6iKJjyzqB5blC3iI1TflQhU2dHekkeGc55NJJNoGQScYugItg0RZ3gMvhQrHzFk
ZNw9va2tSMixmUf3bqCIrBqpDDcuMZUDoDT4fwdjhe0P50Rsluvu6aDxNmdslm+C
NwoQy95fYmijKnII6223djt2rf/Re0Ty23695kn/tsBaOW0ymR3KJi1QvDHHFquP
-----END RSA PRIVATE KEY-----
__EOS__;

    const PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA6nMlrRFuT7fjTe0k6sIn
iS8aJHpdk6S5zXnKEnyKnf0kuhuJcB+ta935SmpZ0m7CxfLOqnSYfX8ahsd6iVZm
POxUR0cbTAPRrlFYiSwRFNNINhTww/OtnNJXGDD/wG2pd2vkbIYFSDNSakWH1mE3
l5Cbbl1TXmQRJEAp9bkDf3pA8tQpkDPkJzcru1ddUuY+A6r7lnvhIKIAXiqUpQgl
wuM6yy5UAZ5DEMBkfuM2HJxCEdmIZ/dpiiCpFnW80j663vhasnAdeNj2cn8v6izz
PekZ7sFtpgS7N9w1FlZ1p9fTKlJKLC+5mSC6cpuvHuhM81tcCnKT6H2JWecuezby
LwIDAQAB
-----END PUBLIC KEY-----
__EOS__;

    const PASSPHRASE = 'passphrase';

    public function test_encrypt_decrypt()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $cleartext = '秘密のメモリーズ / 四条貴音, 豊川風花';
        $encrypted = $public->encrypt($cleartext);
        $this->assertEquals($cleartext, $private->decrypt($encrypted));

        $rawPrivateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateFromRawKey = new Rsa(null, $rawPrivateKey);
        $this->assertEquals($cleartext, $privateFromRawKey->decrypt($encrypted));
    }

    public function test_sign_verify()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message);
        $this->assertTrue($public->verifySignature($message, $signature));

        $rawPrivateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateFromRawKey = new Rsa(null, $rawPrivateKey);
        $signature = $privateFromRawKey->makeSignature($message);
        $this->assertTrue($public->verifySignature($message, $signature));
    }

    public function test_sign_verify_algo()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $hashAlgorithm = OPENSSL_ALGO_SHA512;

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message, $hashAlgorithm);
        $this->assertTrue($public->verifySignature($message, $signature, $hashAlgorithm));

        $rawPrivateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateFromRawKey = new Rsa(null, $rawPrivateKey);
        $signature = $privateFromRawKey->makeSignature($message, $hashAlgorithm);
        $this->assertTrue($public->verifySignature($message, $signature, $hashAlgorithm));
    }

    public function test_hasPrivateKey()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        $this->assertFalse($public->hasPrivateKey());

        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);
        $this->assertTrue($private->hasPrivateKey());
    }

    public function test_getPublicKeyDetail_public()
    {
        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $expects = openssl_pkey_get_details($publicKey);

        $public = new Rsa(self::PUBLIC_KEY);
        $this->assertEquals($expects, $public->getPublicKeyDetail());

        $publicFromRawKey = new Rsa($publicKey);
        $this->assertEquals($expects, $publicFromRawKey->getPublicKeyDetail());
    }

    public function test_getPublicKeyDetail_private()
    {
        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $expects = openssl_pkey_get_details($publicKey);

        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);
        $this->assertEquals($expects, $private->getPublicKeyDetail());

        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateFromRawKey = new Rsa(null, $privateKey);
        $this->assertEquals($expects, $privateFromRawKey->getPublicKeyDetail());
    }

    public function test_getPrivateKeyDetail()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        try {
            $public->getPrivateKeyDetail();
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $publicFromRawKey = new Rsa($publicKey);
        try {
            $publicFromRawKey->getPrivateKeyDetail();
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $expects = openssl_pkey_get_details($privateKey);

        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);
        $this->assertEquals($expects, $private->getPrivateKeyDetail());

        $privateFromRawKey = new Rsa(null, $privateKey);
        $this->assertEquals($expects, $privateFromRawKey->getPrivateKeyDetail());
    }

    public function test_getPrivateKeyDetail_public()
    {
        $public = new Rsa(self::PUBLIC_KEY);
        try {
            $public->getPrivateKeyDetail();
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $publicFromRawKey = new Rsa($publicKey);
        try {
            $publicFromRawKey->getPrivateKeyDetail();
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_getPrivateKeyDetail_private()
    {
        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $expects = openssl_pkey_get_details($privateKey);

        $private = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);
        $this->assertEquals($expects, $private->getPrivateKeyDetail());

        $privateFromRawKey = new Rsa(null, $privateKey);
        $this->assertEquals($expects, $privateFromRawKey->getPrivateKeyDetail());
    }

    public function test_public_key_toString()
    {
        $expects = trim(self::PUBLIC_KEY);

        $instance = new Rsa(self::PUBLIC_KEY);
        $actual = trim(strval($instance));
        $this->assertEquals($expects, $actual);

        $rawPublicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $instanceFromRawKey = new Rsa($rawPublicKey);
        $actual = trim(strval($instanceFromRawKey));
        $this->assertEquals($expects, $actual);
    }

    public function test_private_key_toString()
    {
        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $expects = openssl_pkey_get_details($privateKey);

        # compare old format - new format
        $instance = new Rsa(null, self::PRIVATE_KEY, self::PASSPHRASE);
        $actualPem = strval($instance);
        $actualKey = openssl_pkey_get_private($actualPem, self::PASSPHRASE);
        $actual = openssl_pkey_get_details($actualKey);
        $this->assertEquals($expects, $actual);

        $instanceFromRawKey = new Rsa(null, $privateKey);
        $actualPem = strval($instanceFromRawKey);
        $actualKey = openssl_pkey_get_private($actualPem);
        $actual = openssl_pkey_get_details($actualKey);
        $this->assertEquals($expects, $actual);
    }

    public function test_invalid_key()
    {
        # wrong format (public)
        try {
            $public = new Rsa(DsaTest::PUBLIC_KEY);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        # wrong format (private)
        try {
            $private = new Rsa(DsaTest::PRIVATE_KEY, DsaTest::PASSPHRASE);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        # wrong passphrase
        try {
            $private = new Rsa(self::PRIVATE_KEY);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_publicKeyFromModulusAndExponent()
    {
        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $publicKeyDetails = openssl_pkey_get_details($publicKey);

        $modulus = $publicKeyDetails['rsa']['n'];
        $exponent = $publicKeyDetails['rsa']['e'];
        $genPublicKey = Rsa::publicKeyFromModulusAndExponent($modulus, $exponent);
        $genDetails = openssl_pkey_get_details($genPublicKey);
        $this->assertEquals($publicKeyDetails, $genDetails);
    }

    public function test_privateKeyFromParameters()
    {
        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateKeyDetails = openssl_pkey_get_details($privateKey);

        $genPrivateKey = Rsa::privateKeyFromParameters($privateKeyDetails['rsa']);
        $genDetails = openssl_pkey_get_details($genPrivateKey);
        $this->assertEquals($privateKeyDetails, $genDetails);
    }
}
