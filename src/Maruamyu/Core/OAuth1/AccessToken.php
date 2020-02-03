<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Message\QueryString;

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
     * @param string $queryString
     * @return static|null
     */
    public static function fromQueryString($queryString)
    {
        $data = QueryString::parse($queryString);
        if (isset($data, $data['oauth_token'], $data['oauth_token_secret'])) {
            return new static($data['oauth_token'][0], $data['oauth_token_secret'][0]);
        } else {
            return null;
        }
    }

    /**
     * @return string QUERY_STRING
     */
    public function __toString()
    {
        return QueryString::build($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'oauth_token' => $this->getToken(),
            'oauth_token_secret' => $this->getTokenSecret(),
        ];
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
