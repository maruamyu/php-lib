<?php

namespace Maruamyu\Core;

/**
 * Key-Valueストア処理クラス インタフェース
 */
interface KeyValueStoreInterface
{
    /**
     * キーに対する値を取得する.
     *
     * @param string $key キー
     * @return mixed[] キーに対する値のリスト (存在しなかったときは空)
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function get($key);

    /**
     * キーに対する値を設定する.
     * すでに同じキーが存在している場合は, 上書きされる.
     *
     * @param string $key キー
     * @param mixed $value キーに対する値
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function set($key, $value);

    /**
     * キーに対する値を追加する.
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param string $key キー
     * @param mixed $value キーに対する値
     * @return int 設定後のキーに対する値の数
     * @throws \InvalidArgumentException 空のキーを指定したとき
     */
    public function add($key, $value);

    /**
     * キーに対する値を全て削除する.
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
     * 同じキーが存在した場合は, 引数で渡されたKVSデータの値で上書きされる.
     *
     * @param array|KeyValueStoreInterface $kvs 統合するKVSデータ
     * @return int 統合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function merge($kvs);

    /**
     * データの結合
     * 同じキーが存在する場合でも上書きされない. (以前の値も保持される.)
     *
     * @param array|KeyValueStoreInterface $kvs 結合するKVSデータ
     * @return int 結合後のデータサイズ
     * @throws \InvalidArgumentException 指定されたKVSデータの形式が正しくないとき
     */
    public function append($kvs);

    /**
     * array形式のデータに変換する.
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
