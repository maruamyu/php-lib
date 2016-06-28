<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * 独自実装を含む PSR-7準拠 HTTPリクエストメッセージ オブジェクト
 */
class Request extends MessageAbstract implements RequestInterface
{
    use NormalizeMessageTrait;

    /**
     * リクエストメソッド
     * @var string
     */
    protected $method = 'GET';

    /**
     * リクエストURIオブジェクト
     * @var PsrUriInterface
     */
    protected $uri;

    /**
     * multipart/form-data
     * @var MultipartData[]
     */
    protected $multipartDataList = [];

    /**
     * インスタンスを初期化する.
     *
     * @param string $method メソッド
     * @param string|PsrUriInterface $uri URL
     * @param string|StreamInterface $body 内容
     * @param Headers|string|array $headers ヘッダ
     */
    public function __construct($method = 'GET', $uri = null, $body = null, $headers = null)
    {
        parent::__construct();

        $this->method = static::normalizeMethod($method);

        $this->uri = static::normalizeUri($uri);

        if (is_null($body)) {
            $this->body = null;
        } elseif ($body instanceof StreamInterface) {
            $this->body = $body;
        } elseif (is_string($body)) {
            $this->body = Stream::fromTemp($body);
        } else {
            throw new \InvalidArgumentException('invalid request body.');
        }

        if ($headers instanceof Headers) {
            $this->headers = clone $headers;
        } elseif (is_array($headers) || is_string($headers)) {
            $this->headers = new Headers($headers);
        }

        $this->multipartDataList = [];
    }

    /**
     * clone時のデータコピー.
     */
    public function __clone()
    {
        parent::__clone();
        $this->uri = clone $this->uri;
        $multipartDataList = [];
        foreach ($this->multipartDataList as $multipartData) {
            $multipartDataList[] = clone $multipartData;
        }
        $this->multipartDataList = $multipartDataList;
    }

    /**
     * @return string リクエスト対象(パス)
     */
    public function getRequestTarget()
    {
        return $this->uri->getPath();
    }

    /**
     * @param string $requestTarget リクエスト対象(パス)
     * @return self 指定のリクエスト対象を設定した新しいインスタンス
     */
    public function withRequestTarget($requestTarget)
    {
        $newInstance = clone $this;
        $newInstance->uri = $newInstance->uri->withPath($requestTarget);
        return $newInstance;
    }

    /**
     * @return PsrUriInterface Uriオブジェクト インスタンス
     */
    public function getUri()
    {
        return clone $this->uri;
    }

    /**
     * @param PsrUriInterface $uri Uriオブジェクト インスタンス
     * @param boolean $preserveHost 持っているHostヘッダを再設定しないときtrueを指定
     *   (falseだと, Uriからホストを読み取ってHostヘッダへ設定する.)
     * @return self 指定のUriを設定した新しいインスタンス
     */
    public function withUri(PsrUriInterface $uri, $preserveHost = false)
    {
        if ($preserveHost) {
            $newInstance = clone $this;
        } else {
            $newInstance = $this->withHeader('Host', $uri->getHost());
        }
        $newInstance->uri = clone $uri;
        return $newInstance;
    }

    /**
     * @return string リクエストメソッド
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method リクエストメソッド
     * @return self 指定のメソッドを設定した新しいインスタンス
     * @throws \InvalidArgumentException メソッドが正しくないとき
     */
    public function withMethod($method)
    {
        $method = strtoupper($method);
        if (strlen($method) < 1) {
            throw new \InvalidArgumentException('method is empty.');
        }
        $newInstance = clone $this;
        $newInstance->method = $method;
        return $newInstance;
    }

    /**
     * @return MultipartData[] MultipartDataリスト
     */
    public function getMultipartDataList()
    {
        return $this->multipartDataList;
    }

    /**
     * @param MultipartData[] MultipartDataリスト
     * @return self 指定のMultipartDataリストを設定した新しいインスタンス
     */
    public function withMultipartDataList(array $multipartDataList)
    {
        $newInstance = clone $this;
        $newInstance->multipartDataList = $multipartDataList;
        return $newInstance;
    }
}
