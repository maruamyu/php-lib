<?php

namespace Maruamyu\Core\OAuth2;

use Maruamyu\Core\Base64Url;
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
    /** OpenID Connect Core 1.0 implements */
    use OpenIDExtendsTrait;

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
        $this->httpClient = null;  # see getHttpClient()
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
     * @return AccessToken|null
     * @throws \Exception if invalid settings
     * @see hasValidAccessToken()
     * @see refreshAccessToken()
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
        return $this->getHttpClient()->send($request);
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
        return $this->getHttpClient()->send($request);
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
    public function startAuthorizationCodeGrant(
        array $scopes = [],
        $redirectUrl = null,
        $state = null,
        array $optionalParameters = []
    ) {
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
        return strval($url->withQueryString($parameters));
    }

    /**
     * Authorization Code Grant : exchange code to access_token
     *
     * @note update holding AccessToken if succeeded
     * @param string $code
     * @param string|UriInterface $redirectUrl
     * @param string $state
     * @param array $optionalParameters
     * @return AccessToken|null
     * @throws \Exception if invalid settings or arguments
     */
    public function finishAuthorizationCodeGrant(
        $code,
        $redirectUrl = null,
        $state = null,
        array $optionalParameters = []
    ) {
        if (isset($this->settings->tokenEndpoint) == false) {
            throw new \RuntimeException('tokenEndpoint not set yet.');
        }

        $parameters = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => strval($redirectUrl),
        ];
        if ($state) {
            $parameters['state'] = $state;
        }
        if ($optionalParameters) {
            $parameters = array_merge($parameters, $optionalParameters);
        }

        $request = $this->makePostRequestWithClientCredentials($this->settings->tokenEndpoint, $parameters);
        $response = $this->getHttpClient()->send($request);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $accessToken = new AccessToken($tokenData);
        $this->setAccessToken($accessToken);
        return $this->getAccessToken();
    }

    /**
     * Authorization Code Grant with PKCE (RFC 7636) : generate Authorization URL
     *
     * @param string[] $scopes
     * @param string|UriInterface $redirectUrl
     * @param string $codeVerifier
     * @param string $codeChallengeMethod 'plain' or 'S256'
     * @param array $optionalParameters
     * @return string Authorization URL
     * @throws \Exception if invalid settings or arguments
     */
    public function startAuthorizationCodeGrantWithPkce(
        array $scopes = [],
        $redirectUrl = null,
        $codeVerifier = null,
        $codeChallengeMethod = 'S256',
        array $optionalParameters = []
    ) {
        if (isset($optionalParameters) == false) {
            $optionalParameters = [];
        }
        if (strlen($codeVerifier) < 43 || strlen($codeVerifier) > 128) {
            throw new \DomainException('code_verifier is too short or long');
        }
        switch ($codeChallengeMethod) {
            case 'plain':
                $optionalParameters['code_challenge'] = $codeVerifier;
                break;
            case 'S256':
                $optionalParameters['code_challenge'] = Base64Url::encode(hash('sha256', $codeVerifier, true));
                break;
            default:
                throw new \DomainException('invalid code_challenge_method=' . $codeChallengeMethod . '');
        }
        $optionalParameters['code_challenge_method'] = $codeChallengeMethod;

        return $this->startAuthorizationCodeGrant($scopes, $redirectUrl, null, $optionalParameters);
    }

    /**
     * finish Authorization Code Grant with PKCE (RFC 7636) : exchange code to access_token
     *
     * @note update holding AccessToken if succeeded
     * @param string $code
     * @param string|UriInterface $redirectUrl
     * @param string $codeVerifier
     * @param array $optionalParameters
     * @return AccessToken|null
     * @throws \Exception if invalid settings or arguments
     */
    public function finishAuthorizationCodeGrantWithPkce(
        $code,
        $redirectUrl = null,
        $codeVerifier = null,
        array $optionalParameters = []
    ) {
        if (isset($optionalParameters) == false) {
            $optionalParameters = [];
        }
        if (strlen($codeVerifier) < 43 || strlen($codeVerifier) > 128) {
            throw new \DomainException('code_verifier is too short or long');
        }
        $optionalParameters['code_verifier'] = $codeVerifier;

        return $this->finishAuthorizationCodeGrant($code, $redirectUrl, null, $optionalParameters);
    }

    /**
     * start Implicit Grant
     *
     * @param string[] $scopes
     * @param string|UriInterface $redirectUrl
     * @param string $state
     * @param array $optionalParameters
     * @return string Authorization URL
     * @throws \Exception if invalid settings
     */
    public function startImplicitGrant(array $scopes = [], $redirectUrl = null, $state = null, array $optionalParameters = [])
    {
        if (isset($this->settings->authorizationEndpoint) == false) {
            throw new \RuntimeException('authorizationEndpoint not set yet.');
        }
        $parameters = [
            'response_type' => 'token',
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
        return strval($url->withQueryString($parameters));
    }

    /**
     * Resource Owner Password Credentials Grant
     *
     * @note update holding AccessToken if succeeded
     * @param string $username
     * @param string $password
     * @param string[] $scopes
     * @return AccessToken|null
     * @throws \Exception if invalid settings
     */
    public function requestResourceOwnerPasswordCredentialsGrant($username, $password, array $scopes = [])
    {
        if (isset($this->settings->tokenEndpoint) == false) {
            throw new \RuntimeException('tokenEndpoint not set yet.');
        }

        $parameters = [
            'grant_type' => 'password',
            'username' => $username,
            'password' => $password,
        ];
        if ($scopes) {
            $parameters['scope'] = join(' ', $scopes);
        }

        $request = $this->makePostRequestWithClientCredentials($this->settings->tokenEndpoint, $parameters);
        $response = $this->getHttpClient()->send($request);
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
     * @return AccessToken|null
     * @throws \Exception if invalid settings
     */
    public function requestClientCredentialsGrant(array $scopes = [])
    {
        if (isset($this->settings->tokenEndpoint) == false) {
            throw new \RuntimeException('tokenEndpoint not set yet.');
        }

        $parameters = [
            'grant_type' => 'client_credentials',
        ];
        if ($scopes) {
            $parameters['scope'] = join(' ', $scopes);
        }

        $request = $this->makePostRequestWithClientCredentials($this->settings->tokenEndpoint, $parameters);
        $response = $this->getHttpClient()->send($request);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $accessToken = new AccessToken($tokenData);
        $this->setAccessToken($accessToken);
        return $this->getAccessToken();
    }

    /**
     * HTTP Request of
     * JSON Web Token Profile Authorization Grants (RFC 7523)
     *
     * @param JsonWebKey $jsonWebKey private key
     * @param string $iss
     * @param string $sub
     * @param int $expireSec expire(seconds)
     * @param string[] $scopes list of scopes
     * @return AccessToken|null
     * @throws \Exception if failed
     */
    public function requestJwtBearerGrant(
        JsonWebKey $jsonWebKey,
        $issuer,
        $subject,
        $expireAtTimestamp,
        array $scopes = [],
        array $optionalParameters = []
    ) {
        $request = $this->makeJwtBearerGrantRequest($jsonWebKey, $issuer, $subject, $expireAtTimestamp, $scopes, $optionalParameters);
        $response = $this->getHttpClient()->send($request);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $accessToken = new AccessToken($tokenData);
        $this->setAccessToken($accessToken);
        return $this->getAccessToken();
    }


    /**
     * make HTTP Request of
     * JSON Web Token Profile Authorization Grants (RFC 7523)
     *
     * @param JsonWebKey $jsonWebKey private key
     * @param string $issuer
     * @param string $subject
     * @param int $expireAtTimestamp
     * @param string[] $scopes
     * @param string[] $optionalParameters
     * @return Request
     * @throws \Exception if failed
     */
    public function makeJwtBearerGrantRequest(
        JsonWebKey $jsonWebKey,
        $issuer,
        $subject,
        $expireAtTimestamp,
        array $scopes = [],
        array $optionalParameters = []
    ) {
        if (!($jsonWebKey->hasPrivateKey())) {
            throw new \RuntimeException('not has private key.');
        }

        $jwtClaimSet = [
            'iss' => $issuer,
            'sub' => $subject,
            'aud' => $this->settings->tokenEndpoint,
            'exp' => $expireAtTimestamp,
        ];
        if ($scopes) {
            $jwtClaimSet['scope'] = join(' ', $scopes);
        }
        if ($optionalParameters) {
            $jwtClaimSet = array_merge($jwtClaimSet, $optionalParameters);
        }

        $queryParameters = [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => JsonWebToken::build($jwtClaimSet, $jsonWebKey),
        ];
        $requestBody = QueryString::build($queryParameters);

        return new Request('POST', $this->settings->tokenEndpoint, $requestBody);
    }

    /**
     * refresh access_token by refresh_token
     *
     * @return AccessToken|null
     * @throws \Exception if invalid settings or not has refresh_token
     */
    public function refreshAccessToken()
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

        $parameters = [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        $scopes = $this->accessToken->getScopes();
        if ($scopes) {
            $parameters['scope'] = join(' ', $scopes);
        }

        $request = $this->makePostRequestWithClientCredentials($this->settings->tokenEndpoint, $parameters);
        $response = $this->getHttpClient()->send($request);
        if ($response->statusCodeIsOk() == false) {
            return null;
        }
        $tokenData = json_decode($response->getBody(), true);
        $this->accessToken->update($tokenData);
        return $this->getAccessToken();
    }

    /**
     * revoke access token
     *
     * @return boolean true if revoked
     * @throws \Exception if invalid settings
     */
    public function revokeAccessToken()
    {
        if (!($this->accessToken)) {
            return false;
        }
        return $this->requestTokenRevocation($this->accessToken->getToken(), 'access_token');
    }

    /**
     * revoke refresh token
     *
     * @return boolean true if revoked
     * @throws \Exception if invalid settings
     */
    public function revokeRefreshToken()
    {
        if (!($this->accessToken)) {
            return false;
        }
        return $this->requestTokenRevocation($this->accessToken->getRefreshToken(), 'refresh_token');
    }

    /**
     * token revocation request (RFC 7009)
     *
     * @param string $token
     * @param string $tokenTypeHint 'access_token' or 'refresh_token'
     * @return boolean true if revoked
     * @throws \Exception if invalid settings
     */
    protected function requestTokenRevocation($token, $tokenTypeHint = '')
    {
        if (isset($this->settings->revocationEndpoint) == false) {
            throw new \RuntimeException('revocationEndpoint not set yet.');
        }

        $parameters = [
            'token' => $token,
        ];
        if (strlen($tokenTypeHint) > 0) {
            $parameters['token_type_hint'] = strval($tokenTypeHint);
        }
        if ($this->settings->isRequiredClientCredentialsOnRevocationRequest) {
            $request = $this->makePostRequestWithClientCredentials($this->settings->revocationEndpoint, $parameters);
        } else {
            $requestBody = QueryString::build($parameters);
            $requestHeaders = ['Content-Type' => 'application/x-www-form-urlencoded'];
            $request = new Request('POST', $this->settings->revocationEndpoint, $requestBody, $requestHeaders);
        }
        $response = $this->getHttpClient()->send($request);
        if ($response->statusCodeIsOk()) {
            $this->accessToken = null;
            return true;
        } else {
            return false;
        }
    }

    /**
     * token introspection request (RFC 7662)
     * use Bearer Authorization if has access_token
     * use Client Credentials Authorization if not has access_token
     *
     * @param string $token
     * @param string $tokenTypeHint 'access_token' or 'refresh_token'
     * @return array
     * @throws \Exception if invalid settings
     */
    public function requestTokenIntrospection($token, $tokenTypeHint = '')
    {
        if (isset($this->settings->tokenIntrospectionEndpoint) == false) {
            throw new \RuntimeException('tokenIntrospectionEndpoint not set yet.');
        }

        $parameters = [
            'token' => $token,
        ];
        if (strlen($tokenTypeHint) > 0) {
            $parameters['token_type_hint'] = strval($tokenTypeHint);
        }

        if ($this->accessToken) {
            # has access_token
            $requestBody = QueryString::build($parameters);
            $request = $this->makeRequest('POST', $this->settings->tokenIntrospectionEndpoint, $requestBody)
                ->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded');
        } else {
            # not has access_token
            $request = $this->makePostRequestWithClientCredentials($this->settings->tokenIntrospectionEndpoint, $parameters);
        }

        $response = $this->getHttpClient()->send($request);
        $responseBody = strval($response->getBody());
        return json_decode($responseBody, true);
    }

    /**
     * @param string|UriInterface $url
     * @param array $parameters
     * @return Request
     * @internal
     */
    protected function makePostRequestWithClientCredentials($url, array $parameters = [])
    {
        if (empty($parameters)) {
            $parameters = [];
        }
        $request = new Request('POST', $url);
        # if ($request->getUri()->getScheme() !== 'https') {
        #     throw new \RuntimeException('required https if including client credentials');
        # }

        if ($this->settings->isUseBasicAuthorizationOnClientCredentialsRequest) {
            $credentials = base64_encode($this->settings->clientId . ':' . $this->settings->clientSecret);
            $request = $request->withAddedHeader('Authorization', 'Basic ' . $credentials);
        } else {
            $parameters['client_id'] = $this->settings->clientId;
            $parameters['client_secret'] = $this->settings->clientSecret;
        }

        if (empty($parameters) == false) {
            $request = $request->withAddedHeader('Content-Type', 'application/x-www-form-urlencoded')
                ->withBodyContents(QueryString::build($parameters));
        }

        return $request;
    }

    /**
     * @return Response
     */
    public function getLatestResponse()
    {
        return $this->getHttpClient()->getLatestResponse();
    }

    /**
     * @return HttpClient
     */
    protected function getHttpClient()
    {
        if (isset($this->httpClient) == false) {
            $this->httpClient = static::createHttpClientInstance();
        }
        return $this->httpClient;
    }

    /**
     * @return HttpClient
     * @internal
     */
    protected static function createHttpClientInstance()
    {
        return new HttpClient();
    }
}
