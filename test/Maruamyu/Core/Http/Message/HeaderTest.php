<?php

namespace Maruamyu\Core\Http\Message;

class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * setした値をgetで取り出せる
     */
    public function test_setAndGet()
    {
        $kvs = new Header();
        $kvs->set('Content-Type', 'text/html');
        $this->assertEquals(['text/html'], $kvs->get('content-type'));
    }

    /**
     * 同じキーでsetした2つの値を取り出せる
     */
    public function test_setTwise()
    {
        $kvs = new Header();
        $kvs->set('COOKIE', 'akane=chan');
        $kvs->set('cookie', 'doll=maker');
        $this->assertEquals(['akane=chan', 'doll=maker'], $kvs->get('Cookie'));
    }

    /**
     * 前の値を破棄してsetできる
     */
    public function test_setOverwrite()
    {
        $kvs = new Header();
        $kvs->set('Content-Type', 'text/html');
        $kvs->set('content-type', 'application/xml', true);
        $this->assertEquals(['application/xml'], $kvs->get('CONTENT-TYPE'));
    }

    /**
     * キーの一覧を取得できる
     */
    public function test_names()
    {
        $kvs = new Header();
        $kvs->set('X-Header-1', '1');
        $kvs->set('X-Header-2', '2');
        $kvs->set('X-Header-3', '3');
        $this->assertEquals(['X-Header-1', 'X-Header-2', 'X-Header-3'], $kvs->names());
    }

    /**
     * 指定したキーのデータを削除できる
     */
    public function test_delete()
    {
        $kvs = new Header();
        $kvs->set('X-Header-1', '1');
        $kvs->set('X-Header-2', '2');
        $kvs->set('X-Header-3', '3');
        $kvs->delete('X-Header-2');
        $this->assertEquals(['X-Header-1', 'X-Header-3'], $kvs->names());
    }

    /**
     * メッセージヘッダのフィールド一覧を取得できる
     */
    public function test_fields()
    {
        $kvs = new Header();
        $kvs->set('Host', 'example.jp');
        $kvs->set('Content-Type', 'text/html');

        $expect = [
            'Host: example.jp',
            'Content-Type: text/html',
        ];
        $this->assertEquals($expect, $kvs->fields());
    }

    /**
     * メッセージヘッダのフィールドでの設定
     */
    public function test_setFromField()
    {
        $header = new Header();
        $header->setFromField('Content-Type: text/html');
        $this->assertEquals(['text/html'], $header->get('Content-Type'));
    }

    /**
     * インスタンス初期化時のパラメータ(文字列)
     */
    public function test_initialize_by_string()
    {
        $initValue  = 'Host: example.jp' ."\r\n";
        $initValue .= 'Content-Type: text/html' ."\r\n";

        $header = new Header($initValue);
        $this->assertEquals(['example.jp'], $header->get('Host'));
        $this->assertEquals(['text/html'], $header->get('Content-Type'));
    }

    /**
     * インスタンス初期化時のパラメータ(純配列)
     */
    public function test_initialize_by_fields()
    {
        $initValue = [
            'Host: example.jp',
            'Content-Type: text/html',
        ];
        $header = new Header($initValue);
        $this->assertEquals(['example.jp'], $header->get('Host'));
        $this->assertEquals(['text/html'], $header->get('Content-Type'));
    }

    /**
     * インスタンス初期化時のパラメータ(連想配列)
     */
    public function test_initialize_by_assoc()
    {
        $initValue = [
            'Host' => 'example.jp',
            'Content-Type' => 'text/html',
        ];
        $header = new Header($initValue);
        $this->assertEquals(['example.jp'], $header->get('Host'));
        $this->assertEquals(['text/html'], $header->get('Content-Type'));
    }
}
