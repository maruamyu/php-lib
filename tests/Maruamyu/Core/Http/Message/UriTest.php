<?php

namespace Maruamyu\Core\Http\Message;

class UriTest extends \PHPUnit\Framework\TestCase
{
    public function test_instance()
    {
        $uri = new Uri();
        $this->assertNotNull($uri->toString());
    }

    public function test_getScheme()
    {
        $uri = new Uri('https://example.jp/hoge?fuga=piyo');
        $this->assertEquals('https', $uri->getScheme());
    }

    public function test_getHost()
    {
        $uri = new Uri('https://example.jp/hoge?fuga=piyo');
        $this->assertEquals('example.jp', $uri->getHost());
    }

    public function test_getPort()
    {
        $uri = new Uri('http://example.jp/hoge?fuga=piyo');
        $this->assertEquals(null, $uri->getPort());

        $uri = new Uri('http://example.jp:80/hoge?fuga=piyo');
        $this->assertEquals(null, $uri->getPort());

        $uri = new Uri('http://example.jp:8080/hoge?fuga=piyo');
        $this->assertEquals(8080, $uri->getPort());
    }

    public function test_getPath()
    {
        $uri = new Uri('https://example.jp/hoge?fuga=piyo');
        $this->assertEquals('/hoge', $uri->getPath());
    }

    public function test_getQueryString()
    {
        $url = 'http://example.jp/?hoge=fuga';

        $uri = new Uri($url);
        $this->assertEquals($url, $uri->toString());

        $queryString = $uri->getQueryString();
        $queryString->set('hogehoge', 'ほげほげ');

        $this->assertEquals($url, $uri->toString());
    }

    public function test_withQueryString()
    {
        $uri = new Uri('http://example.jp/?hoge=fuga');

        $queryString = $uri->getQueryString();
        $this->assertEquals('hoge=fuga', $queryString->toString());

        $queryString->set('hogehoge', 'ほげほげ');
        $this->assertEquals('hoge=fuga&hogehoge=%E3%81%BB%E3%81%92%E3%81%BB%E3%81%92', $queryString->toString());

        $uri = $uri->withQueryString($queryString);

        $queryString->set('fugafuga', 'ふがふが');

        $this->assertEquals('http://example.jp/?hoge=fuga&hogehoge=%E3%81%BB%E3%81%92%E3%81%BB%E3%81%92', $uri->toString());
    }

    public function test_withAddedQueryString()
    {
        $uri = new Uri('http://example.jp/?hoge=fuga');

        $uri = $uri->withAddedQueryString('hoge=' . rawurlencode('ほげほげ'));
        $this->assertEquals('http://example.jp/?hoge=fuga&hoge=%E3%81%BB%E3%81%92%E3%81%BB%E3%81%92', $uri->toString());

        $uri = $uri->withAddedQueryString(['fuga' => 'fugafuga']);
        $this->assertEquals('http://example.jp/?hoge=fuga&hoge=%E3%81%BB%E3%81%92%E3%81%BB%E3%81%92&fuga=fugafuga', $uri->toString());

        $kvs = new QueryString();
        $kvs->set('piyo', 'ぴよぴよ');
        $uri = $uri->withAddedQueryString($kvs);
        $this->assertEquals('http://example.jp/?hoge=fuga&hoge=%E3%81%BB%E3%81%92%E3%81%BB%E3%81%92&fuga=fugafuga&piyo=%E3%81%B4%E3%82%88%E3%81%B4%E3%82%88', $uri->toString());
    }

    public function test_getFragment()
    {
        $uri = new Uri('https://example.jp/hoge?fuga=piyo');
        $this->assertEquals('', $uri->getFragment());

        $uri = new Uri('https://example.jp/hoge?fuga=piyo#foo');
        $this->assertEquals('foo', $uri->getFragment());
    }

    public function test_equals()
    {
        $simpleUrl = 'http://example.jp/';
        $uri = new Uri($simpleUrl);
        $this->assertTrue($uri->equals($simpleUrl));

        $hasPathUrl = 'http://example.jp/foo/var';
        $uri = new Uri($hasPathUrl);
        $this->assertTrue($uri->equals($hasPathUrl));

        $hasQueryStringUrl = 'https://example.jp/foo/var?hoge=fuga&hoge=piyo';
        $uri = new Uri($hasQueryStringUrl);
        $this->assertTrue($uri->equals($hasQueryStringUrl));

        $hasPortUrl = 'http://example.jp:8080/foo/var?hoge=fuga&hoge=piyo';
        $uri = new Uri($hasPortUrl);
        $this->assertTrue($uri->equals($hasPortUrl));

        $hasUserAuthUrl = 'http://user@example.jp:8080/foo/var?hoge=fuga&hoge=piyo';
        $uri = new Uri($hasUserAuthUrl);
        $this->assertTrue($uri->equals($hasUserAuthUrl));

        $hasUserPathAuthUrl = 'http://user:pass@example.jp:8080/foo/var?hoge=fuga&hoge=piyo';
        $uri = new Uri($hasUserPathAuthUrl);
        $this->assertTrue($uri->equals($hasUserPathAuthUrl));

        $hasFragmentUrl = 'https://example.jp/foo/var#baz';
        $uri = new Uri($hasFragmentUrl);
        $this->assertTrue($uri->equals($hasFragmentUrl));
    }
}
