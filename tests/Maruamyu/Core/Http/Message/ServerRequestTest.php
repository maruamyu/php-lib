<?php

namespace Maruamyu\Core\Http\Message;

class ServerRequestTest extends \PHPUnit\Framework\TestCase
{
    public function test_fromEnvironment_get_method()
    {
        $_SERVER = [
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_DNT' => '1',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'ja',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
            'HTTP_HOST' => 'example.jp',
            'SERVER_NAME' => 'internal.example.jp',
            'SERVER_PORT' => '80',
            'SERVER_ADDR' => '127.0.0.1',
            'REQUEST_SCHEME' => 'http',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_URI' => '/hoge/fuga?piyo=hogefugapiyo',
            'CONTENT_LENGTH' => '',
            'CONTENT_TYPE' => '',
            'REQUEST_METHOD' => 'GET',
            'QUERY_STRING' => 'hidden=params&piyo=hogefugapiyo',
        ];

        $_GET = [
            'hidden' => 'params',
            'piyo' => 'hogefugapiyo',
        ];

        $_POST = [];

        $_COOKIE = [
            'my_session_id' => 'hoge_session_id',
        ];

        $serverRequest = ServerRequest::fromEnvironment();
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
        $this->assertEquals('GET', $serverRequest->getMethod());
        $this->assertEquals('http://example.jp/hoge/fuga?piyo=hogefugapiyo', strval($serverRequest->getUri()));
        $this->assertEquals($_SERVER, $serverRequest->getServerParams());
        $this->assertEquals($_GET, $serverRequest->getQueryParams());
        $this->assertEquals($_POST, $serverRequest->getParsedBody());
        $this->assertEquals($_COOKIE, $serverRequest->getCookieParams());
    }

    public function test_fromEnvironment_post_method()
    {
        $_SERVER = [
            'HTTP_CACHE_CONTROL' => 'max-age=0',
            'HTTP_UPGRADE_INSECURE_REQUESTS' => '1',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_DNT' => '1',
            'HTTP_ACCEPT_ENCODING' => 'gzip, deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'ja',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'HTTP_USER_AGENT' => 'Mozilla/5.0',
            'HTTP_HOST' => 'example.jp',
            'SERVER_NAME' => 'internal.example.jp',
            'SERVER_PORT' => '80',
            'SERVER_ADDR' => '127.0.0.1',
            'REQUEST_SCHEME' => 'http',
            'SERVER_PROTOCOL' => 'HTTP/1.1',
            'REQUEST_URI' => '/hoge/fuga?piyo=hogefugapiyo',
            'CONTENT_LENGTH' => '15',
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
            'REQUEST_METHOD' => 'POST',
            'QUERY_STRING' => 'hidden=params&piyo=hogefugapiyo',
        ];

        $_GET = [
            'hidden' => 'params',
            'piyo' => 'hogefugapiyo',
        ];

        $_POST = [
            'hoge' => ['hogehoge'],
        ];

        $_COOKIE = [
            'my_session_id' => 'hoge_session_id',
        ];

        $serverRequest = ServerRequest::fromEnvironment();
        $this->assertEquals('1.1', $serverRequest->getProtocolVersion());
        $this->assertEquals('POST', $serverRequest->getMethod());
        $this->assertEquals('http://example.jp/hoge/fuga?piyo=hogefugapiyo', strval($serverRequest->getUri()));
        $this->assertEquals($_SERVER, $serverRequest->getServerParams());
        $this->assertEquals($_GET, $serverRequest->getQueryParams());
        $this->assertEquals($_POST, $serverRequest->getParsedBody());
        $this->assertEquals($_COOKIE, $serverRequest->getCookieParams());
        $this->assertEmpty($serverRequest->getUploadedFiles());
    }
}