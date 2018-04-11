<?php

namespace Maruamyu\Core\OAuth1;

/**
 * oauth_consumer_key and oauth_consumer_secret
 */
class ConsumerKey
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $token oauth_consumer_key
     * @param string $tokenSecret oauth_consumer_secret
     */
    public function __construct($key, $secret)
    {
        $this->key = strval($key);
        $this->secret = strval($secret);
    }

    /**
     * @return string oauth_consumer_key
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string oauth_consumer_secret
     */
    public function getSecret()
    {
        return $this->secret;
    }
}
