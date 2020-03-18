<?php

namespace Maruamyu\Core;

class UuidTest extends \PHPUnit\Framework\TestCase
{
    public function test_generate()
    {
        $expectsRegexp = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/u';
        $generated = [];
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::generate();
            $this->assertRegExp($expectsRegexp, $uuid);
            $this->assertArrayNotHasKey($uuid, $generated);
            $generated[$uuid] = $i;
        }
    }

    public function test_generateV4()
    {
        $expectsRegexp = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/u';
        $generated = [];
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::generateV4();
            $this->assertRegExp($expectsRegexp, $uuid);
            $this->assertArrayNotHasKey($uuid, $generated);
            $generated[$uuid] = $i;
        }
    }
}
