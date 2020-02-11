<?php

namespace Maruamyu\Core\Http\Message;

/**
 * QUERY_STRING 処理クラス
 */
class QueryString
{
    /** @var string[][] ['key' => ['value1', 'value2', ...], ...] */
    private $data;

    /**
     * @param string|array|self $initValue 初期値
     */
    public function __construct($initValue = null)
    {
        $this->data = [];

        if (($initValue instanceof self) || is_array($initValue)) {
            $this->merge($initValue);
        } elseif (is_string($initValue)) {
            $this->data = static::parse($initValue);
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
        return static::build($this->data);
    }

    /**
     * @return string OAuthの署名に利用するQUERY_STRING形式の文字列
     *   注意: PHPの独自拡張形式ではない文字列を(意図的に)出力します
     */
    public function toOAuthQueryString()
    {
        return static::buildForOAuth1($this->data);
    }

    /**
     * @return string PHPの独自拡張形式なQUERY_STRING形式の文字列
     * @see \http_build_query()
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
        foreach ($this->data as $key => $values) {
            $escapedKey = str_replace('"', '\\"', $key);
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
     * @return array
     */
    public function toArray()
    {
        $dst = [];
        foreach ($this->data as $key => $values) {
            if (empty($values)) {
                continue;
            }
            if (count($values) >= 2) {
                $dst[$key] = $values;
            } else {
                $dst[$key] = $values[0];
            }
        }
        return $dst;
    }

    /**
     * JSON文字列の生成
     *
     * @return string JSON文字列
     */
    public function toJson()
    {
        return json_encode($this->toArray());
    }

    /**
     * @param string $key キー
     * @return bool 値が存在するならtrue, 存在しないならfalse
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * キーの一覧を取得する.
     *
     * @return string[] キーの一覧
     */
    public function keys()
    {
        return array_keys($this->data);
    }

    /**
     * サイズ(キーの個数)を取得する.
     *
     * @return int サイズ(キーの個数)
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * 空(サイズが0)かどうか判定する.
     *
     * @return bool 空(サイズが0)ならtrue, それ以外はfalse
     * @see hasAny()
     */
    public function isEmpty()
    {
        return ($this->count() == 0);
    }

    /**
     * 空でない(サイズが0でない)かどうか判定する.
     *
     * @return bool 空でないならtrue, それ以外はfalse
     * @see isEmpty()
     */
    public function hasAny()
    {
        return !($this->isEmpty());
    }

    /**
     * キーに対する値を取得する.
     *
     * @param string $key キー
     * @return string[] キーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function get($key)
    {
        $key = static::validateKey($key);
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return [];
        }
    }

    /**
     * @param string $key
     * @param string $glue
     * @return string
     */
    public function getString($key, $glue = '')
    {
        if (isset($this->data[$key])) {
            return join($glue, $this->data[$key]);
        } else {
            return '';
        }
    }

    /**
     * @param string $key
     * @return string
     */
    public function getFirst($key)
    {
        if (isset($this->data[$key], $this->data[$key][0])) {
            return $this->data[$key][0];
        } else {
            return '';
        }
    }

    /**
     * キーに対する値を設定する.
     * すでに同じキーが存在している場合は, 上書きされる.
     *
     * @param string $key キー
     * @param string|string[] $values キーに対する値
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function set($key, $values)
    {
        $key = static::validateKey($key);
        if (is_array($values)) {
            $this->data[$key] = $values;
        } else {
            $this->data[$key] = [$values];
        }
    }

    /**
     * キーに対する値を追加する.
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param string $key キー
     * @param string|string[] $values キーに対する値
     * @return int 設定後のキーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function add($key, $values)
    {
        $key = static::validateKey($key);
        if (!(isset($this->data[$key]))) {
            $this->data[$key] = [];
        }
        if (is_array($values)) {
            $this->data[$key] = array_merge($this->data[$key], $values);
        } else {
            $this->data[$key][] = $values;
        }
        return count($this->data[$key]);
    }

    /**
     * キーに対する値を全て削除する.
     *
     * @param string $key キー
     * @return mixed[] 削除したキーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function delete($key)
    {
        $key = static::validateKey($key);
        $deleted = [];
        if (isset($this->data[$key])) {
            $deleted = $this->data[$key];
            unset($this->data[$key]);
        }
        return $deleted;
    }

    /**
     * データの統合
     * 同じキーが存在した場合は, 引数で渡されたデータの値で上書きされる.
     *
     * @param array|self $parameters
     * @return int 統合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function merge($parameters)
    {
        if ($parameters instanceof self) {
            foreach ($parameters->data as $key => $values) {
                $this->set($key, $values);
            }
        } elseif (is_array($parameters)) {
            foreach ($parameters as $key => $values) {
                $this->set($key, $values);
            }
        } else {
            throw new \InvalidArgumentException('invalid data type.');
        }
        return $this->count();
    }

    /**
     * データの結合
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param array|self $parameters
     * @return int 統合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function append($parameters)
    {
        if ($parameters instanceof self) {
            foreach ($parameters->data as $key => $values) {
                $this->add($key, $values);
            }
        } elseif (is_array($parameters)) {
            foreach ($parameters as $key => $values) {
                $this->add($key, $values);
            }
        } else {
            throw new \InvalidArgumentException('invalid data type.');
        }
        return $this->count();
    }

    /**
     * @param string $queryString QUERY_STRING形式の文字列
     *   注意: PHPの独自拡張形式は(意図的に)解釈しません
     *   (例: "key[]=value" は ['key[]' => ['value']] となります)
     * @return string[][] パースした配列
     */
    public static function parse($queryString)
    {
        $parameters = [];
        $kvpairs = explode('&', $queryString);
        foreach ($kvpairs as $kvpair) {
            if (strlen($kvpair) < 1) {
                continue;
            }
            $delimiterPos = strpos($kvpair, '=');
            if ($delimiterPos === false) {
                $key = urldecode($kvpair);
                $value = '';
            } else {
                $key = urldecode(substr($kvpair, 0, $delimiterPos));
                $value = urldecode(substr($kvpair, ($delimiterPos + 1)));
            }
            if (strlen($key) < 1) {
                continue;
            }
            if (!isset($parameters[$key])) {
                $parameters[$key] = [];
            }
            $parameters[$key][] = $value;
        }
        return $parameters;
    }

    /**
     * @param string[][] $parameters
     * @return string
     */
    public static function build(array $parameters)
    {
        $kvpairs = [];
        foreach ($parameters as $key => $values) {
            $encodedKey = rawurlencode($key);
            if (is_array($values)) {
                foreach ($values as $value) {
                    $kvpairs[] = $encodedKey . '=' . rawurlencode(strval($value));
                }
            } else {
                $kvpairs[] = $encodedKey . '=' . rawurlencode($values);
            }
        }
        return join('&', $kvpairs);
    }

    /**
     * for OAuth1.0 base_string
     *
     * @param string[][] $parameters
     * @return string
     */
    public static function buildForOAuth1(array $parameters)
    {
        $kvpairs = [];
        $keys = array_keys($parameters);
        sort($keys, SORT_STRING);
        foreach ($keys as $key) {
            $encodedKey = rawurlencode($key);
            if (is_array($parameters[$key])) {
                $values = $parameters[$key];
                sort($values, SORT_STRING);
                foreach ($values as $value) {
                    $kvpairs[] = $encodedKey . '=' . rawurlencode(strval($value));
                }
            } else {
                $kvpairs[] = $encodedKey . '=' . rawurlencode($parameters[$key]);
            }
        }
        return join('&', $kvpairs);
    }

    /**
     * @param string $key
     * @return string $key
     * @throws \InvalidArgumentException if empty
     */
    protected static function validateKey($key)
    {
        $key = strval($key);
        if (strlen($key) < 1) {
            throw new \InvalidArgumentException('key is empty.');
        }
        return $key;
    }
}
