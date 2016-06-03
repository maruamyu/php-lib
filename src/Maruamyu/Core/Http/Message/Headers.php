<?php

namespace Maruamyu\Core\Http\Message;

use Maruamyu\Core\ArrayDetectionTrait;

/**
 * 独自実装 HTTPヘッダ 処理クラス
 */
class Headers
{
    use ArrayDetectionTrait;

    const LINE_END = "\r\n";

    private $data;

    /**
     * コンストラクタ
     *
     * @param mixed $initValue 初期値
     */
    public function __construct($initValue = null)
    {
        $this->data = [];

        if (is_array($initValue)) {
            if (static::isVector($initValue)) {
                foreach ($initValue as $field) {
                    $this->addFromField($field);
                }
            } else {
                foreach ($initValue as $name => $value) {
                    $this->add($name, $value);
                }
            }
        } elseif (is_string($initValue)) {
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
        $fields = $this->fields();
        if (empty($fields)) {
            return '';
        }
        return join(static::LINE_END, $this->fields()) . static::LINE_END;
    }

    /**
     * ヘッダメッセージの文字列を取得する.
     *
     * @return string ヘッダメッセージ文字列
     */
    public function toString()
    {
        return $this->__toString();
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
     * @return mixed[] 名前に対する値のリスト (存在しなかったときは空)
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
     * @param mixed $value 名前に対する値
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function set($name, $value)
    {
        $lowerName = strtolower($name);
        $this->data[$lowerName] = [];
        $this->add($name, $value);
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
        if (!$name) {
            return false;
        }
        $this->set($name, $value);
        return true;
    }

    /**
     * 値を追加する.
     * 同じ名前が存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param string $name 名前
     * @param mixed $value 名前に対する値
     * @return int count of elemets
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function add($name, $value)
    {
        $lowerName = strtolower($name);
        if (!isset($this->data[$lowerName])) {
            $this->data[$lowerName] = [];
        }
        if (static::isVector($value)) {
            foreach ($value as $vv) {
                $this->data[$lowerName][] = [$vv, $name];
            }
        } else {
            $this->data[$lowerName][] = [$value, $name];
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
        if (!$name) {
            return false;
        }
        return $this->add($name, $value);
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
    public function hasName($name)
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
     *
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
     * 名前に対する値の数を取得する.
     *
     * @param string $name 名前
     * @return int 名前に対する値の数
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function valueCount($name)
    {
        $lowerName = strtolower($name);
        return count($this->data[$lowerName]);
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
     * @param string $field ヘッダのフィールド("ヘッダ名: 値")
     * @return array|null [ヘッダ名, 値] (不正なデータのときはnull)
     */
    public static function parseField($field)
    {
        $field = trim($field);
        $delimiterPos = strpos($field, ': ', 0);
        if ($delimiterPos === false) {
            return null;
        }
        $name = trim(substr($field, 0, $delimiterPos));
        $value = trim(substr($field, ($delimiterPos + 2)));  # strlen(': ') = 2
        return [$name, $value];
    }
}
