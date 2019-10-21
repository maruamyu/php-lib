<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OAuth2.0 access_token
 */
class AccessToken
{
    /** @var string */
    private $token;

    /** @var string */
    private $type;

    /** @var int|null */
    private $expiresIn;

    /** @var \DateTimeImmutable|null */
    private $expireAt;

    /** @var string|null */
    private $refreshToken;

    /** @var string[] */
    private $scopes;

    /** @var string */
    private $idToken;

    /**
     * @param array $tokenData decoded successful access_token response
     * @param int $timestamp unixtime
     */
    public function __construct(array $tokenData = null, $timestamp = null)
    {
        if (empty($tokenData)) {
            return;
        }
        $this->setTokenData($tokenData, $timestamp);
    }

    /**
     * @return string type of token
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string access_token
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string refresh_token
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @return \DateTimeImmutable|null expire of token (return null if not set yet)
     */
    public function getExpireAt()
    {
        return $this->expireAt;
    }

    /**
     * @return string[] list of scopes
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @param string $scope needle scope
     * @return boolean true if needle in scopes, else false
     */
    public function inScopes($scope)
    {
        return in_array($scope, $this->scopes, true);
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->idToken;
    }

    /**
     * @return string "type token"
     */
    public function getHeaderValue()
    {
        return $this->getType() . ' ' . $this->getToken();
    }

    /**
     * @return string
     * @see toJson()
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_SLASHES);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $tokenData = [
            'access_token' => $this->token,
            'token_type' => $this->type,
        ];

        if ($this->expiresIn) {
            $tokenData['expires_in'] = $this->expiresIn;
        } elseif ($this->expireAt) {
            $tokenData['expires_in'] = $this->expireAt->getTimestamp() - time();
        }

        if ($this->refreshToken) {
            $tokenData['refresh_token'] = $this->refreshToken;
        }

        if (!(empty($this->scopes))) {
            $tokenData['scope'] = join(' ', $this->scopes);
        }

        if ($this->idToken) {
            $tokenData['id_token'] = strval($this->idToken);
        }

        return $tokenData;
    }

    /**
     * @param array $tokenData
     * @param int $timestamp
     */
    public function update(array $tokenData, $timestamp = null)
    {
        if (isset($tokenData['access_token'])) {
            $this->token = $tokenData['access_token'];
        }
        if (isset($tokenData['token_type'])) {
            $this->type = $tokenData['token_type'];
        }
        if (isset($tokenData['expires_in'])) {
            $this->setExpiresIn($tokenData['expires_in'], $timestamp);
        }
        if (isset($tokenData['refresh_token'])) {
            $this->refreshToken = $tokenData['refresh_token'];
        }
        if (isset($tokenData['scope'])) {
            $this->scopes = explode(' ', $tokenData['scope']);
        }
        if (isset($tokenData['id_token'])) {
            $this->idToken = $tokenData['id_token'];
        }
    }

    /**
     * @param int $expiresIn
     * @param int $timestamp
     */
    protected function setExpiresIn($expiresIn, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }
        $expireAtTimestamp = $timestamp + $expiresIn;

        $this->expiresIn = $expiresIn;
        $this->expireAt = \DateTimeImmutable::createFromFormat('U', $expireAtTimestamp);
    }

    /**
     * @param array $tokenData
     * @param int $timestamp
     */
    protected function setTokenData(array $tokenData, $timestamp = null)
    {
        $this->token = null;
        $this->type = null;
        $this->expiresIn = null;
        $this->expireAt = null;
        $this->refreshToken = null;
        $this->scopes = [];
        $this->idToken = null;
        $this->update($tokenData, $timestamp);
    }
}
