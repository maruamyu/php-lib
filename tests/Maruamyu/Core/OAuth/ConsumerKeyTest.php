<?php

namespace Maruamyu\Core\OAuth;

class ConsumerKeyTest extends \PHPUnit\Framework\TestCase
{
    public function test_token()
    {
        $consumerKey = new ConsumerKey('shimamura', 'udzuki');
        $this->assertEquals('shimamura', $consumerKey->getToken());
    }

    public function test_tokenSecret()
    {
        $consumerKey = new ConsumerKey('shibuya', 'rin');
        $this->assertEquals('rin', $consumerKey->getTokenSecret());
    }
}
