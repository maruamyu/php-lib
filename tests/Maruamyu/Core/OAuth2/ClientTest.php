<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Base64Url;
use Maruamyu\Core\Http\Message\Uri;

class ClientTest extends \PHPUnit\Framework\TestCase
{
    public function test_initialize()
    {
        $settings = $this->getSettings();

        $client = new Client($settings);
        $this->assertInstanceOf(Client::class, $client);
    }

    public function test_startAuthorizationCodeGrant()
    {
        $settings = $this->getSettings();

        $client = new Client($settings);
        $callbackUrl = 'https://example.jp/oauth2/callback';
        $state = sha1(uniqid());
        $uri = new Uri($client->startAuthorizationCodeGrant(['openid'], $callbackUrl, $state));

        $this->assertEquals('example.jp', $uri->getHost());
        $this->assertEquals('/oauth2/authorization', $uri->getPath());
        $this->assertEquals('code', $uri->getQueryString()->getString('response_type'));
        $this->assertEquals('client_id', $uri->getQueryString()->getString('client_id'));
        $this->assertEquals('openid', $uri->getQueryString()->getString('scope'));
        $this->assertEquals($callbackUrl, $uri->getQueryString()->getString('redirect_uri'));
        $this->assertEquals($state, $uri->getQueryString()->getString('state'));
    }

    public function test_startAuthorizationCodeGrantWithPkce()
    {
        $settings = $this->getSettings();

        $client = new Client($settings);
        $callbackUrl = 'https://example.jp/oauth2/callback';
        $codeVerifier = openssl_random_pseudo_bytes(128);
        $uri = new Uri($client->startAuthorizationCodeGrantWithPkce(['openid'], $callbackUrl, $codeVerifier));

        $this->assertEquals('example.jp', $uri->getHost());
        $this->assertEquals('/oauth2/authorization', $uri->getPath());
        $this->assertEquals('code', $uri->getQueryString()->getString('response_type'));
        $this->assertEquals('client_id', $uri->getQueryString()->getString('client_id'));
        $this->assertEquals('openid', $uri->getQueryString()->getString('scope'));
        $this->assertEquals($callbackUrl, $uri->getQueryString()->getString('redirect_uri'));
        $this->assertEquals('S256', $uri->getQueryString()->getString('code_challenge_method'));
        $this->assertEquals(Base64Url::encode(hash('sha256', $codeVerifier, true)), $uri->getQueryString()->getString('code_challenge'));
    }

    public function test_startImplicitGrant()
    {
        $settings = $this->getSettings();

        $client = new Client($settings);
        $callbackUrl = 'https://example.jp/oauth2/callback';
        $state = sha1(uniqid());
        $uri = new Uri($client->startImplicitGrant(['openid'], $callbackUrl, $state));

        $this->assertEquals('example.jp', $uri->getHost());
        $this->assertEquals('/oauth2/authorization', $uri->getPath());
        $this->assertEquals('token', $uri->getQueryString()->getString('response_type'));
        $this->assertEquals('client_id', $uri->getQueryString()->getString('client_id'));
        $this->assertEquals('openid', $uri->getQueryString()->getString('scope'));
        $this->assertEquals($callbackUrl, $uri->getQueryString()->getString('redirect_uri'));
        $this->assertEquals($state, $uri->getQueryString()->getString('state'));
    }

    public function test_makeRequest()
    {
        $settings = $this->getSettings();
        $accessToken = new AccessToken([
            'access_token' => 'access_token',
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ]);

        $client = new Client($settings, $accessToken);
        $request = $client->makeRequest('POST', 'https://example.jp/oauth2/endpoint');
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('https://example.jp/oauth2/endpoint', strval($request->getUri()));
        $this->assertEquals(['Bearer access_token'], $request->getHeader('Authorization'));
    }

    /**
     * @return Settings
     */
    private function getSettings()
    {
        $settings = new Settings();
        $settings->clientId = 'client_id';
        $settings->clientSecret = 'client_secret';
        $settings->authorizationEndpoint = 'https://example.jp/oauth2/authorization';
        return $settings;
    }
}
