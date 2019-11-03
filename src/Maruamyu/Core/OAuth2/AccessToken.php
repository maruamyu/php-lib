<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OAuth2.0 access_token
 */
class AccessToken
{
    /** @var string */
    private $token = '';

    /** @var string */
    private $type = '';

    /** @var int|null */
    private $expiresIn = null;

    /** @var string|null */
    private $refreshToken = null;

    /** @var string[] */
    private $scopes = [];

    /** @var string|null */
    private $idToken = null;

    /** @var \DateTimeImmutable|null */
    private $issuedAt = null;

    /** @var \DateTimeImmutable|null */
    private $expireAt = null;

    /**
     * @param array $tokenData decoded successful access_token response
     * @param \DateTimeInterface|integer $issuedAt
     */
    public function __construct(array $tokenData = null, $issuedAt = null)
    {
        if (isset($tokenData) && (empty($tokenData) == false)) {
            $this->update($tokenData, $issuedAt);
        }
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
     * @return string|null refresh_token
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
        }

        if ($this->issuedAt) {
            $tokenData['iat'] = $this->issuedAt->getTimestamp();
        }
        if ($this->expireAt) {
            $tokenData['exp'] = $this->expireAt->getTimestamp();
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
     * @param integer|\DateTimeInterface $issuedAt
     */
    public function update(array $tokenData, $issuedAt = null)
    {
        if (isset($tokenData['access_token'])) {
            $this->token = strval($tokenData['access_token']);
        }
        if (isset($tokenData['token_type'])) {
            $this->type = strval($tokenData['token_type']);
        }
        if (isset($tokenData['expires_in'])) {
            $this->expiresIn = intval($tokenData['expires_in'], 10);
        }
        if (isset($tokenData['refresh_token'])) {
            $this->refreshToken = strval($tokenData['refresh_token']);
        }
        if (isset($tokenData['scope'])) {
            $this->scopes = explode(' ', strval($tokenData['scope']));
        }
        if (isset($tokenData['id_token'])) {
            $this->idToken = strval($tokenData['id_token']);
        }

        # `iat` and `exp` from id_token
        $idTokenPayload = [];
        if (isset($this->idToken)) {
            try {
                $idTokenPayload = JsonWebToken::parse($this->idToken);
            } catch (\Exception $exception) {
                $idTokenPayload = [];
            }
        }

        # set issuedAt
        if (isset($tokenData['iat'])) {
            $this->issuedAt = \DateTimeImmutable::createFromFormat('U', $tokenData['iat']);
        } elseif ($issuedAt instanceof \DateTimeImmutable) {
            $this->issuedAt = $issuedAt;
        } elseif ($issuedAt instanceof \DateTime) {
            $this->issuedAt = \DateTimeImmutable::createFromMutable($issuedAt);
        } elseif (is_integer($issuedAt)) {
            $this->issuedAt = \DateTimeImmutable::createFromFormat('U', $issuedAt);
        } elseif (isset($idTokenPayload['iat'])) {
            $this->issuedAt = \DateTimeImmutable::createFromFormat('U', $idTokenPayload['iat']);
        }

        # set expireAt
        if (isset($tokenData['exp'])) {
            $this->expireAt = \DateTimeImmutable::createFromFormat('U', $tokenData['exp']);
        } elseif (isset($this->issuedAt, $this->expiresIn)) {
            $this->expireAt = $this->issuedAt->modify('+ ' . $this->expiresIn . ' sec');
        }
    }
}
