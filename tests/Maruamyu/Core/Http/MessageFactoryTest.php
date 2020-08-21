<?php

namespace Maruamyu\Core\Http;

use Maruamyu\Core\Http\Message\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MessageFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function test_createRequest()
    {
        $messageFactory = new MessageFactory();

        $request = $messageFactory->createRequest('POST', 'https://example.jp/');
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://example.jp/', strval($request->getUri()));
    }

    public function test_createRequest_uri()
    {
        $messageFactory = new MessageFactory();

        $uri = new Uri('https://example.jp/');
        $request = $messageFactory->createRequest('HEAD', $uri);
        $expects = clone $uri;
        $this->assertEquals($expects, $request->getUri());
    }

    public function test_createResponse_default()
    {
        $messageFactory = new MessageFactory();

        $response = $messageFactory->createResponse();
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getReasonPhrase());
    }

    public function test_createResponse_only_code()
    {
        $messageFactory = new MessageFactory();

        $response = $messageFactory->createResponse(500);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertEquals('Internal Server Error', $response->getReasonPhrase());
    }

    public function test_createResponse_code_and_reasonPhrase()
    {
        $messageFactory = new MessageFactory();

        $response = $messageFactory->createResponse(404, 'Not Found!!');
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals('Not Found!!', $response->getReasonPhrase());
    }

    public function test_createServerRequest()
    {
        $messageFactory = new MessageFactory();

        $serverRequest = $messageFactory->createServerRequest('POST', 'https://example.jp/');
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertEquals('POST', $serverRequest->getMethod());
        $this->assertEquals('https://example.jp/', strval($serverRequest->getUri()));
        $this->assertEquals([], $serverRequest->getServerParams());
    }

    public function test_createServerRequest_with_serverParams()
    {
        $messageFactory = new MessageFactory();

        $serverParams = [
            'hoge' => 'hogehoge',
            'fuga' => 'fugafuga',
            'piyo' => 'piyopiyo',
        ];
        $serverRequest = $messageFactory->createServerRequest('HEAD', 'https://example.jp/', $serverParams);
        $this->assertInstanceOf(ServerRequestInterface::class, $serverRequest);
        $this->assertEquals('HEAD', $serverRequest->getMethod());
        $this->assertEquals('https://example.jp/', strval($serverRequest->getUri()));
        $this->assertEquals($serverParams, $serverRequest->getServerParams());
    }
}
