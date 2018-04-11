<?php

namespace Maruamyu\Core\Http\Message;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    public function test_method()
    {
        $request = new Request();

        $request = $request->withMethod('POST');
        $this->assertEquals('POST', $request->getMethod());
    }

    public function test_method_immutable()
    {
        $request = new Request();

        $request = $request->withMethod('PUT');
        $this->assertEquals('PUT', $request->getMethod());

        $request->withMethod('DELETE');
        $this->assertEquals('PUT', $request->getMethod());
    }

    public function test_method_with_initialize()
    {
        $request = new Request('PATCH');
        $this->assertEquals('PATCH', $request->getMethod());
    }

    public function test_uri_with_initialize()
    {
        $request = new Request('GET', 'http://example.jp/');
        $this->assertEquals('http://example.jp/', strval($request->getUri()));
    }

    public function test_body_with_initialize()
    {
        $request = new Request('GET', 'http://example.jp/', 'hoge');
        $this->assertEquals('hoge', strval($request->getBody()));

        $stream = Stream::fromTemp();
        $stream->write('fuga');
        $request = new Request('POST', 'http://example.jp/', $stream);
        $this->assertEquals('fuga', strval($request->getBody()));
    }

    public function test_header_with_initialize()
    {
        $request = new Request('GET', 'http://example.jp/', null, ['X-Hoge: hogehoge']);
        $this->assertEquals('hogehoge', $request->getHeaderLine('X-Hoge'));

        $request = new Request('GET', 'http://example.jp/', null, ['X-Fuga' => 'fugafuga']);
        $this->assertEquals('fugafuga', $request->getHeaderLine('X-Fuga'));

        $headers = new Headers();
        $headers->add('X-Piyo', 'hoge');
        $headers->add('X-Piyo', 'fuga');
        $headers->add('X-Piyo', 'piyo');
        $request = new Request('GET', 'http://example.jp/', null, $headers);
        $this->assertEquals('hoge, fuga, piyo', $request->getHeaderLine('X-Piyo'));
    }

    #----------------------------------------

    protected function getInstance()
    {
        return new Request();
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
