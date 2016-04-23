<?php

namespace Maruamyu\Core\OAuth;

use Maruamyu\Core\Http\Message\ServerRequest;
use Maruamyu\Core\Http\Message\Uri;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    public function test_setAccessToken()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $consumer = new Consumer($consumerKey);
        $consumer->setAccessToken($accessToken);
        $this->assertTrue($consumer->hasAccessToken());
    }

    public function test_setNullAccessToken()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $consumer = new Consumer($consumerKey);
        $consumer->setAccessToken($accessToken);
        $consumer->setNullAccessToken();
        $this->assertFalse($consumer->hasAccessToken());
    }

    public function test_verifySignature()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();
        $consumer = new Consumer($consumerKey);
        $consumer->setAccessToken($accessToken);

        $serverRequest = $this->getServerRequest();
        $this->assertTrue($consumer->verifySignature($serverRequest));
    }

    private function getConsumerKey()
    {
        return new ConsumerKey('consumer_key', 'consumer_secret');
    }

    private function getAccessToken()
    {
        return new AccessToken('oauth_token', 'oauth_token_secret');
    }

    private function getServerRequest()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];
        $url .= '?' . http_build_query($params);

        $authorizationValue = 'OAuth realm="http://example.jp/"';
        $authorizationValue .= ' , oauth_version="1.0"';
        $authorizationValue .= ' , oauth_signature_method="HMAC-SHA1"';
        $authorizationValue .= ' , oauth_consumer_key="' . rawurlencode($consumerKey->getToken()) . '"';
        $authorizationValue .= ' , oauth_token="' . rawurlencode($accessToken->getToken()) . '"';
        $authorizationValue .= ' , oauth_nonce="nonce"';
        $authorizationValue .= ' , oauth_timestamp="1234567890"';
        $authorizationValue .= ' , oauth_signature="qBoVJCjYWRHEmXh5VRqzItHuA50="';

        return (new ServerRequest())
            ->withUri(new Uri($url))
            ->withMethod('GET')
            ->withHeader('Authorization', $authorizationValue);
    }
}
