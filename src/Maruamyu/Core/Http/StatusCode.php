<?php

namespace Maruamyu\Core\Http;

/**
 * HTTP status codes
 */
class StatusCode
{
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const MOVED_PERMANENTLY = 301;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENT_REDIRECT = 308;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const INTERNAL_SERVER_ERROR = 500;
    const SERVICE_UNAVAILABLE = 503;

    /** @var string[] */
    const REASON_PHRASES = [
        # 1xx Informational
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',

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
        # 306 => 'Switch Proxy',  # (Unused)
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
        425 => 'Too Early',
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

    /** @var int */
    private $code;

    /** @var string */
    private $reasonPhrase;

    /**
     * @param int $code
     * @param string $reasonPhrase
     */
    public function __construct($code, $reasonPhrase = '')
    {
        $this->code = intval($code, 10);
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return ($this->getCode() == static::OK);
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getReasonPhrase()
    {
        if (strlen($this->reasonPhrase) > 0) {
            return $this->reasonPhrase;
        } else {
            return static::toReasonPhrase($this->getCode());
        }
    }

    /**
     * @param int $code
     * @return string
     */
    public static function toReasonPhrase($code)
    {
        $reasonPhrases = static::REASON_PHRASES;
        $code = intval($code, 10);
        if (isset($reasonPhrases[$code])) {
            return $reasonPhrases[$code];
        } else {
            return '';
        }
    }
}
