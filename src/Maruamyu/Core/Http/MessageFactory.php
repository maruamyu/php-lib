<?php

namespace Maruamyu\Core\Http;

use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;
use Maruamyu\Core\Http\Message\ServerRequest;
use Maruamyu\Core\Http\Message\Stream;
use Maruamyu\Core\Http\Message\UploadedFile;
use Maruamyu\Core\Http\Message\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;

/**
 * PSR-17: HTTP Factories
 *
 * implements
 *     RequestFactoryInterface,
 *     ResponseFactoryInterface,
 *     ServerRequestFactoryInterface,
 *     StreamFactoryInterface,
 *     UploadedFileFactoryInterface,
 *     UriFactoryInterface
 * required PHP >= 7.1
 *
 * @see https://www.php-fig.org/psr/psr-17/
 */
class MessageFactory
{
    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @return RequestInterface
     */
    public function createRequest($method, $uri)
    {
        return new Request($method, $uri);
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     */
    public function createResponse($code = 200, $reasonPhrase = '')
    {
        return new Response(null, null, $code, $reasonPhrase);
    }

    /**
     * @param string $method
     * @param UriInterface|string $uri
     * @param array $serverParams
     * @return ServerRequestInterface
     */
    public function createServerRequest($method, $uri, array $serverParams = [])
    {
        if (empty($method)) {  # '0' is invalid method
            if (!(empty($serverParams['REQUEST_METHOD']))) {
                $method = $serverParams['REQUEST_METHOD'];
            } else {
                throw new \InvalidArgumentException('HTTP method is empty');
            }
        }
        return new ServerRequest($method, $uri, null, null, $serverParams);
    }

    /**
     * @param string $content
     * @return StreamInterface
     */
    public function createStream($content = '')
    {
        return Stream::fromTemp($content);
    }

    /**
     * @param string $filename
     * @param string $mode
     * @return StreamInterface
     */
    public function createStreamFromFile($filename, $mode = 'r')
    {
        return Stream::fromFilePath($filename, $mode);
    }

    /**
     * @param resource $resource
     * @return StreamInterface
     */
    public function createStreamFromResource($resource)
    {
        return new Stream($resource);
    }

    /**
     * @param StreamInterface $stream
     * @param int|null $size
     * @param int $error
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     * @return UploadedFileInterface
     */
    public function createUploadedFile(StreamInterface $stream, $size = null, $error = UPLOAD_ERR_OK, $clientFilename = null, $clientMediaType = null)
    {
        $fileEntry = [
            'tmp_name' => '',
            'error' => $error,
            'size' => $size,
            'name' => $clientFilename,
            'type' => $clientMediaType,
        ];
        return new UploadedFile($fileEntry, $stream);
    }

    /**
     * @param string $uri
     * @return UriInterface
     */
    public function createUri($uri = '')
    {
        return new Uri($uri);
    }
}
