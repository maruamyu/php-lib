<?php

namespace Maruamyu\Core\Http\Message;

use Maruamyu\Core\ArrayDetectionTrait;
use Maruamyu\Core\KeyValueStore;

/**
 * 独自実装 HTTPヘッダ 処理クラス
 */
class Header extends KeyValueStore
{
    use ArrayDetectionTrait;

    /**
     * コンストラクタ
     *
     * @param mixed $initValue 初期値
     */
    public function __construct($initValue = null)
    {
        $this->initialize();

        if (is_array($initValue)) {
            if (static::isVector($initValue)) {
                foreach ($initValue as $field) {
                    $this->setFromField($field);
                }
            } else {
                foreach ($initValue as $key => $value) {
                    $this->set($key, $value);
                }
            }
        } elseif (is_string($initValue)) {
            $fields = explode("\r\n", $initValue);
            foreach ($fields as $field) {
                $this->setFromField($field);
            }
        }
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
        $pairs = parent::get(strtolower($name));
        if (!empty($pairs)) {
            foreach ($pairs as $pair) {
                list($value) = $pair;
                $values[] = $value;
            }
        }
        return $values;
    }

    /**
     * 値を設定する.
     *
     * @param string $name 名前
     * @param mixed $value 名前に対する値
     * @param boolean $overwrite 前に保持していた値を破棄するときtrue
     * @return int 設定したあとの名前に対する値の数
     * @throws \InvalidArgumentException 空の名前を指定したとき
     */
    public function set($name, $value, $overwrite = false)
    {
        return parent::set(strtolower($name), [$value, $name], $overwrite);
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
        return parent::delete(strtolower($name));
    }

    /**
     * ヘッダが存在するかどうか判定する.
     *
     * @param string $name 名前
     * @return boolean 存在するならtrue, それ以外はfalse
     */
    public function hasKey($name)
    {
        return parent::hasKey(strtolower($name));
    }

    /**
     * ヘッダが存在するかどうか判定する.
     *
     * @param string $name 名前
     * @return boolean 存在するならtrue, それ以外はfalse
     */
    public function hasName($name)
    {
        return $this->hasKey($name);
    }

    /**
     * 名前の一覧を取得する.
     *
     * @return string[] 名前の一覧
     */
    public function keys()
    {
        $origNames = [];
        $lowerNames = parent::keys();
        foreach ($lowerNames as $lowerName) {
            $pairs = parent::get($lowerName);
            if (!empty($pairs)) {
                list(, $origName) = $pairs[0];
                $origNames[] = $origName;
            }
        }
        return $origNames;
    }

    /**
     * 名前の一覧を取得する.
     *
     * @return string[] 名前の一覧
     */
    public function names()
    {
        return $this->keys();
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
        $lowerNames = parent::keys();
        foreach ($lowerNames as $lowerName) {
            $pairs = parent::get($lowerName);
            foreach ($pairs as $pair) {
                list($value, $origName) = $pair;
                $fields[] = $origName . ': ' . $value;
            }
        }
        return $fields;
    }

    /**
     * メッセージヘッダのフィールドから値を設定する.
     *
     * @param string $field ヘッダのフィールド("ヘッダ名: 値")
     * @return int|false 設定できたとき:設定後の名前に対する値の数, 設定できなかったとき:false
     */
    public function setFromField($field)
    {
        $field = trim($field);
        $delimiterPos = strpos(trim($field), ': ', 0);
        if ($delimiterPos === false) {
            return false;
        }
        $name = substr($field, 0, $delimiterPos);
        $value = substr($field, ($delimiterPos + 2));
        return $this->set($name, $value);
    }
}
