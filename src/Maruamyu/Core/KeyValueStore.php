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

    public function __construct()
    {
        $this->data = [];
    }

    /**
     * キーに対する値を取得する.
     *
     * @param string $key キー
     * @return mixed[] キーに対する値のリスト (存在しなかったときは空)
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
     * キーに対する値を設定する.
     * すでに同じキーが存在している場合は, 上書きされる.
     *
     * @param string $key キー
     * @param mixed $value キーに対する値
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function set($key, $value)
    {
        $key = static::validateKey($key);
        $this->data[$key] = [$value];
    }

    /**
     * キーに対する値を追加する.
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param string $key キー
     * @param mixed $value キーに対する値
     * @return int 設定後のキーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function add($key, $value)
    {
        $key = static::validateKey($key);
        if (!(isset($this->data[$key]))) {
            $this->data[$key] = [];
        }
        $this->data[$key][] = $value;
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
        $key = static::validateKey($key);
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
     * 同じキーが存在した場合は, 引数で渡されたKVSデータの値で上書きされる.
     *
     * @param array|KeyValueStoreInterface $kvs 統合するKVSデータ
     * @return int 統合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function merge($kvs)
    {
        if ($kvs instanceof KeyValueStoreInterface) {
            $this->mergeFromKeyValueStore($kvs);
        } elseif (is_array($kvs)) {
            $this->mergeFromArray($kvs);
        } else {
            throw new \InvalidArgumentException('invalid type.');
        }
        return $this->count();
    }

    /**
     * データの結合
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param array|KeyValueStoreInterface $kvs 結合するKVSデータ
     * @return int 結合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function append($kvs)
    {
        if ($kvs instanceof KeyValueStoreInterface) {
            $this->appendFromKeyValueStore($kvs);
        } elseif (is_array($kvs)) {
            $this->appendFromArray($kvs);
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
     * 同じキーが存在した場合は, 引数で渡されたKVSデータの値で上書きされる.
     *
     * @param KeyValueStoreInterface $kvs 統合するKVSデータ
     */
    protected function mergeFromKeyValueStore(KeyValueStoreInterface $kvs)
    {
        foreach ($kvs->keys() as $key) {
            $values = $kvs->get($key);
            if (empty($values)) {
                continue;
            }
            $this->delete($key);
            foreach ($values as $value) {
                $this->add($key, $value);
            }
        }
    }

    /**
     * データの統合(array)
     * 同じキーが存在した場合は, 引数で渡されたKVSデータの値で上書きされる.
     *
     * @param array $data 統合するKVSデータ
     */
    protected function mergeFromArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (static::isVector($value)) {
                $this->delete($key);
                foreach ($value as $elem) {
                    $this->add($key, $elem);
                }
            } else {
                $this->set($key, $value);
            }
        }
    }

    /**
     * データの結合(KVS)
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param KeyValueStoreInterface $kvs 統合するKVSデータ
     */
    protected function appendFromKeyValueStore(KeyValueStoreInterface $kvs)
    {
        foreach ($kvs->keys() as $key) {
            $values = $kvs->get($key);
            if (empty($values)) {
                continue;
            }
            foreach ($values as $value) {
                $this->add($key, $value);
            }
        }
    }

    /**
     * データの結合(array)
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param array $data 統合するKVSデータ
     */
    protected function appendFromArray(array $data)
    {
        foreach ($data as $key => $value) {
            if (static::isVector($value)) {
                foreach ($value as $elem) {
                    $this->add($key, $elem);
                }
            } else {
                $this->add($key, $value);
            }
        }
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
