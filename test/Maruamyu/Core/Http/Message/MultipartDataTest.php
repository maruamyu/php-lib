<?php

namespace Maruamyu\Core\Http\Message;

class MultipartDataTest extends \PHPUnit_Framework_TestCase
{
    public function test_name()
    {
        $multipartData = new MultipartData('1st_maihama', Stream::fromTemp());
        $this->assertEquals('1st_maihama', $multipartData->getName());
    }

    public function test_emptyName()
    {
        $this->expectException('InvalidArgumentException');
        new MultipartData('', Stream::fromTemp());
    }

    public function test_stream()
    {
        $multipartData = new MultipartData('2nd', Stream::fromTemp('yoyogi'));
        $buffer = strval($multipartData->getStream());
        $this->assertEquals($buffer, 'yoyogi');
    }

    public function test_contentType()
    {
        $multipartData = new MultipartData('summer_maihama', Stream::fromTemp());
        $this->assertEquals('application/octet-stream', $multipartData->getContentType());

        $multipartData = new MultipartData('summer_osaka', Stream::fromTemp('{"marshmallow":"catch"}'), 'application/json');
        $this->assertEquals('application/json', $multipartData->getContentType());
    }

    public function test_fileName()
    {
        $multipartData = new MultipartData('3rd_makuhari', Stream::fromTemp());
        $this->assertEquals('3rd_makuhari', $multipartData->getFileName());

        $multipartData = new MultipartData('3rd_makuhari', Stream::fromTemp(), '', 'hall_9-11.txt');
        $this->assertEquals('hall_9-11.txt', $multipartData->getFileName());
    }
}
