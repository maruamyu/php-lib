<?php

namespace Maruamyu\Core\OAuth1;

class AuthorizationHeaderTest extends \PHPUnit\Framework\TestCase
{
    public function test_getScheme()
    {
        $authorizationHeader = new AuthorizationHeader();
        $this->assertEquals('OAuth', $authorizationHeader->getScheme());
    }

    public function test_fromHeaderValue()
    {
        $headerValue  = 'OAuth realm="http://example.jp/"';
        $headerValue .= ' , oauth_version="1.0"';
        $headerValue .= ' , oauth_signature_method="HMAC-SHA1"';
        $headerValue .= ' , oauth_consumer_key="consumer_key"';
        $headerValue .= ' , oauth_token="oauth_token"';
        $headerValue .= ' , oauth_nonce="nonce"';
        $headerValue .= ' , oauth_timestamp="1234567890"';
        $headerValue .= ' , oauth_signature="qBoVJCjYWRHEmXh5VRqzItHuA50="';

        $params = [
            'realm' => 'http://example.jp/',
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_consumer_key' => 'consumer_key',
            'oauth_token' => 'oauth_token',
            'oauth_nonce' => 'nonce',
            'oauth_timestamp' => '1234567890',
            'oauth_signature' => 'qBoVJCjYWRHEmXh5VRqzItHuA50=',
        ];
        $authorizationHeader = new AuthorizationHeader($headerValue);
        $this->assertEquals($params, $authorizationHeader->getParams());
    }

    public function test_fromAuthParams()
    {
        $params = [
            'realm' => 'http://example.jp/',
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_consumer_key' => 'consumer_key',
            'oauth_token' => 'oauth_token',
            'oauth_nonce' => 'nonce',
            'oauth_timestamp' => '1234567890',
            'oauth_signature' => 'qBoVJCjYWRHEmXh5VRqzItHuA50=',
        ];
        $authorizationHeader = new AuthorizationHeader($params);
        $this->assertEquals($params, $authorizationHeader->getParams());
    }
}
