<?php

namespace Maruamyu\Core\OAuth;

class AuthorizationHeader
{
    private $params;

    /**
     * インスタンスを初期化する.
     *
     * @param array|string $initValue ヘッダの値
     *   array  - auth-paramsの内容(連想配列)
     *   string - Authorizationヘッダの値
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
     * auth-schemeを表す文字列を取得する.
     *
     * @return string 'OAuth'
     */
    public function getScheme()
    {
        return 'OAuth';
    }

    /**
     * auth-paramsの内容を取得する.
     *
     * @return array auth-paramsの内容
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Authorizationヘッダの値を取得する.
     *
     * @return string Authorizationヘッダの値
     */
    public function getHeaderValue()
    {
        return $this->getScheme() . ' ' . static::buildAuthParams($this->params);
    }

    /**
     * Authorizationヘッダの値を生成する.
     *
     * @param array $authParams 署名パラメータ
     * @return string Authorizationヘッダの値
     */
    public static function build(array $authParams)
    {
        return 'OAuth ' . static::buildAuthParams($authParams);
    }

    /**
     * auth-paramsの値(文字列)を生成する.
     *
     * @param array $authParams パラメータ
     * @return string auth-paramsの値(文字列)
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
     * Authorizationヘッダの値をパースする.
     *
     * @param string $headerValue Authorizationヘッダの値
     * @return array auth-paramsを配列にしたもの
     *   (auth-schemeがOAuth以外のときは空配列が返る)
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
     * auth-paramsの値(文字列)をパースする.
     *
     * @param string $authParams auth-paramsの値(文字列)
     * @return array auth-paramsを配列にしたもの
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
