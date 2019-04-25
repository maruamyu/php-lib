<?php

namespace Maruamyu\Core\Http\Message;

/**
 * 独自実装 HTTPヘッダ 処理クラス
 */
class Headers
{
    const LINE_END = "\r\n";

    private $data;

    /**
     * コンストラクタ
     *
     * @param string|array $initValue 初期値
     */
    public function __construct($initValue = null)
    {
        $this->data = [];

        if (is_array($initValue)) {
            foreach ($initValue as $key => $value) {
                if (is_string($key)) {  # assoc of name => values
                    $this->add($key, $value);
                } else {  # vector of fields
                    $this->addFromField($value);
                }
            }
        } elseif (is_string($initValue)) {
            # header string
            $fields = explode(static::LINE_END, $initValue);
            foreach ($fields as $field) {
                $this->addFromField($field);
            }
        }
    }

    /**
     * ヘッダメッセージの文字列を取得する.
     *
     * @return string ヘッダメッセージ文字列
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * ヘッダメッセージの文字列を取得する.
     *
     * @return string ヘッダメッセージ文字列
     */
    public function toString()
    {
        $fields = $this->fields();
        if (empty($fields)) {
            return '';
        }
        return join(static::LINE_END, $fields) . static::LINE_END;
    }

    /**
     * 値を取得する.
     *
     * @return array 名前に対する値のリスト (存在しなかったときは空)
     */
    public function toArray()
    {
        $data = [];
        foreach ($this->data as $lowerName => $pairs) {
            if (empty($pairs)) {
                continue;
            }
            $values = [];
            foreach ($pairs as $pair) {
                list($value) = $pair;
                $values[] = $value;
            }
            list(, $origName) = $pairs[0];
            $data[$origName] = $values;
        }
        return $data;
    }

    /**
     * 値を取得する.
     *
     * @param string $name 名前
     * @return string[] 名前に対する値のリスト (存在しなかったときは空)
     */
    public function get($name)
    {
        $values = [];
        $lowerName = strtolower($name);
        if (isset($this->data[$lowerName])) {
            foreach ($this->data[$lowerName] as $pair) {
                list($value) = $pair;
                $values[] = $value;
            }
        }
        return $values;
    }

    /**
     * 値を設定する.
     * すでに同じ名前の値が存在している場合は, 上書きされる.
     *
     * @param string $name 名前
     * @param mixed $values 名前に対する値
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function set($name, $values)
    {
        $lowerName = strtolower($name);
        $this->data[$lowerName] = [];
        $this->add($name, $values);
    }

    /**
     * メッセージヘッダのフィールドから値を設定する.
     * すでに同じ名前の値が存在している場合は, 上書きされる.
     *
     * @param string $field ヘッダのフィールド("ヘッダ名: 値")
     * @return boolean 設定できたときはtrue, それ以外はfalse
     * @see set()
     */
    public function setFromField($field)
    {
        list($name, $value) = static::parseField($field);
        if (strlen($name) > 0) {
            $this->set($name, $value);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 値を追加する.
     * 同じ名前が存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param string $name 名前
     * @param string|string[] $values 名前に対する値
     * @return int count of elemets
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function add($name, $values)
    {
        $lowerName = strtolower($name);
        if (!isset($this->data[$lowerName])) {
            $this->data[$lowerName] = [];
        }
        if (is_array($values)) {
            foreach ($values as $value) {
                $this->data[$lowerName][] = [$value, $name];
            }
        } else {
            $this->data[$lowerName][] = [$values, $name];
        }
        return count($this->data[$lowerName]);
    }

    /**
     * メッセージヘッダのフィールドから値を追加する.
     * 同じ名前が存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param string $field ヘッダのフィールド("ヘッダ名: 値")
     * @return int|false 設定できたとき:設定後の名前に対する値の数, 設定できなかったとき:false
     * @see add()
     */
    public function addFromField($field)
    {
        list($name, $value) = static::parseField($field);
        if (strlen($name) > 0) {
            return $this->add($name, $value);
        } else {
            return false;
        }
    }

    /**
     * 値を削除する.
     *
     * @param string $name 名前
     * @return mixed[] 削除した名前に対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function delete($name)
    {
        $deletedValues = [];
        $lowerName = strtolower($name);
        if (isset($this->data[$lowerName])) {
            foreach ($this->data[$lowerName] as $pair) {
                list($value) = $pair;
                $deletedValues[] = $value;
            }
            unset($this->data[$lowerName]);
        }
        return $deletedValues;
    }

    /**
     * ヘッダが存在するかどうか判定する.
     *
     * @param string $name 名前
     * @return boolean 存在するならtrue, それ以外はfalse
     */
    public function has($name)
    {
        $lowerName = strtolower($name);
        return isset($this->data[$lowerName]);
    }

    /**
     * 名前の一覧を取得する.
     *
     * @return string[] 名前の一覧
     */
    public function names()
    {
        $origNames = [];
        foreach ($this->data as $lowerName => $pairs) {
            if (!empty($pairs)) {
                list(, $origName) = $pairs[0];
                $origNames[] = $origName;
            }
        }
        return $origNames;
    }

    /**
     * メッセージヘッダのフィールド一覧を取得する.
     * ("ヘッダ名: 値" の配列が返る)
     *
     * @return string[] ヘッダのフィールド一覧
     */
    public function fields()
    {
        $fields = [];
        foreach ($this->data as $lowerName => $pairs) {
            foreach ($pairs as $pair) {
                list($value, $origName) = $pair;
                $fields[] = $origName . ': ' . $value;
            }
        }
        return $fields;
    }

    /**
     * ヘッダの行数を取得する.
     *
     * @return int ヘッダの行数
     */
    public function count()
    {
        $count = 0;
        foreach ($this->data as $lowerName => $pairs) {
            $count += count($pairs);
        }
        return $count;
    }

    /**
     * 空(サイズが0)かどうか判定する.
     *
     * @return boolean 空(サイズが0)ならtrue, それ以外はfalse
     * @see hasAny()
     */
    public function isEmpty()
    {
        return !($this->hasAny());
    }

    /**
     * 空でない(サイズが0でない)かどうか判定する.
     *
     * @return boolean 空でないならtrue, それ以外はfalse
     * @see isEmpty()
     */
    public function hasAny()
    {
        return (count($this->data) > 0);
    }

    /**
     * データの統合
     * 同じ名前のヘッダ値が存在した場合は, 引数で渡されたヘッダデータの値で上書きされる.
     *
     * @param array|Headers $headers 統合するヘッダデータ
     * @throws \InvalidArgumentException 指定されたヘッダデータの形式が正しくないとき
     */
    public function merge($headers)
    {
        if ($headers instanceof Headers) {
            $this->data = array_merge($this->data, $headers->data);
        } elseif (is_array($headers)) {
            $this->merge(new static($headers));
        } else {
            throw new \InvalidArgumentException('invalid type.');
        }
    }

    /**
     * データの結合
     * 同じ名前のヘッダ値が存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param array|Headers $headers 統合するヘッダデータ
     * @throws \InvalidArgumentException 指定されたヘッダデータの形式が正しくないとき
     */
    public function append($headers)
    {
        if ($headers instanceof Headers) {
            foreach ($headers->data as $lowerName => $pairs) {
                foreach ($pairs as $pair) {
                    list($value, $origName) = $pair;
                    $this->add($origName, $value);
                }
            }
        } elseif (is_array($headers)) {
            $this->append(new static($headers));
        } else {
            throw new \InvalidArgumentException('invalid type.');
        }
    }

    /**
     * ヘッダのフィールドを名前と値に分割する.
     *
     * @param string $field "name: value"
     * @return array [name, value]
     */
    public static function parseField($field)
    {
        $delimiterPos = strpos($field, ': ', 0);
        if ($delimiterPos === false) {
            return ['', ''];
        }
        $name = trim(substr($field, 0, $delimiterPos));
        $value = trim(substr($field, ($delimiterPos + 2)));  # strlen(': ') = 2
        return [$name, $value];
    }
}
