<?php

namespace Maruamyu\Core\OAuth2;

class JsonWebKeyTest extends \PHPUnit\Framework\TestCase
{
    public function test_createFromPublicKey()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            $ecdsaJsonWebKey = JsonWebKey::createFromPublicKey(self::ECDSA_PUBLIC_KEY);
            $this->assertEquals('EC', $ecdsaJsonWebKey->getKeyType());
            $this->assertFalse($ecdsaJsonWebKey->hasPrivateKey());
            $this->assertFalse($ecdsaJsonWebKey->canMakeSignature());
        }

        $rsaJsonWebKey = JsonWebKey::createFromPublicKey(self::RSA_PUBLIC_KEY);
        $this->assertEquals('RSA', $rsaJsonWebKey->getKeyType());
        $this->assertFalse($rsaJsonWebKey->hasPrivateKey());
        $this->assertFalse($rsaJsonWebKey->canMakeSignature());
    }

    public function test_createFromPrivateKey()
    {
        # ECDSA required PHP >= 7.1
        if (version_compare(PHP_VERSION, '7.1.0') >= 0) {
            $ecdsaJsonWebKey = JsonWebKey::createFromPrivateKey(self::ECDSA_PRIVATE_KEY);
            $this->assertEquals('EC', $ecdsaJsonWebKey->getKeyType());
            $this->assertTrue($ecdsaJsonWebKey->hasPrivateKey());
            $this->assertTrue($ecdsaJsonWebKey->canMakeSignature());
        }

        $rsaJsonWebKey = JsonWebKey::createFromPrivateKey(self::RSA_PRIVATE_KEY);
        $this->assertEquals('RSA', $rsaJsonWebKey->getKeyType());
        $this->assertTrue($rsaJsonWebKey->hasPrivateKey());
        $this->assertTrue($rsaJsonWebKey->canMakeSignature());
    }

    public function test_createFromCommonKey()
    {
        $octJsonWebKey = JsonWebKey::createFromCommonKey('hoge');
        $this->assertEquals('oct', $octJsonWebKey->getKeyType());
        $this->assertTrue($octJsonWebKey->hasPrivateKey());
        $this->assertTrue($octJsonWebKey->canMakeSignature());
    }

    public function test_keyId()
    {
        $jsonWebKey = JsonWebKey::createFromCommonKey('hoge', 'some_key_id');
        $this->assertEquals('some_key_id', $jsonWebKey->getKeyId());
    }

    public function test_alg()
    {
        $jsonWebKey = JsonWebKey::createFromCommonKey('hoge', 'some_key_id', 'HS512');
        $this->assertEquals('HS512', $jsonWebKey->getAlgorithm());
    }

    public function test_setAlgorithm()
    {
        $jsonWebKey = JsonWebKey::createFromCommonKey('hoge', 'some_key_id', 'HS512');
        $jsonWebKey->setAlgorithm('HS256');
        $this->assertEquals('HS256', $jsonWebKey->getAlgorithm());
    }

    public function test_signature()
    {
        $private = JsonWebKey::createFromPrivateKey(self::RSA_PRIVATE_KEY);
        $public = JsonWebKey::createFromPublicKey(self::RSA_PUBLIC_KEY);

        $message = 'some_message';
        $signature = $private->makeSignature($message);
        $this->assertTrue($public->verifySignature($message, $signature));
    }

    public function test_toArray()
    {
        $jsonWebKey = JsonWebKey::createFromPublicKey(self::RSA_PUBLIC_KEY);
        $expects = [
            'kty' => 'RSA',
            'alg' => 'RS256',
            'n' => 'xEOxvGr15r4HaiZ1vHpNUmbnDlx7OPWaQV76zoQqXuFTA827x8JytbkgSFjl-PmCorPfmjcTNcwEMZ4p1jN8EoEfnreLHc8jLI42WtFWT-ayhy5CIrLN1-Raew3em1lnokNDS6zP88UK8LY_V1LuUk1BpAuiNgUYZIOngIQlM-0',
            'e' => 'AQAB',
            'kid' => 't5DmJPw7e8ph9sy4O8Nexjtyg0x_TAV4zmBpKPelSPY',
            'key_ops' => 'verify',
        ];
        $this->assertEquals($expects, $jsonWebKey->toArray());
    }

    const ECDSA_PRIVATE_KEY = <<<__EOS__
-----BEGIN EC PRIVATE KEY-----
MIHbAgEBBEHuKq7myar9kjmGy3MEoi6uWlJMmO7EefpYwuAjvJbu736Sqj+1uH+c
M/FJBVElcO1zlkhKMkzsgtnjOSV2MRIa9qAHBgUrgQQAI6GBiQOBhgAEAUhvNTjv
z/9AFX4+olJZAidHpPjBV/6wcsQ6+besaVFRiiuQYkWAqwE8Dhf+hKSbUl542p1k
Id/wj3lL0l00+h+fALaUxy7XajedmggQKe0Ea+emWLZPHqD4NIkKMSdbo8cFPsIY
gWzulp5nDm1KGGrDrwGTw2SbLN3IlpGNPFQz+IX/
-----END EC PRIVATE KEY-----
__EOS__;

    const ECDSA_PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIGbMBAGByqGSM49AgEGBSuBBAAjA4GGAAQBSG81OO/P/0AVfj6iUlkCJ0ek+MFX
/rByxDr5t6xpUVGKK5BiRYCrATwOF/6EpJtSXnjanWQh3/CPeUvSXTT6H58AtpTH
LtdqN52aCBAp7QRr56ZYtk8eoPg0iQoxJ1ujxwU+whiBbO6WnmcObUoYasOvAZPD
ZJss3ciWkY08VDP4hf8=
-----END PUBLIC KEY-----
__EOS__;

    const RSA_PRIVATE_KEY = <<<__EOS__
-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDEQ7G8avXmvgdqJnW8ek1SZucOXHs49ZpBXvrOhCpe4VMDzbvH
wnK1uSBIWOX4+YKis9+aNxM1zAQxninWM3wSgR+et4sdzyMsjjZa0VZP5rKHLkIi
ss3X5Fp7Dd6bWWeiQ0NLrM/zxQrwtj9XUu5STUGkC6I2BRhkg6eAhCUz7QIDAQAB
AoGAKW6M99SwooxdLlh+JFLBPfMBNfPqA2U9si0lzzDxbOQuTTBCQvJWmuxA12UE
72Fk5YoJWxnjUUkHXZ4hANoPh83cYwOu2wwfRuq1bxgTMck7qqsH2UGyl+h79n4/
0nxo0SD+dQZXnP8hE2H/+6xI04oLc7aTtBwhctBqHqecmPUCQQDhhbKE7bmNtnow
sHG4vDw5Lb1lxEJSy9ihjfwmVNKe0GK//zdo58NCu8+c8sFzBqZELdb79zIrU6Rn
CNRWzYqfAkEA3snGmMIW8naAxoHTKOE+um0Mt40ZiqltPv1KZ77D7p4tU7gGCiBX
nL/rQFqZT7bTRBFOt31fObgKEvGMvoAB8wJBAMkI+qNemxsFwJTopOdt/S1nZb9z
HUBbcMhLHqw4zuw9jNnkM0uz1i8F5sPc7q+QDOUYC93edP2EeThT+Z1LarcCQHUB
PZbuoESYrgsTFcYrfdlE+l/P7/EeCC0Ds7cGvjoswptsU2ewErNVLAUxT8FIwG3I
NTIy2vciS9AlIgjOi7cCQGd8Nfxv9eqBXA9VVOUB3IvBwDUMWfB4Ah7qfrizNGvT
8JyarASoBZTBxdiNjRbED9cx6ap6rcj80F5dplQLjJ8=
-----END RSA PRIVATE KEY-----
__EOS__;

    const RSA_PUBLIC_KEY = <<<__EOS__
-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDEQ7G8avXmvgdqJnW8ek1SZucO
XHs49ZpBXvrOhCpe4VMDzbvHwnK1uSBIWOX4+YKis9+aNxM1zAQxninWM3wSgR+e
t4sdzyMsjjZa0VZP5rKHLkIiss3X5Fp7Dd6bWWeiQ0NLrM/zxQrwtj9XUu5STUGk
C6I2BRhkg6eAhCUz7QIDAQAB
-----END PUBLIC KEY-----
__EOS__;
}
