<?php

namespace Maruamyu\Core\Http\Message;

use Maruamyu\Core\Http\StatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * 独自実装を含む PSR-7準拠 HTTPレスポンスメッセージ オブジェクト
 */
class Response extends MessageAbstract implements ResponseInterface
{
    /** @var integer */
    private $statusCode = 0;

    /** @var string */
    private $reasonPhrase = '';

    /**
     * インスタンスを初期化する.
     *
     * @param StreamInterface|resource|string $body 内容
     * @param Headers|string|array $headers ヘッダ
     * @param int $statusCode HTTPステータスコード
     * @param string $statusReasonPhrase ステータスを表す文字列("OK"等)
     * @param string $protocolVersion プロトコルバージョン
     */
    public function __construct($body = null, $headers = null, $statusCode = 0, $statusReasonPhrase = '', $protocolVersion = '')
    {
        parent::__construct();

        if (is_null($body)) {
            $this->body = null;
        } elseif ($body instanceof StreamInterface) {
            $this->body = $body;
        } elseif (is_resource($body)) {
            $this->body = new Stream($body);
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

        $statusCode = intval($statusCode, 10);
        if ($statusCode > 0) {
            $this->statusCode = $statusCode;
            $this->reasonPhrase = strval($statusReasonPhrase);
        }

        if (strlen($protocolVersion) > 0) {
            $this->protocolVersion = strval($protocolVersion);
        }
    }

    /**
     * @return boolean true if "200 OK"
     */
    public function statusCodeIsOk()
    {
        return ($this->statusCode === StatusCode::OK);
    }

    /**
     * @return int HTTPステータスコード
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $code HTTPステータスコード
     * @param string $reasonPhrase ステータスを表す文字列("OK"等)
     * @return static 指定のステータスコードを設定した新しいインスタンス
     * @throws \InvalidArgumentException コードが正しくないとき
     */
    public function withStatus($code, $reasonPhrase = '')
    {
        $newInstance = clone $this;

        $statusCode = intval($code, 10);
        if ($statusCode < 0) {
            throw new \InvalidArgumentException('invalid code: ' . $code);
        }
        $newInstance->statusCode = $statusCode;
        $newInstance->reasonPhrase = $reasonPhrase;

        return $newInstance;
    }

    /**
     * @return string ステータスを表す文字列
     */
    public function getReasonPhrase()
    {
        if (strlen($this->reasonPhrase) > 0) {
            return $this->reasonPhrase;
        } else {
            $statusCode = $this->getStatusCode();
            return static::statusCodeToReasonPhrase($statusCode);
        }
    }

    /**
     * @param int $code HTTPステータスコード
     * @return string HTTPステータスコードに応じた文字列
     */
    public static function statusCodeToReasonPhrase($code)
    {
        return StatusCode::toReasonPhrase($code);
    }
}
