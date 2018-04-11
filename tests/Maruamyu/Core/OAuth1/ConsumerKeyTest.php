<?php

namespace Maruamyu\Core\OAuth1;

class ConsumerKeyTest extends \PHPUnit\Framework\TestCase
{
    public function test_getKey()
    {
        $consumerKey = new ConsumerKey('shimamura', 'udzuki');
        $this->assertEquals('shimamura', $consumerKey->getKey());
    }

    public function test_getSecret()
    {
        $consumerKey = new ConsumerKey('shibuya', 'rin');
        $this->assertEquals('rin', $consumerKey->getSecret());
    }
}
