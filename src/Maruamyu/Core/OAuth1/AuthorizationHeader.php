<?php

namespace Maruamyu\Core\OAuth1;

/**
 * OAuth 1.0 Authorization Header
 */
class AuthorizationHeader
{
    private $params;

    /**
     * @param array|string $initValue Authorization Header value
     *   array  - auth-params (array)
     *   string - Authorization Header value
     */
    public function __construct($initValue = null)
    {
        if (is_array($initValue)) {
            $this->params = $initValue;
        } else {
            $this->params = static::parse($initValue);
        }
    }

    /**
     * auth-scheme
     *
     * @return string 'OAuth'
     */
    public function getScheme()
    {
        return 'OAuth';
    }

    /**
     * auth-params
     *
     * @return array auth-params
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Authorization Header value
     *
     * @return string Authorization Header value
     */
    public function getHeaderValue()
    {
        return $this->getScheme() . ' ' . static::buildAuthParams($this->params);
    }

    /**
     * build Authorization Header value
     *
     * @param array $authParams auth-params
     * @return string Authorization Header value
     */
    public static function build(array $authParams)
    {
        return 'OAuth ' . static::buildAuthParams($authParams);
    }

    /**
     * build auth-params string
     *
     * @param array $authParams auth-params
     * @return string auth-params
     */
    public static function buildAuthParams(array $authParams)
    {
        if (empty($authParams)) {
            return '';
        }
        $kvpairs = [];
        foreach ($authParams as $key => $value) {
            $key = rawurlencode($key);
            $value = rawurlencode($value);
            $kvpairs[] = '' . $key . '="' . $value . '"';
        }
        return join(', ', $kvpairs);
    }

    /**
     * parse Authorization Header value
     *
     * @param string $headerValue Authorization Header value
     * @return array auth-params
     * @note if auth-scheme != OAuth, then return empty array.
     */
    public static function parse($headerValue)
    {
        $headerValue = strval($headerValue);
        if (strlen($headerValue) < 1) {
            return [];
        }
        list($authScheme, $authParams) = explode(' ', $headerValue, 2);
        if (strcasecmp($authScheme, 'OAuth') != 0) {
            return [];
        }
        return static::parseAuthParams($authParams);
    }

    /**
     * parse auth-params string
     *
     * @param string $authParams auth-params
     * @return array auth-params
     */
    public static function parseAuthParams($authParams)
    {
        $authParams = strval($authParams);
        if (strlen($authParams) < 1) {
            return [];
        }

        $parsed = [];
        $paramsLength = strlen($authParams);
        $keyHeadPos = 0;
        while (($keyTailPos = strpos($authParams, '=', $keyHeadPos)) !== false) {
            $keyLength = ($keyTailPos - $keyHeadPos);
            $key = substr($authParams, $keyHeadPos, $keyLength);

            $valueHeadPos = $keyTailPos + 1;
            if (substr($authParams, $valueHeadPos, 1) === '"') {
                # quoted
                $valueHeadPos++;
                $valueTailPos = $valueHeadPos;
                while (($valueTailPos = strpos($authParams, '"', $valueTailPos)) !== false) {
                    $escapeChar = substr($authParams, ($valueTailPos - 1), 1);
                    if ($escapeChar !== '\\') {
                        break;
                    }
                    $valueTailPos++;
                }
                if ($valueTailPos === false) {
                    $valueTailPos = $paramsLength;
                }

                $valueLength = $valueTailPos - $valueHeadPos;
                if ($valueLength > 0) {
                    $value = substr($authParams, $valueHeadPos, $valueLength);
                    $value = str_replace('\\"', '"', $value);
                } else {
                    $value = '';
                }

                $keyHeadPos = strpos($authParams, ',', $valueTailPos);
                if ($keyHeadPos === false) {
                    $keyHeadPos = $paramsLength;
                } else {
                    $keyHeadPos = $keyHeadPos + 1;
                }

            } else {
                # not quoted
                $valueTailPos = strpos($authParams, ',', $valueHeadPos);
                if ($valueTailPos === false) {
                    $valueTailPos = $paramsLength;
                }
                $keyHeadPos = $valueTailPos + 1;

                while (substr($authParams, ($valueTailPos - 1), 1) === ' ') {
                    $valueTailPos--;
                }
                $valueLength = $valueTailPos - $valueHeadPos;
                $value = substr($authParams, $valueHeadPos, $valueLength);
            }

            $key = rawurldecode($key);
            $parsed[$key] = rawurldecode($value);

            if ($keyHeadPos >= $paramsLength) {
                break;
            }
            while (substr($authParams, $keyHeadPos, 1) === ' ') {
                $keyHeadPos++;
            }
        }

        return $parsed;
    }
}
