<?php

namespace Maruamyu\Core\OAuth1;

/**
 * oauth_token and oauth_token_secret
 */
class AccessToken
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $tokenSecret;

    /**
     * @param string $token oauth_token
     * @param string $tokenSecret oauth_token_secret
     */
    public function __construct($token, $tokenSecret)
    {
        $this->token = strval($token);
        $this->tokenSecret = strval($tokenSecret);
    }

    /**
     * @return string oauth_token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string oauth_token_secret
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }
}
