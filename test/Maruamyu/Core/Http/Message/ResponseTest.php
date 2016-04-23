<?php

namespace Maruamyu\Core\Http\Message;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function test_statusCode()
    {
        $response = new Response();

        $response = $response->withStatus(200);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_statusCode_immutable()
    {
        $response = new Response();

        $response = $response->withStatus(404);
        $this->assertEquals(404, $response->getStatusCode());

        $response->withStatus(302);
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function test_reasonPhrase_default()
    {
        $response = new Response();
        $response = $response->withStatus(503);

        $this->assertEquals('Service Unavailable', $response->getReasonPhrase());
    }

    public function test_reasonPhrase_set()
    {
        $response = new Response();
        $response = $response->withStatus(418, 'オイラはティーポット！');
        $this->assertEquals('オイラはティーポット！', $response->getReasonPhrase());
    }

    public function test_reasonPhrase_immutable()
    {
        $response = new Response();
        $response = $response->withStatus(418, 'オイラはティーポット！');

        $response->withStatus(400, 'オイラはティーポットじゃねえ！');

        $this->assertEquals('オイラはティーポット！', $response->getReasonPhrase());
    }

    public function test_body_with_initialize()
    {
        $response = new Response('hoge');
        $this->assertEquals('hoge', strval($response->getBody()));

        $stream = Stream::fromTemp();
        $stream->write('fuga');
        $response = new Response($stream);
        $this->assertEquals('fuga', strval($response->getBody()));

        $handler = tmpfile();
        fwrite($handler, 'piyo');
        fseek($handler, 0, SEEK_SET);
        $response = new Response($handler);
        $this->assertEquals('piyo', strval($response->getBody()));
    }

    public function test_header_with_initialize()
    {
        $header = new Header();
        $header->set('Content-Type', 'text/html');
        $header->set('Cookie', 'hoge=hogehoge');
        $response = new Response('', $header);
        $this->assertEquals($header->fields(), $response->getHeaderFields());

        $response = new Response('', ['Content-Type' => 'text/plain']);
        $this->assertEquals(['Content-Type: text/plain'], $response->getHeaderFields());

        $response = new Response('', ['Content-Type: application/json']);
        $this->assertEquals(['Content-Type: application/json'], $response->getHeaderFields());
    }

    public function test_status_with_initialize()
    {
        $response = new Response('', null, 200);
        $this->assertEquals(200, $response->getStatusCode());

        $response = new Response('', null, 404, 'Not Found!!');
        $this->assertEquals('Not Found!!', $response->getReasonPhrase());
    }

    public function test_protocol_version_with_initialize()
    {
        $response = new Response('', null, 0, '', '1.0');
        $this->assertEquals('1.0', $response->getProtocolVersion());
    }

    #----------------------------------------

    protected function getInstance()
    {
        return new Response();
    }

    public function test_withHeader()
    {
        $message = $this->getInstance()
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-Type', 'text/html');

        # immutable
        $message->withHeader('Content-Type', 'text/plain');

        $this->assertEquals(['text/html'], $message->getHeader('Content-Type'));
    }

    public function test_withAddedHeader()
    {
        $message = $this->getInstance();

        $message = $message->withAddedHeader('Cookie', '1st=nakano');
        $this->assertEquals(['1st=nakano'], $message->getHeader('Cookie'));

        $message = $message->withAddedHeader('Cookie', '2nd=makuhari');
        $this->assertEquals(['1st=nakano', '2nd=makuhari'], $message->getHeader('Cookie'));

        # immutable
        $message->withAddedHeader('Cookie', '3rd=tour');
        $this->assertEquals(['1st=nakano', '2nd=makuhari'], $message->getHeader('Cookie'));
    }

    public function test_hasHeader()
    {
        $message = $this->getInstance()
            ->withHeader('Content-Type', 'text/html');

        $this->assertTrue($message->hasHeader('Content-Type'));
        $this->assertFalse($message->hasHeader('Cookie'));

        # immutable
        $message->withHeader('Cookie', '4th=budoukan');
        $this->assertFalse($message->hasHeader('Cookie'));
    }

    public function test_withoutHeader()
    {
        $message = $this->getInstance()
            ->withHeader('Host', 'example.jp')
            ->withHeader('Content-Type', 'text/html')
            ->withoutHeader('Host');

        $this->assertTrue($message->hasHeader('Content-Type'));
        $this->assertFalse($message->hasHeader('Host'));

        # immutable
        $message->withoutHeader('Content-Type');
        $this->assertTrue($message->hasHeader('Content-Type'));
    }

    public function test_headerFields()
    {
        $message = $this->getInstance()
            ->withHeader('Host', 'example.jp')
            ->withHeader('Content-Type', 'text/html');

        $expect = [
            'Host: example.jp',
            'Content-Type: text/html',
        ];
        $this->assertEquals($expect, $message->getHeaderFields());
    }

    public function test_ContentType()
    {
        # 最後に設定されたものを取得したい
        $message = $this->getInstance()
            ->withAddedHeader('Content-Type', 'application/json')
            ->withAddedHeader('Content-Type', 'text/html');
        $this->assertEquals('text/html', $message->getContentType());
    }

    public function test_body()
    {
        $stream = Stream::fromTemp();
        $stream->write('手作りのぶどーかん！');
        # $stream->rewind();

        $message = $this->getInstance();
        $message = $message->withBody($stream);

        # $this->assertEquals('手作りのぶどーかん！', $message->getBody()->getContents());
        $this->assertEquals('手作りのぶどーかん！', strval($message->getBody()));

        # immutabel(stream単位)
        $stream2 = Stream::fromTemp();
        $stream2->write('Thank You!');
        $message->withBody($stream2);
        $this->assertEquals('手作りのぶどーかん！', strval($message->getBody()));
    }

    public function test_bodyContents()
    {
        $message = $this->getInstance();
        $message = $message->withBodyContents('ハイポーズ！');

        $this->assertEquals('ハイポーズ！', strval($message->getBody()));

        # immutabel
        $message->withBodyContents('Welcome!!');
        $this->assertEquals('ハイポーズ！', strval($message->getBody()));
    }
}
