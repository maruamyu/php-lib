<?php

namespace Maruamyu\Core\OAuth2;

/**
 * OAuth2.0 access_token
 */
class AccessToken
{
    /** @var array */
    private $data = [];

    /** @var \DateTimeImmutable|null */
    private $issuedAt = null;

    /** @var \DateTimeImmutable|null */
    private $expiresAt = null;

    /**
     * @param array $tokenData decoded successful access_token response
     * @param \DateTimeInterface|int $issuedAt
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
        if (isset($this->data['token_type'])) {
            return strval($this->data['token_type']);
        } else {
            return '';
        }
    }

    /**
     * @return string access_token
     */
    public function getToken()
    {
        if (isset($this->data['access_token'])) {
            return strval($this->data['access_token']);
        } else {
            return '';
        }
    }

    /**
     * @return string|null refresh_token
     */
    public function getRefreshToken()
    {
        if (isset($this->data['refresh_token'])) {
            return strval($this->data['refresh_token']);
        } else {
            return null;
        }
    }

    /**
     * @return int expires_in (return zero if not set yet)
     */
    public function getExpiresIn()
    {
        if (isset($this->data['expires_in'])) {
            return intval(($this->data['expires_in']), 10);
        } else {
            return 0;
        }
    }

    /**
     * @return \DateTimeImmutable|null expire of token (return null if not set yet)
     */
    public function getExpiresAt()
    {
        return $this->expiresAt;
    }

    /**
     * @return \DateTimeImmutable|null expire of token (return null if not set yet)
     * @deprecated getExpiresAt()
     */
    public function getExpireAt()
    {
        return $this->getExpiresAt();
    }

    /**
     * @return string[] list of scopes
     */
    public function getScopes()
    {
        if (isset($this->data['scope'])) {
            return explode(' ', $this->data['scope']);
        } else {
            return [];
        }
    }

    /**
     * @param string $scope needle scope
     * @return bool true if needle in scopes, else false
     */
    public function inScopes($scope)
    {
        return in_array($scope, $this->getScopes(), true);
    }

    /**
     * @return string|null
     */
    public function getIdToken()
    {
        if (isset($this->data['id_token'])) {
            return strval($this->data['id_token']);
        } else {
            return null;
        }
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
        $tokenData = $this->data;

        if ($this->issuedAt) {
            $tokenData['iat'] = $this->issuedAt->getTimestamp();
        }
        if ($this->expiresAt) {
            $tokenData['exp'] = $this->expiresAt->getTimestamp();
        }

        return $tokenData;
    }

    /**
     * @param array $tokenData
     * @param int|\DateTimeInterface $issuedAt
     */
    public function update(array $tokenData, $issuedAt = null)
    {
        $this->data = $tokenData;

        # `iat` and `exp` from id_token
        $idTokenPayload = [];
        if (isset($tokenData['id_token'])) {
            try {
                $idTokenPayload = JsonWebToken::parse($tokenData['id_token']);
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

        # set expiresAt
        if (isset($tokenData['exp'])) {
            $this->expiresAt = \DateTimeImmutable::createFromFormat('U', $tokenData['exp']);
        } elseif (isset($this->issuedAt, $tokenData['expires_in'])) {
            $this->expiresAt = $this->issuedAt->modify('+ ' . $tokenData['expires_in'] . ' sec');
        }
    }
}
