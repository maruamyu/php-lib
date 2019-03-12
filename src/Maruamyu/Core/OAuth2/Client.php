<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Http\Client as HttpClient;
use Maruamyu\Core\Http\Message\Headers;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;
use Maruamyu\Core\Http\Message\Uri;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

/**
 * The OAuth 2.0 Authorization Framework (RFC 6749)
 * and OpenID Connect Core 1.0
 * light-weight client
 */
class Client
{
    /** @var Settings */
    protected $settings;

    /** @var AccessToken */
    protected $accessToken;

    /** @var HttpClient */
    protected $httpClient;

    /**
     * @param Settings $settings
     * @param AccessToken $accessToken
     */
    public function __construct(Settings $settings, AccessToken $accessToken = null)
    {
        $this->settings = clone $settings;
        if ($accessToken) {
            $this->setAccessToken($accessToken);
        }
        $this->httpClient = new HttpClient();
    }

    /**
     * @return AccessToken|null
     */
    public function getAccessToken()
    {
        if ($this->accessToken) {
            return clone $this->accessToken;
        } else {
            return null;
        }
    }

    /**
     * @param AccessToken $accessToken
     */
    public function setAccessToken(AccessToken $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @param \DateTimeInterface $currentTime
     * @return boolean true
     */
    public function hasValidAccessToken(\DateTimeInterface $currentTime = null)
    {
        # not has access_token
        if (!$this->accessToken) {
            return false;
        }

        # check expire_at
        $expireAt = $this->accessToken->getExpireAt();
        if ($expireAt) {
            if (!$currentTime) {
                try {
                    $currentTime = new \DateTime();
                } catch (\Exception $exception) {
                    return false;
                }
            }
            if ($currentTime > $expireAt) {
                return false;
            }
        }

        # valid
        return true;
    }

    /**
     * @note not reload if hasValidAccessToken()
     * @see hasValidAccessToken()
     * @see refreshAccessToken()
     * @return AccessToken|null
     * @throws \Exception if invalid settings
     */
    public function reloadAccessToken()
    {
        if ($this->hasValidAccessToken()) {
            return $this->accessToken;
        }
        return $this->refreshAccessToken();
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param string|StreamInterface $body
     * @param Headers|string|array $headers
     * @return Response
     * @see makeRequest()
     */
    public function request($method, $uri, $body = null, $headers = null)
    {
        $request = $this->makeRequest($method, $uri, $body, $headers);
        return $this->httpClient->send($request);
    }

    /**
     * @note add access_token if not include Authorization header
     * @param Request $request
     * @return Response
     */
    public function sendRequest(Request $request)
    {
        if (!($request->hasHeader('Authorization'))) {
            if ($this->accessToken) {
                $request = $request->withHeader('Authorization', $this->accessToken->getHeaderValue());
            }
        }
        return $this->httpClient->send($request);
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param string|StreamInterface $body
     * @param Headers|string|array $headers
     * @return Request HTTP request message including access_token
     */
    public function makeRequest($method, $uri, $body = null, $headers = null)
    {
        if (!($headers instanceof Headers)) {
            $headers = new Headers($headers);
        }
        if ($this->accessToken) {
            $headers->set('Authorization', $this->accessToken->getHeaderValue());
        }
        return new Request($method, $uri, $body, $headers);
    }

    /**
     * Authorization Code Grant : generate Authorization URL
     *
     * @param string[] $scopes
     * @param string|UriInterface $redirectUrl
     * @param string $state
     * @param array $optionalParameters
     * @return string Authorization URL
     * @throws \Exception if invalid settings or arguments
     */
    public function startAuthorizationCodeGrant(array $scopes = [], $redirectUrl = null, $state = null, array $optionalParameters = [])
    {
        if (isset($this->settings->authorizationEndpoint) == false) {
            throw new \RuntimeException('authorizationEndpoint not set yet.');
        }
        $parameters = [
            'response_type' => 'code',
            'client_id' => $this->settings->clientId,
        ];
        if ($redirectUrl) {
            $parameters['redirect_uri'] = strval($redirectUrl);
        }
        if ($scopes) {
            $parameters['scope'] = join(' ', $scopes);
        }
        if ($state) {
            $parameters['state'] = $state;
        }
        if ($optionalParameters) {
            $parameters = array_merge($parameters, $optionalParameters);
        }
        $url = new Uri($this->settings->authorizationEndpoint);
        return $url->withQueryString($parameters)->toString();
    }

    /**
     * Authorization Code Grant : exchange code to access_token
     *
     * @note update holding AccessToken if succeeded
     * @param string $code
     * @param string|UriInterface $redirectUrl
     * @param string $state
     * @return AccessToken|null
     * @throws \Exception if invalid settings or arguments
     */
    public function finishAuthorizationCodeGrant($code, $redirectUrl = null, $state = null)
    {
        if (isset($this->settings->tokenEndpoint) == false) {
            throw new \RuntimeException('tokenEndpoint not set yet.');
        }
        $parameters = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => strval($redirectUrl),
            'client_id' => $this->settings->clientId,
            'client_secret' => $this->settings->clientSecret,
        ];
        if ($state) {
            $parameters['state'] = $state;
        }
        $requestBody = QueryString::build($parameters);
        $response = $this->httpClient->request('POST', $this->settings->tokenEndpoint, ['body' => $requestBody]);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $accessToken = new AccessToken($tokenData);
        $this->setAccessToken($accessToken);
        return $this->getAccessToken();
    }

    /**
     * Client Credentials Grant
     *
     * @note update holding AccessToken if succeeded
     * @param string[] $scopes
     * @param boolean $usingBasicAuthorization
     * @return AccessToken|null
     * @throws \Exception if invalid settings
     */
    public function requestClientCredentialsGrant(array $scopes = [], $usingBasicAuthorization = false)
    {
        if (isset($this->settings->tokenEndpoint) == false) {
            throw new \RuntimeException('tokenEndpoint not set yet.');
        }
        $request = new Request('POST', $this->settings->tokenEndpoint);
        # if ($request->getUri()->getScheme() !== 'https') {
        #     throw new \RuntimeException('Client Credentials Grant required https.');
        # }

        $parameters = [
            'grant_type' => 'client_credentials',
        ];
        if ($scopes) {
            $parameters['scope'] = join(' ', $scopes);
        }
        if ($usingBasicAuthorization) {
            $credentials = base64_encode($this->settings->clientId . ':' . $this->settings->clientSecret);
            $request = $request->withAddedHeader('Authorization', 'Basic ' . $credentials);
        } else {
            $parameters['client_id'] = $this->settings->clientId;
            $parameters['client_secret'] = $this->settings->clientSecret;
        }
        $requestBody = QueryString::build($parameters);
        $request = $request->withBodyContents($requestBody);

        $response = $this->httpClient->send($request);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $accessToken = new AccessToken($tokenData);
        $this->setAccessToken($accessToken);
        return $this->getAccessToken();
    }

    /**
     * refresh access_token by refresh_token
     *
     * @param boolean $usingBasicAuthorization
     * @return AccessToken|null
     * @throws \Exception if invalid settings or not has refresh_token
     */
    public function refreshAccessToken($usingBasicAuthorization = false)
    {
        if (isset($this->settings->tokenEndpoint) == false) {
            throw new \RuntimeException('tokenEndpoint not set yet.');
        }
        if (!($this->accessToken)) {
            throw new \RuntimeException('not has access_token!!');
        }

        $refreshToken = strval($this->accessToken->getRefreshToken());
        if (strlen($refreshToken) < 1) {
            throw new \RuntimeException('not has refresh_token!!');
        }

        $request = new Request('POST', $this->settings->tokenEndpoint);
        # if ($request->getUri()->getScheme() !== 'https') {
        #     throw new \RuntimeException('Client Credentials Grant required https.');
        # }

        $parameters = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        $scopes = $this->accessToken->getScopes();
        if ($scopes) {
            $parameters['scope'] = join(' ', $scopes);
        }

        if ($usingBasicAuthorization) {
            $credentials = base64_encode($this->settings->clientId . ':' . $this->settings->clientSecret);
            $request = $request->withAddedHeader('Authorization', 'Basic ' . $credentials);
        } else {
            $parameters['client_id'] = $this->settings->clientId;
            $parameters['client_secret'] = $this->settings->clientSecret;
        }

        $requestBody = QueryString::build($parameters);
        $request = $request->withBodyContents($requestBody);

        $response = $this->httpClient->send($request);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $this->accessToken->update($tokenData);
        return $this->getAccessToken();
    }

    /**
     * revoke access token
     */
    public function revokeAccessToken()
    {
        if (isset($this->settings->revocationEndpoint) == false) {
            throw new \RuntimeException('revocationEndpoint not set yet.');
        }
        if ($this->accessToken) {
            $parameters = [
                'access_token' => $this->accessToken->getToken(),
            ];
            $requestBody = QueryString::build($parameters);
            $this->httpClient->request('POST', $this->settings->revocationEndpoint, ['body' => $requestBody]);
        }
        $this->accessToken = null;
    }

    /**
     * @return Response
     */
    public function getLatestResponse()
    {
        return $this->httpClient->getLatestResponse();
    }
}
