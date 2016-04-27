<?php

namespace Maruamyu\Core\OAuth;

class AccessTokenTest extends \PHPUnit_Framework_TestCase
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
}
