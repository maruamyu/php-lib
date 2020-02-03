<?php

namespace Maruamyu\Core\OAuth1;

class AccessTokenTest extends \PHPUnit\Framework\TestCase
{
    public function test_token()
    {
        $accessToken = new AccessToken('tendou', 'teru');
        $this->assertEquals('tendou', $accessToken->getToken());
    }

    public function test_tokenSecret()
    {
        $accessToken = new AccessToken('sakuraba', 'kaoru');
        $this->assertEquals('kaoru', $accessToken->getTokenSecret());
    }

    public function test_fromQueryString()
    {
        $accessToken = AccessToken::fromQueryString('oauth_token=kashiwagi&oauth_token_secret=tsubasa');
        $this->assertNotNull($accessToken);
        $this->assertEquals('kashiwagi', $accessToken->getToken());
        $this->assertEquals('tsubasa', $accessToken->getTokenSecret());

        $invalidAccessToken = AccessToken::fromQueryString('oauth_token=yamamura');
        $this->assertNull($invalidAccessToken);
    }

    public function test_toString()
    {
        $accessToken = new AccessToken('yamamura', 'ken');
        $this->assertEquals('oauth_token=yamamura&oauth_token_secret=ken', strval($accessToken));
    }
}
