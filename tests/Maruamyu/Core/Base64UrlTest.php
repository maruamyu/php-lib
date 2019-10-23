<?php

namespace Maruamyu\Core;

class Base64UrlTest extends \PHPUnit\Framework\TestCase
{
    public function test_encode()
    {
        # "\xFB\xFB" -> "+/s="
        $fixture = chr(0xFB) . chr(0xFB);
        $this->assertEquals('-_s', Base64Url::encode($fixture));
        $this->assertEquals('-_s=', Base64Url::encode($fixture, true));
    }

    public function test_decode()
    {
        # "+/s=" -> "\xFB\xFB"
        $expected = chr(0xFB) . chr(0xFB);
        $this->assertEquals($expected, Base64Url::decode('-_s'));
        $this->assertEquals($expected, Base64Url::decode('-_s='));
    }
}
