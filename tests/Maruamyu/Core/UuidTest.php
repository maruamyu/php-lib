<?php

namespace Maruamyu\Core;

class UuidTest extends \PHPUnit\Framework\TestCase
{
    public function test_generate()
    {
        $expectsRegExp = '/^[0-9A-F]{8}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{4}-[0-9A-F]{12}$/u';
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
        $this->assertRegExp($expectsRegExp, Uuid::generate());
    }

    public function test_generateV4()
    {
        $expectsRegExp = '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/u';
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
        $this->assertRegExp($expectsRegExp, Uuid::generateV4());
    }
}
