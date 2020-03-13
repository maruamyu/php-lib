<?php

namespace Maruamyu\Core;

class RandomizerTest extends \PHPUnit\Framework\TestCase
{
    public function test_randomInt()
    {
        $dice = Randomizer::randomInt(1, 6);
        # $this->assertIsInt($dice);
        $this->assertTrue(is_integer($dice));

        for ($i = 0; $i < 1000; $i++) {
            $dice = Randomizer::randomInt(1, 6);
            $this->assertGreaterThanOrEqual(1, $dice);
            $this->assertLessThanOrEqual(6, $dice);
        }
    }

    public function test_randomInt_invalid()
    {
        # for PHP < 7.0
        if (version_compare(PHP_VERSION, '7.0.0') < 0) {
            $this->assertTrue(true);
            return;
        }
        try {
            Randomizer::randomInt(1, 0);
            $this->assertTrue(false);
        } catch (\Error $error) {
            $this->assertTrue(true);
        }
    }

    public function test_randomBytes()
    {
        $token = Randomizer::randomBytes(32);
        # $this->assertIsString($token);
        $this->assertTrue(is_string($token));
        $this->assertEquals(32, strlen($token));
    }

    public function test_randomBytes_invalid()
    {
        # for PHP < 7.0
        if (version_compare(PHP_VERSION, '7.0.0') < 0) {
            $this->assertTrue(true);
            return;
        }
        try {
            Randomizer::randomBytes(0);
            $this->assertTrue(false);
        } catch (\Error $error) {
            $this->assertTrue(true);
        }
    }

    public function test_generateToken()
    {
        $token = Randomizer::generateToken(32);
        # $this->assertIsString($token);
        $this->assertTrue(is_string($token));
        $this->assertEquals(32, strlen($token));
    }

    public function test_generateUuid()
    {
        $expectsRegExp = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/u';
        $this->assertRegExp($expectsRegExp, Randomizer::generateUuid());
    }

    public function test_generateUlid()
    {
        $expectsRegExp = '/^[0123456789ABCDEFGHJKMNPQRSTVWXYZ]{26}$/u';
        $this->assertRegExp($expectsRegExp, Randomizer::generateUlid());
    }
}
