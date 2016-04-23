<?php

namespace Maruamyu\Core\OAuth;

/**
 * tokenとtoken_secretのペア
 */
abstract class TokenAbstract
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
     * @param string $token token
     * @param string $tokenSecret token_secret
     */
    public function __construct($token, $tokenSecret)
    {
        $this->setToken($token, $tokenSecret);
    }

    /**
     * @return string token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string token_secret
     */
    public function getTokenSecret()
    {
        return $this->tokenSecret;
    }

    /**
     * @param string $token token
     * @param string $tokenSecret token_secret
     */
    protected function setToken($token, $tokenSecret)
    {
        $this->token = strval($token);
        $this->tokenSecret = strval($tokenSecret);
    }
}
