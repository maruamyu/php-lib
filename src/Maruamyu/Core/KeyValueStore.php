<?php

namespace Maruamyu\Core;

/**
 * Key-Valueストア処理クラス
 */
class KeyValueStore implements KeyValueStoreInterface
{
    use ArrayDetectionTrait;

    /** @var mixed[][] ['key' => [value1, value2, ...], ...] */
    private $data = [];

    /**
     * 内部に持っているデータを初期化する.
     */
    public function initialize()
    {
        $this->data = [];
    }

    /**
     * 値を取得する.
     *
     * @param string $key キー
     * @return mixed[] キーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function get($key)
    {
        $key = strval($key);
        if (strlen($key) < 1) {
            throw new \InvalidArgumentException('key is empty.');
        }
        if (isset($this->data[$key])) {
            return $this->data[$key];
        } else {
            return [];
        }
    }

    /**
     * 値を設定する.
     *
     * @param string $key キー
     * @param mixed $value キーに対する値
     * @param boolean $overwrite 前に保持していた値を破棄するときtrue
     * @return int 設定したあとのキーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function set($key, $value, $overwrite = false)
    {
        $key = strval($key);
        if (strlen($key) < 1) {
            throw new \InvalidArgumentException('key is empty.');
        }
        if (!(isset($this->data[$key])) || $overwrite) {
            $this->data[$key] = [];
        }
        $this->data[$key][] = $value;
        return count($this->data[$key]);
    }

    /**
     * 値を削除する.
     *
     * @param string $key キー
     * @return mixed[] 削除したキーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function delete($key)
    {
        $key = strval($key);
        if (strlen($key) < 1) {
            throw new \InvalidArgumentException('key is empty.');
        }
        $deleted = [];
        if (isset($this->data[$key])) {
            $deleted = $this->data[$key];
            unset($this->data[$key]);
        }
        return $deleted;
    }

    /**
     * キーが存在するかどうか判定する.
     *
     * @param string $key キー
     * @return boolean キーが存在するならtrue, それ以外はfalse
     */
    public function hasKey($key)
    {
        return (isset($this->data[$key]) && !(empty($this->data[$key])));
    }

    /**
     * キーに対する値の数を取得する.
     *
     * @param string $key キー
     * @return int キーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function valueCount($key)
    {
        $key = strval($key);
        if (strlen($key) < 1) {
            throw new \InvalidArgumentException('key is empty.');
        }
        if (isset($this->data[$key])) {
            return count($this->data[$key]);
        } else {
            return 0;
        }
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
     * サイズ(キーの個数)を取得する.
     *
     * @return int サイズ(キーの個数)
     * @see count()
     */
    public function size()
    {
        return $this->count();
    }

    /**
     * 空(サイズが0)かどうか判定する.
     *
     * @return boolean 空(サイズが0)ならtrue, それ以外はfalse
     * @see hasAny()
     */
    public function isEmpty()
    {
        return ($this->count() == 0);
    }

    /**
     * 空でない(サイズが0でない)かどうか判定する.
     *
     * @return boolean 空でないならtrue, それ以外はfalse
     * @see isEmpty()
     */
    public function hasAny()
    {
        return !($this->isEmpty());
    }

    /**
     * データの統合
     *
     * @param array|KeyValueStoreInterface $kvs 統合するKVSデータ
     * @param boolean $overwrite 同じキーの値のとき上書きするならtrue
     * @return int 統合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function merge($kvs, $overwrite = false)
    {
        if ($kvs instanceof KeyValueStoreInterface) {
            $this->mergeFromKeyValueStore($kvs, $overwrite);
        } elseif (is_array($kvs)) {
            $this->mergeFromArray($kvs, $overwrite);
        } else {
            throw new \InvalidArgumentException('invalid type.');
        }
        return $this->count();
    }

    /**
     * array形式のデータに変換する
     *
     * @return array 配列データ
     */
    public function toArray()
    {
        $converted = [];
        foreach ($this->keys() as $key) {
            $values = $this->get($key);
            if (count($values) == 1) {
                $converted[$key] = $values[0];
            } else {
                $converted[$key] = $values;
            }
        }
        return $converted;
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
     * データの統合(KVS)
     *
     * @param KeyValueStoreInterface $kvs 統合するKVSデータ
     * @param boolean $overwrite 同じキーの値のとき上書きするならtrue
     */
    protected function mergeFromKeyValueStore(KeyValueStoreInterface $kvs, $overwrite = false)
    {
        foreach ($kvs->keys() as $key) {
            $values = $kvs->get($key);
            if (empty($values)) {
                continue;
            }
            if ($overwrite) {
                $this->delete($key);
            }
            foreach ($values as $value) {
                $this->set($key, $value);
            }
        }
    }

    /**
     * データの統合(array)
     *
     * @param array $data 統合するKVSデータ
     * @param boolean $overwrite 同じキーの値のとき上書きするならtrue
     */
    protected function mergeFromArray(array $data, $overwrite = false)
    {
        foreach ($data as $key => $value) {
            if (static::isVector($value)) {
                if ($overwrite) {
                    $this->delete($key);
                }
                foreach ($value as $elem) {
                    $this->set($key, $elem);
                }
            } else {
                $this->set($key, $value, $overwrite);
            }
        }
    }
}
