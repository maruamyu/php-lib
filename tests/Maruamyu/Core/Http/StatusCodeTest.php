<?php

namespace Maruamyu\Core\Http;

class StatusCodeTest extends \PHPUnit\Framework\TestCase
{
    public function test_getCode()
    {
        $statusCode = new StatusCode(200);
        $this->assertEquals(200, $statusCode->getCode());
    }

    public function test_isOk_200()
    {
        $statusCode = new StatusCode(200);
        $this->assertTrue($statusCode->isOk());
    }

    public function test_isOk_not200()
    {
        $statusCode = new StatusCode(404);
        $this->assertFalse($statusCode->isOk());
    }

    public function test_getReasonPhrase_auto()
    {
        $statusCode = new StatusCode(202);
        $this->assertEquals('Accepted', $statusCode->getReasonPhrase());
    }

    public function test_getReasonPhrase_manual()
    {
        $statusCode = new StatusCode(200, 'OK, OK, Dounts OK!');
        $this->assertEquals('OK, OK, Dounts OK!', $statusCode->getReasonPhrase());
    }

    public function test_toReasonPhrase()
    {
        $this->assertEquals('Payment Required', StatusCode::toReasonPhrase(402));
    }
}
