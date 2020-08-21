<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * Webアプリから利用するリクエストコンテキストデータ
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    protected $serverParams = [];

    /**
     * @var array
     */
    protected $cookieParams = [];

    /**
     * @var array
     */
    protected $queryParams = [];

    /**
     * @var null|array|object
     */
    protected $parsedBody;

    /**
     * @var UploadedFileInterface[]
     */
    protected $uploadedFiles = [];

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string $method
     * @param string|PsrUriInterface $uri
     * @param string|StreamInterface $body
     * @param Headers|string|array $headers
     * @param array $serverParams
     */
    public function __construct($method = 'GET', $uri = null, $body = null, $headers = null, array $serverParams = [])
    {
        parent::__construct($method, $uri, $body, $headers);
        $this->serverParams = $serverParams;
    }

    /**
     * サーバーのパラメータを返す.
     *
     * @return array $_SERVER
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * Cookieを返す.
     *
     * @return array $_COOKIE
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * 指定されたCookieデータを設定した新しいインスタンスを返す.
     *
     * @param array $cookieParams パラメータ
     * @return static パラメータを設定した新しいインスタンス
     */
    public function withCookieParams(array $cookieParams)
    {
        $newInstance = clone $this;
        $newInstance->cookieParams = $cookieParams;
        return $newInstance;
    }

    /**
     * QUERY_STRINGの内容をパースして返す.
     * ({parse_str()}を使ったPHP互換形式)
     *
     * @return array QUERY_STRINGの内容
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * 指定されたQUERY_STRINGデータを設定した新しいインスタンスを返す.
     * ({parse_str()}を使ったPHP互換形式)
     *
     * @param array $queryParams パラメータ
     * @return static パラメータを設定した新しいインスタンス
     */
    public function withQueryParams(array $queryParams)
    {
        $newInstance = clone $this;
        $newInstance->queryParams = $queryParams;
        return $newInstance;
    }

    /**
     * アップロードされたファイルのリストを取得する.
     *
     * @return UploadedFileInterface[] アップロードファイルのリスト
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * アップロードされたファイルのリストを設定した新しいインスタンスを返す.
     *
     * @param UploadedFileInterface[] $uploadedFiles アップロードファイルのリスト
     * @return static パラメータを設定した新しいインスタンス
     * @throws \InvalidArgumentException 入力データが正しくないとき
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        $newInstance = clone $this;
        $newInstance->uploadedFiles = [];
        foreach ($uploadedFiles as $uploadedFile) {
            if (!($uploadedFile instanceof UploadedFileInterface)) {
                throw new \InvalidArgumentException('invalid uploaded files.');
            }
            $newInstance->uploadedFiles[] = clone $uploadedFile;
        }
        return $newInstance;
    }

    /**
     * POSTパラメータの内容をパースして返す.
     * ({parse_str()}または{json_decode()}を使ったPHP互換形式)
     *
     * @return null|array|object POSTパラメータの内容
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * POSTパラメータの内容を設定した新しいインスタンスを返す.
     * ({parse_str()}または{json_decode()}を使ったPHP互換形式)
     *
     * @param null|array|object $parsedBody POSTデータ
     * @return static POSTデータを設定した新しいインスタンス
     * @throws \InvalidArgumentException POSTデータが正しくないとき
     */
    public function withParsedBody($parsedBody)
    {
        if (!(is_null($parsedBody)) && !(is_array($parsedBody)) && !(is_object($parsedBody))) {
            throw new \InvalidArgumentException('invalid params: ' . gettype($parsedBody));
        }
        $newInstance = clone $this;
        $newInstance->parsedBody = $parsedBody;
        return $newInstance;
    }

    /**
     * リクエストの属性情報を取得する.
     *
     * @return array Attributes derived from the request.
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * リクエストの属性値を取得する.
     *
     * @param string $name 属性名
     * @param mixed $default デフォルト値
     * @return mixed 属性値
     * @see getAttributes()
     */
    public function getAttribute($name, $default = null)
    {
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        } else {
            return $default;
        }
    }

    /**
     * リクエストの属性情報を設定した新しいインスタンスを返す.
     *
     * @param string $name 属性名
     * @param mixed $value 属性値
     * @return static 属性情報を設定した新しいインスタンス
     * @see getAttributes()
     */
    public function withAttribute($name, $value)
    {
        $newInstance = clone $this;
        $newInstance->attributes[$name] = $value;
        return $newInstance;
    }

    /**
     * リクエストの属性情報を削除した新しいインスタンスを返す.
     *
     * @param string $name 属性名
     * @return static 属性情報から属性名を削除した新しいインスタンス
     * @see getAttributes()
     */
    public function withoutAttribute($name)
    {
        $newInstance = clone $this;
        if (isset($newInstance->attributes[$name])) {
            unset($newInstance->attributes[$name]);
        }
        return $newInstance;
    }

    /**
     * @return static instance from $_SERVER, $_COOKIE, $_POST, $_GET, $_FILES
     */
    public static function fromEnvironment()
    {
        $serverRequest = new static();

        # Message protocolVersion
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            if (preg_match('#^HTTP/([0-9\\\.]+)#u', $_SERVER['SERVER_PROTOCOL'], $matches)) {
                $serverRequest->protocolVersion = strval($matches[1]);
            }
        }

        # Message headers
        $serverRequest->headers = static::getHeadersFromEnvironment();

        # Message body
        # this library required (PHP >= 5.6). php://input is re-useful.
        $serverRequest->body = Stream::fromFilePath('php://input', 'rb');

        # Request method
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $serverRequest->method = strtoupper($_SERVER['REQUEST_METHOD']);
        }

        # Request uri
        $serverRequest->uri = static::getUriFromEnvironment();

        # ServerRequest serverParams
        $serverRequest->serverParams = $_SERVER;

        # ServerRequest cookieParams
        $serverRequest->cookieParams = $_COOKIE;

        # ServerRequest queryParams
        $serverRequest->queryParams = $_GET;

        # ServerRequest parsedBody
        list($contentType) = explode(';', $_SERVER['CONTENT_TYPE'], 2);
        switch ($contentType) {
            case 'application/json':
            case 'text/javascript':
                # from JSON -> parsedBody instanceof stdClass
                $serverRequest->parsedBody = json_decode(strval($serverRequest->body));
                break;
            case 'application/xml':
            case 'text/xml':
                $serverRequest->parsedBody = simplexml_load_string(strval($serverRequest->body));
                break;
            case 'application/x-www-form-urlencoded':
            case 'multipart/form-data':
            default:
                # parsedBody is PHP original structure
                $serverRequest->parsedBody = $_POST;
        }

        # ServerRequest uploadedFiles
        $serverRequest->uploadedFiles = static::parseUploadedFiles($_FILES);

        # ServerRequest attributes
        $serverRequest->attributes = [];

        return $serverRequest;
    }

    /**
     * @return Headers
     */
    protected static function getHeadersFromEnvironment()
    {
        $headers = new Headers();

        if (function_exists('apache_request_headers')) {
            $apacheRequestHeaders = apache_request_headers();
            if ($apacheRequestHeaders) {
                foreach ($apacheRequestHeaders as $headerName => $headerValue) {
                    $headers->set($headerName, $headerValue);
                }
            }
        }

        foreach ($_SERVER as $rawHeaderName => $headerValue) {
            if (substr($rawHeaderName, 0, 5) === 'HTTP_') {
                $headerName = strtr(substr($rawHeaderName, 5), '_', '-');
                $headerName = ucwords(strtolower($headerName), '-');
                $headers->set($headerName, $headerValue);
            }
        }
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers->set('Content-Type', $_SERVER['CONTENT_TYPE']);
        }
        if (isset($_SERVER['CONTENT_LENGTH'])) {
            $headers->set('Content-Length', $_SERVER['CONTENT_LENGTH']);
        }
        if (isset($_SERVER['CONTENT_MD5'])) {
            $headers->set('Content-Md5', $_SERVER['CONTENT_MD5']);
        }

        if ($headers->has('Authorization') == false) {
            if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
                $headers->set('Authorization', $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
            } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
                $basicPassword = isset($_SERVER['PHP_AUTH_PW']) ? $_SERVER['PHP_AUTH_PW'] : '';
                $authParams = base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basicPassword);
                $headers->set('Authorization', 'Basic ' . $authParams);
            } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
                $headers->set('Authorization', $_SERVER['PHP_AUTH_DIGEST']);
            }
        }

        return $headers;
    }

    /**
     * @return Uri
     */
    protected static function getUriFromEnvironment()
    {
        $protocol = 'http';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
        } elseif (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] !== 'off')) {
            $protocol = 'https';
        }

        $protocolDefaultPort = null;
        if ($protocol === 'https') {
            $protocolDefaultPort = 443;
        } elseif ($protocol === 'http') {
            $protocolDefaultPort = 80;
        }

        $host = '';
        if (isset($_SERVER['HTTP_HOST'])) {
            $host = $_SERVER['HTTP_HOST'];
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            $host = $_SERVER['SERVER_NAME'];
        } elseif (isset($_SERVER['SERVER_ADDR'])) {
            $host = $_SERVER['SERVER_ADDR'];
        }

        $uriString = $protocol . '://' . $host;
        $hasQueryString = false;
        if (isset($_SERVER['REQUEST_URI'])) {
            $requestUriParts = explode('?', $_SERVER['REQUEST_URI'], 2);
            $uriString .= $requestUriParts[0];
            if (isset($requestUriParts[1])) {
                $hasQueryString = true;
                $uriString .= '?' . $requestUriParts[1];
            }
        }
        if (!($hasQueryString) && isset($_SERVER['QUERY_STRING'])) {
            $uriString .= '?' . strval($_SERVER['QUERY_STRING']);
        }

        $uri = new Uri($uriString);
        if (isset($_SERVER['SERVER_PORT'])) {
            $serverPort = intval($_SERVER['SERVER_PORT'], 10);
            if ($protocolDefaultPort !== $serverPort) {
                $uri = $uri->withPort($serverPort);
            }
        }

        return $uri;
    }

    /**
     * @param array $files $_FILES
     * @return UploadedFile[]
     */
    protected static function parseUploadedFiles(array $files)
    {
        if (empty($files)) {
            return [];
        }
        $parsed = [];
        foreach ($files as $values) {
            if (is_array($values['error'])) {
                $fileCount = count($values['error']);
                $attrKeys = ['name', 'type', 'tmp_name', 'error', 'size'];
                for ($i = 0; $i < $fileCount; $i++) {
                    $entry = [];
                    foreach ($attrKeys as $key) {
                        $entry[$key] = $values[$key][$i];
                    }
                    $parsed[] = new UploadedFile($entry);
                }
            } else {
                $parsed[] = new UploadedFile($values);
            }
        }
        return $parsed;
    }
}
