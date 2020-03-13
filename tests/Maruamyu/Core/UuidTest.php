<?php

namespace Maruamyu\Core;

class UuidTest extends \PHPUnit\Framework\TestCase
{
    const EXPECTS_REGEXP = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/u';

    public function test_generate()
    {
        $generated = [];
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::generate();
            $this->assertRegExp(self::EXPECTS_REGEXP, $uuid);
            $this->assertArrayNotHasKey($uuid, $generated);
            $generated[$uuid] = $i;
        }
    }

    public function test_generateV4()
    {
        $generated = [];
        for ($i = 0; $i < 100; $i++) {
            $uuid = Uuid::generateV4();
            $this->assertRegExp(self::EXPECTS_REGEXP, $uuid);
            $this->assertArrayNotHasKey($uuid, $generated);
            $generated[$uuid] = $i;
        }
    }
}
