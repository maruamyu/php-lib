<?php

namespace Maruamyu\Core\OAuth;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function test_setAccessToken()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $client = new Client($consumerKey);
        $client->setAccessToken($accessToken);
        $this->assertTrue($client->hasAccessToken());
    }

    public function test_setNullAccessToken()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $client = new Client($consumerKey);
        $client->setAccessToken($accessToken);
        $client->setNullAccessToken();
        $this->assertFalse($client->hasAccessToken());
    }

    public function test_makeRequest()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $client = new Client($consumerKey);
        $client->setAccessToken($accessToken);

        $method = 'GET';
        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];

        $request = $client->makeRequest($method, $url, $params);
        $this->assertEquals($url . '?' . http_build_query($params), strval($request->getUri()));

        $headerValue = $request->getHeaderLine('Authorization');
        list($authScheme, $parsed) = static::parseAuthorizationHeader($headerValue);
        $this->assertEquals('OAuth', $authScheme);
        $this->assertEquals($url, $parsed['realm']);
        $this->assertEquals('1.0', $parsed['oauth_version']);
        $this->assertEquals('HMAC-SHA1', $parsed['oauth_signature_method']);
        $this->assertEquals($consumerKey->getToken(), $parsed['oauth_consumer_key']);
        $this->assertEquals($accessToken->getToken(), $parsed['oauth_token']);
        $this->assertNotNull($parsed['oauth_timestamp']);
        $this->assertNotNull($parsed['oauth_nonce']);
        $this->assertNotNull($parsed['oauth_signature']);
    }

    public function test_makeAuthorizationHeader()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $client = new Client($consumerKey);
        $client->setAccessToken($accessToken);

        $method = 'GET';
        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];

        $headerValue = $client->makeAuthorization($method, $url, $params);

        $this->assertEquals('1.0', $headerValue['oauth_version']);
        $this->assertEquals('HMAC-SHA1', $headerValue['oauth_signature_method']);
        $this->assertEquals($consumerKey->getToken(), $headerValue['oauth_consumer_key']);
        $this->assertEquals($accessToken->getToken(), $headerValue['oauth_token']);
        $this->assertNotNull($headerValue['oauth_timestamp']);
        $this->assertNotNull($headerValue['oauth_nonce']);
        $this->assertNotNull($headerValue['oauth_signature']);
    }

    private static function parseAuthorizationHeader($headerValue)
    {
        list($headerAuthScheme, $headerAuthParams) = explode(' ', $headerValue, 2);

        $parsed = [];
        $kvpairs = explode(',', $headerAuthParams);
        foreach ($kvpairs as $kvpair) {
            list($key, $value) = explode('=', trim($kvpair), 2);
            $key = rawurldecode($key);
            $value = rawurldecode(trim($value, '"'));
            $parsed[$key] = $value;
        }

        return [$headerAuthScheme, $parsed];
    }

    private function getConsumerKey()
    {
        return new ConsumerKey('consumer_key', 'consumer_secret');
    }

    private function getAccessToken()
    {
        return new AccessToken('oauth_token', 'oauth_token_secret');
    }
}
