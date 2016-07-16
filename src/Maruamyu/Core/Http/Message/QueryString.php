<?php

namespace Maruamyu\Core\Http\Message;

use Maruamyu\Core\ArrayDetectionTrait;
use Maruamyu\Core\KeyValueStore;

/**
 * QUERY_STRING形式データを扱うクラス
 */
class QueryString extends KeyValueStore
{
    use ArrayDetectionTrait;

    /**
     * コンストラクタ
     *
     * @param mixed $initValue 初期値
     */
    public function __construct($initValue = null)
    {
        parent::__construct();

        if (is_array($initValue)) {
            foreach ($initValue as $key => $value) {
                if (static::isVector($value)) {
                    foreach ($value as $valueElem) {
                        $this->add($key, $valueElem);
                    }
                } else {
                    $this->add($key, $value);
                }
            }
        } elseif (is_string($initValue)) {
            # parse_str($initValue, $parsed);
            # では, hoge=hoge&hoge=hogehoge を解釈できない.
            # そのため, 独自のパーサを利用する.
            $parsed = static::parseQueryString($initValue);
            foreach ($parsed as $key => $values) {
                foreach ($values as $value) {
                    $this->add($key, $value);
                }
            }
        }
    }

    /**
     * @return string QUERY_STRING形式の文字列
     * @see toString()
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string QUERY_STRING形式の文字列
     *   注意: PHPの独自拡張形式ではない文字列を(意図的に)出力します
     */
    public function toString()
    {
        $kvpairs = [];
        $keys = $this->keys();
        foreach ($keys as $key) {
            $values = $this->get($key);
            if (empty($values)) {
                continue;
            }
            $encodedKey = rawurlencode($key);
            foreach ($values as $value) {
                $value = strval($value);
                $kvpairs[] = $encodedKey . '=' . rawurlencode($value);
            }
        }
        return join('&', $kvpairs);
    }

    /**
     * @return string OAuthの署名に利用するQUERY_STRING形式の文字列
     *   注意: PHPの独自拡張形式ではない文字列を(意図的に)出力します
     */
    public function toOAuthQueryString()
    {
        $kvpairs = [];
        $keys = $this->keys();
        sort($keys, SORT_STRING);
        foreach ($keys as $key) {
            $values = $this->get($key);
            if (empty($values)) {
                continue;
            }
            sort($values, SORT_STRING);
            $encodedKey = rawurlencode($key);
            foreach ($values as $value) {
                $value = strval($value);
                $kvpairs[] = $encodedKey . '=' . rawurlencode($value);
            }
        }
        return join('&', $kvpairs);
    }

    /**
     * @return string PHPの独自拡張形式なQUERY_STRING形式の文字列
     * @see \http_build_query
     */
    public function toPHPQueryString()
    {
        return http_build_query($this->toArray());
    }

    /**
     * @param string $boundary boundary
     * @param string $lineEnd 行末文字列(デフォルトは"\r\n")
     * @return string multipart/form-data 形式の文字列
     */
    public function toMultiPartFormData($boundary, $lineEnd = null)
    {
        $multipartFormData = '';
        if (strlen($lineEnd) < 1) {
            $lineEnd = "\r\n";
        }
        foreach ($this->keys() as $key) {
            $escapedKey = str_replace('"', '\\"', $key);
            $values = $this->get($key);
            foreach ($values as $value) {
                $multipartFormData .= '--' . $boundary . $lineEnd;
                $multipartFormData .= 'Content-Disposition: form-data; name="' . $escapedKey . '"' . $lineEnd;
                $multipartFormData .= $lineEnd;
                $multipartFormData .= $value . $lineEnd;
            }
        }
        return $multipartFormData;
    }

    /**
     * @param string $queryString QUERY_STRING形式の文字列
     *   注意: PHPの独自拡張形式は(意図的に)解釈しません
     *   (例: "key[]=value" は ['key[]' => ['value']] となります)
     * @return array パースした配列
     */
    public static function parseQueryString($queryString)
    {
        if (strlen($queryString) < 1) {
            return [];
        }
        $parsed = [];
        $queryString = str_replace('&amp;', '&', $queryString);
        $queryString = str_replace(';', '&', $queryString);
        $kvpairs = explode('&', $queryString);
        foreach ($kvpairs as $kvpair) {
            $delimiterPos = strpos($kvpair, '=', 0);
            if ($delimiterPos === false) {
                continue;
            }
            $key = rawurldecode(substr($kvpair, 0, $delimiterPos));
            if (strlen($key) < 1) {
                continue;
            }
            if (!(isset($parsed[$key]))) {
                $parsed[$key] = [];
            }
            $parsed[$key][] = rawurldecode(substr($kvpair, ($delimiterPos + 1)));  # strlen('=') = 1
        }
        return $parsed;
    }
}
