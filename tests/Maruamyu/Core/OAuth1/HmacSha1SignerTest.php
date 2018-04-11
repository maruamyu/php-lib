<?php

namespace Maruamyu\Core\OAuth1;

class HmacSha1SignerTest extends \PHPUnit\Framework\TestCase
{
    public function test_getSignatureMethod()
    {
        $consumerKey = $this->getConsumerKey();
        $singer = new HmacSha1Signer($consumerKey);
        $this->assertEquals('HMAC-SHA1', $singer->getSignatureMethod());
    }

    public function test_sign()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $method = 'GET';
        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];
        $authParams = [
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_nonce' => 'nonce',
            'oauth_consumer_key' => $consumerKey->getKey(),
            'oauth_token' => $accessToken->getToken(),
        ];

        $singer = new HmacSha1Signer($consumerKey, $accessToken);
        $signature = $singer->sign($method, $url, $params, $authParams);
        $this->assertEquals('qBoVJCjYWRHEmXh5VRqzItHuA50=', $signature);
    }

    public function test_verify()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $method = 'GET';
        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];
        $authParams = [
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_nonce' => 'nonce',
            'oauth_consumer_key' => $consumerKey->getKey(),
            'oauth_token' => $accessToken->getToken(),
            'oauth_signature' => 'qBoVJCjYWRHEmXh5VRqzItHuA50=',
        ];

        $singer = new HmacSha1Signer($consumerKey, $accessToken);
        $this->assertTrue($singer->verify($method, $url, $params, $authParams));

        $authParams['oauth_signature'] = 'invalid_signature';
        $this->assertFalse($singer->verify($method, $url, $params, $authParams));
    }

    public function test_verify_query_string()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $method = 'GET';
        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
        ];
        $authParams = [
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_nonce' => 'nonce',
            'oauth_consumer_key' => $consumerKey->getKey(),
            'oauth_token' => $accessToken->getToken(),
            'oauth_signature' => 'qBoVJCjYWRHEmXh5VRqzItHuA50=',
        ];

        $url .= '?' . http_build_query($params);

        $singer = new HmacSha1Signer($consumerKey, $accessToken);
        $this->assertTrue($singer->verify($method, $url, null, $authParams));

        $authParams['oauth_signature'] = 'invalid_signature';
        $this->assertFalse($singer->verify($method, $url, null, $authParams));
    }

    public function test_verify_query_string_all()
    {
        $consumerKey = $this->getConsumerKey();
        $accessToken = $this->getAccessToken();

        $method = 'GET';
        $url = 'http://example.jp/';
        $params = [
            'hoge' => 'ほげほげ',
            'fuga' => 'ふがふが',
            'oauth_version' => '1.0',
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => '1234567890',
            'oauth_nonce' => 'nonce',
            'oauth_consumer_key' => $consumerKey->getKey(),
            'oauth_token' => $accessToken->getToken(),
            'oauth_signature' => 'qBoVJCjYWRHEmXh5VRqzItHuA50=',
        ];

        $url .= '?' . http_build_query($params);

        $singer = new HmacSha1Signer($consumerKey, $accessToken);
        $this->assertTrue($singer->verify($method, $url, null));

        $url .= 'invalid_param=1';
        $this->assertFalse($singer->verify($method, $url, null));
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
