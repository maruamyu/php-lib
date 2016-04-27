<?php

namespace Maruamyu\Core;

/**
 * Key-Valueストア処理クラス インタフェース
 */
interface KeyValueStoreInterface
{
    /**
     * 内部に持っているデータを初期化する.
     */
    public function initialize();

    /**
     * 値を取得する.
     *
     * @param string $key キー
     * @return mixed[] キーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function get($key);

    /**
     * 値を設定する.
     *
     * @param string $key キー
     * @param mixed $value キーに対する値
     * @return int 設定後のキーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function set($key, $value);

    /**
     * 値を削除する.
     *
     * @param string $key キー
     * @return mixed[] 削除したキーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function delete($key);

    /**
     * キーが存在するかどうか判定する.
     *
     * @param string $key キー
     * @return boolean キーが存在するならtrue, それ以外はfalse
     */
    public function hasKey($key);

    /**
     * キーの一覧を取得する.
     *
     * @return string[] キーの一覧
     */
    public function keys();

    /**
     * サイズ(キーの個数)を取得する.
     *
     * @return int サイズ(キーの個数)
     */
    public function count();

    /**
     * サイズ(キーの個数)を取得する.
     *
     * @return int サイズ(キーの個数)
     * @see count()
     */
    public function size();

    /**
     * 空(サイズが0)かどうか判定する.
     *
     * @return boolean 空(サイズが0)ならtrue, それ以外はfalse
     * @see hasAny()
     */
    public function isEmpty();

    /**
     * 空でない(サイズが0でない)かどうか判定する.
     *
     * @return boolean 空でないならtrue, それ以外はfalse
     * @see isEmpty()
     */
    public function hasAny();

    /**
     * キーに対する値の数を取得する.
     *
     * @param string $key キー
     * @return int キーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function valueCount($key);

    /**
     * データの統合
     *
     * @param array|KeyValueStoreInterface $kvs 統合するKVSデータ
     * @return int 統合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function merge($kvs);

    /**
     * array形式のデータに変換する
     *
     * @return array 配列データ
     */
    public function toArray();

    /**
     * JSON文字列の生成
     *
     * @return string JSON文字列
     */
    public function toJson();
}
