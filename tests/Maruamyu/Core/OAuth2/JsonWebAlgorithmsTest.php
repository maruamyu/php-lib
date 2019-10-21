<?php

namespace Maruamyu\Core\OAuth2;

class JsonWebAlgorithmsTest extends \PHPUnit\Framework\TestCase
{
    public function test_isSupportedHashAlgorithm()
    {
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('invalid'));

        $this->assertTrue(JsonWebAlgorithms::isSupportedHashAlgorithm('HS256'));
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('HS256', 'RSA'));
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('HS256', 'EC'));
        $this->assertTrue(JsonWebAlgorithms::isSupportedHashAlgorithm('HS256', 'oct'));

        $this->assertTrue(JsonWebAlgorithms::isSupportedHashAlgorithm('RS256'));
        $this->assertTrue(JsonWebAlgorithms::isSupportedHashAlgorithm('RS256', 'RSA'));
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('RS256', 'EC'));
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('RS256', 'oct'));

        $this->assertTrue(JsonWebAlgorithms::isSupportedHashAlgorithm('ES256'));
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('ES256', 'RSA'));
        $this->assertTrue(JsonWebAlgorithms::isSupportedHashAlgorithm('ES256', 'EC'));
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('ES256', 'oct'));

        # RSASSA-PSS is not supported
        $this->assertFalse(JsonWebAlgorithms::isSupportedHashAlgorithm('PS256'));
    }

    public function test_getSupportedAlgsByKty()
    {
        $this->assertEquals(['RS256', 'RS384', 'RS512'], JsonWebAlgorithms::getSupportedAlgsByKty('RSA'));
        $this->assertEquals(['ES256', 'ES384', 'ES512'], JsonWebAlgorithms::getSupportedAlgsByKty('EC'));
        $this->assertEquals(['HS256', 'HS384', 'HS512'], JsonWebAlgorithms::getSupportedAlgsByKty('oct'));
    }

    public function test_getCrvValueFromCurveName()
    {
        $this->assertEquals('P-256', JsonWebAlgorithms::getCrvValueFromCurveName('secp256r1'));
        $this->assertEquals('P-384', JsonWebAlgorithms::getCrvValueFromCurveName('secp384r1'));
        $this->assertEquals('P-521', JsonWebAlgorithms::getCrvValueFromCurveName('secp521r1'));
        $this->assertEmpty(JsonWebAlgorithms::getCrvValueFromCurveName('invalid'));
    }

    public function test_getCurveNameFromCrvValue()
    {
        $this->assertEquals('secp256r1', JsonWebAlgorithms::getCurveNameFromCrvValue('P-256'));
        $this->assertEquals('secp384r1', JsonWebAlgorithms::getCurveNameFromCrvValue('P-384'));
        $this->assertEquals('secp521r1', JsonWebAlgorithms::getCurveNameFromCrvValue('P-521'));
        $this->assertEmpty(JsonWebAlgorithms::getCurveNameFromCrvValue('invalid'));
    }
}
