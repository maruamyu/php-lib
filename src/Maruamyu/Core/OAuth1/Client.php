<?php

namespace Maruamyu\Core\OAuth1;

use Maruamyu\Core\Http\Driver\DriverFactory;
use Maruamyu\Core\Http\Driver\DriverInterface;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * OAuth 1.0 Client
 */
class Client extends CoreLogic
{
    /**
     * @var DriverFactory
     */
    protected $httpDriverFactory = null;

    /**
     * execute HTTP request with OAuth signature
     *
     * @param string $method HTTP method
     * @param string|UriInterface $uri URL
     * @param array|QueryString $params form data
     * @param bool $notUseAuthorizationHeader if true, then auth-params into QUERY_STRING or form data
     * @return Response response message
     * @throws \InvalidArgumentException if invalid args
     */
    public function doRequest($method, $uri, $params = null, $notUseAuthorizationHeader = false)
    {
        $httpRequest = $this->makeRequest($method, $uri, $params, $notUseAuthorizationHeader);
        $httpDriver = $this->getHttpDriver($httpRequest);
        return $httpDriver->execute();
    }

    /**
     * @param Request $request HTTP request message
     * @return DriverInterface HTTP driver class
     */
    protected function getHttpDriver(Request $request = null)
    {
        if (!$this->httpDriverFactory) {
            $this->httpDriverFactory = new DriverFactory();
        }
        return $this->httpDriverFactory->getDriver($request);
    }
}
