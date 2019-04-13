<?php

namespace Maruamyu\Core\Cipher;

class DsaTest extends \PHPUnit\Framework\TestCase
{
    const PRIVATE_KEY = <<<__EOS__
-----BEGIN DSA PRIVATE KEY-----
Proc-Type: 4,ENCRYPTED
DEK-Info: DES-EDE3-CBC,998D2BCE83C36CF9

A5diSxV1GfY/Z6psyX4R5iwcz0ZHiuUZAvKtPoLvxIhO5nNbJHhdGHsHpGHSh6hL
qLMwYmE04hHYs6L0C5UtorcTN0RBZ38ihh5VMKRbO5WG+ZwBGULY8UdD9pdyDkg4
mRaUrlUGS5hqLs4OfvWFrOZ8vGj8mtiTOUZuhezA+KXDDCyuMMypeBwC6NkRy3a7
Weu4kZbKVnkxtlqwqIV1T6m9GFxzvGm7KL/pSABVuuLlVvIekYNqZC4E8lKOYid1
/91UIN0d8JN7cF/BiqokAVimnwO7tZT997mDsPZz9BfEVjxzdec/8ennaYumSlox
/y4OHtrco4D2PghcgNJ5ZrGUnHaqjZJdiv56+0NaxyBh2hOOTz8kklEoU2SqTBMr
Vx2mSDKvr90AXFTlv1ec/vEaCjcmkxb7bmxGmIqTYMpjuBTH5ttx1DUGR2CntAt8
pOzuBSh2Gofp14hF7r7XcEFJr9Kl4Cn7kS7PcTVYaM9bSibmyxvBfQNy5isr0sRU
xkoguOAPvnrN4JzI+mTnJZi0J/CrVQ6C09cWZWSGkeh+7ybbU60M4ypkPRTlA6cP
DbXCpETgAM4vMXLNN/Q03WEVOj3ROii7ym8n01DQs4fuwhsIonKNVcQx2cQMTIYV
QR/4FrENxJ4HRus0k4C+S/uycEqyvhVeBn1GNaVRL7jI7V7wBp5h7Yu232S+y3/7
WrT/DV8bdNr27mYqJbksbfJq2VIDIWKkqPzQCyk294b7s+U2wvUCx37Ctu8vHG+d
74E7uxsVDe7ItWxeArcy0IujBV9AcpC/YZr5RJ5A7ZtJHsVwYC2MDdzNjQdRx2Px
jsMYaU87yqi3I3BDK0NHJj4PxbIjJV/zwNyvaqdhnIvnFs/BIpqsYD+chaeB1H+K
ZmK4YyATT0bVQCcsI+8GQQ29NdBzr/56jnMiu8TKljwsHiIDN4Fv2w/eAdhibzFy
sjVyGdHEZ04NNfjTEkWwq7XgVcM8XeAg9Mo/myZZBcLo3pOaRVpNrxb4yDyxPvYO
Pe4q3DHCoRN4ZYNeQaIC3LDNzLKS3VGdaXkHCkAZtLwNrT7T4sSvrFWOnuTOop/O
C7Lj1j1v3uSZ0vvcsc9+JoiOd1DuQAw2oVD/vIa7HZ5a7GLn01wjBVS6IJA/Tps1
-----END DSA PRIVATE KEY-----
__EOS__;

    const PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIIDRzCCAjkGByqGSM44BAEwggIsAoIBAQCTjWXAjnFUGWCCKKpmaJXBfaJ2m7XZ
sv1nbbAFVbaaOQnztMVxVAMp/HAQ5RwRkR4ZdBzn66YX38ZvWNRBq4+w81wci98Z
4IkKEdQp+PiVC/SurIZzAgV0pW8mr5XECMMuwIy2WLf+SYuW3FI4PA1Vuuz97jLA
0R2w3zeNuBYT/nV24PyzFvqpwPRlXRIfZXsEfMbaW8kie9Q7j+PkRQ/DnBjs5+aC
1mz6C0xVkJh49VCzVqPRmJoU4YDwi4n+RUCGyyE4m35ZXbHhkloOhE6wMB4Yxuip
UEBxEgEaDnEJRLfibLrKVBuMJMlV5yw2JgLgFUMNqS0ITQW/xriOHaqxAiEAhx7E
4c4ca3+s+Ag5WW1e8PSM3NRmt1lLjzZJ5cYVrt8CggEAAh7ryS47SJDzYw8Dg/PS
iZ4zTVvmVijQ4Z3aPm1kaoUppvQC8AMyJjw4ZS6EojjENxtIoGeyZun/IKXyIqlM
wzdLuUyIrZV7GExz/Mf3BA/p/nPtS43wtieQU6/MkWUZTJiRBepVbfXcUeTpClEU
ZrQAQSrynsyM8njNq9aGf5yKrUOfSCyxbckd6SZe36MS0qevvjhZ+siSahxBYKU7
887SonWNZz70/K6KqrMNbXvEpRlwZ4s8hb1RgZNq6gY54hECaqvb8BZ5shm3IB+m
R91WiH0WJdPba0bCOabmby0z12RKYYW7aJFOqcaNDSFyRwAnc6aM3YDzCPObWBUV
qwOCAQYAAoIBAQCE4E81swXL+fYdv7oSGszePXQPfc0Y8m8gw1+vayqiopvtoaAT
dzgcvoBCgpNOYvO3SVCLSTFM8rkzEkh3dg7y1wt68bgJvtcRKh8XjcQ4jpTIK9ce
GdDGX4Uqzc/VksFKstak3wIyzGsbOry0gJ/wDcnVVhmqyYmB+dPndPFVhkVEpfsl
Fvz7n0t0NKFnOqyFh4MhJhetrkVQ4uJiyxuygvmDktE2+SMix+qyws8JQiRFWUSV
z2DoCG8IE4SZHDNwuEOrw2IHVhKdQEENzXbKjA4TBOPFlfqg8CFxOlRFkXqWOmaj
J1PEZC7bsGvkwH4GBLLcPBQvIPtzfo2bZOvP
-----END PUBLIC KEY-----
__EOS__;

    const PASSPHRASE = 'passphrase';

    /*
    # DSA encryption is not supported
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
        $public = new Dsa(self::PUBLIC_KEY);
        $private = new Dsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message);
        $this->assertTrue($public->verifySignature($message, $signature));
    }

    public function test_sign_verify_algo()
    {
        $public = new Dsa(self::PUBLIC_KEY);
        $private = new Dsa(null, self::PRIVATE_KEY, self::PASSPHRASE);

        $message = '虹色letters / Cleasky';
        $signature = $private->makeSignature($message, OPENSSL_ALGO_SHA512);
        $this->assertTrue($public->verifySignature($message, $signature, OPENSSL_ALGO_SHA512));
    }

    public function test_invalid_key()
    {
        # wrong format (public)
        try {
            $public = new Dsa(RsaTest::PUBLIC_KEY);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        # wrong format (private)
        try {
            $private = new Dsa(RsaTest::PRIVATE_KEY, RsaTest::PASSPHRASE);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }

        # wrong passphrase
        try {
            $private = new Dsa(self::PRIVATE_KEY);
            $this->assertTrue(false);
        } catch (\Exception $exception) {
            $this->assertTrue(true);
        }
    }

    public function test_publicKeyFromParameters()
    {
        $publicKey = openssl_pkey_get_public(self::PUBLIC_KEY);
        $publicKeyDetails = openssl_pkey_get_details($publicKey);

        $genPublicKey = Dsa::publicKeyFromParameters($publicKeyDetails['dsa']);
        $genDetails = openssl_pkey_get_details($genPublicKey);
        $this->assertEquals($publicKeyDetails, $genDetails);
    }

    public function test_privateKeyFromParameters()
    {
        $privateKey = openssl_pkey_get_private(self::PRIVATE_KEY, self::PASSPHRASE);
        $privateKeyDetails = openssl_pkey_get_details($privateKey);

        $genPrivateKey = Dsa::privateKeyFromParameters($privateKeyDetails['dsa']);
        $genDetails = openssl_pkey_get_details($genPrivateKey);
        $this->assertEquals($privateKeyDetails, $genDetails);
    }
}
