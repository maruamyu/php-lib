<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * 独自実装を含む PSR-7準拠 HTTPレスポンスメッセージ オブジェクト
 */
class Response extends MessageAbstract implements ResponseInterface
{
    protected static $statusCodes = [
        # 1xx Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',

        # 2xx Success
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',

        # 3xx Redirection
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',

        # 4xx Client Error
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',

        # 5xx Server Error
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * HTTPステータスコード
     */
    private $statusCode = 0;

    /**
     * ステータスを表す文字列
     */
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
        if (isset(static::$statusCodes[$code])) {
            return static::$statusCodes[$code];
        } else {
            return '';
        }
    }
}
