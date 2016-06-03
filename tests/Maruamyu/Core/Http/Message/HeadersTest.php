<?php

namespace Maruamyu\Core\Http\Message;

class HeadersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * setした値をgetで取り出せる
     */
    public function test_set_get()
    {
        $headers = new Headers();
        $headers->set('Content-Type', 'text/html');
        $this->assertEquals(['text/html'], $headers->get('content-type'));
    }

    /**
     * 同じキーでsetした2つの値を取り出せる
     */
    public function test_add()
    {
        $headers = new Headers();
        $headers->add('COOKIE', 'akane=chan');
        $headers->add('cookie', 'doll=maker');
        $this->assertEquals(['akane=chan', 'doll=maker'], $headers->get('Cookie'));
    }

    /**
     * 前の値を破棄してsetできる
     */
    public function test_set_overwrite()
    {
        $headers = new Headers();
        $headers->set('Content-Type', 'text/html');
        $headers->set('content-type', 'application/xml');
        $this->assertEquals(['application/xml'], $headers->get('CONTENT-TYPE'));
    }

    /**
     * 値を一覧で設定できる
     */
    public function test_setArray()
    {
        $headers = new Headers();
        $headers->add('X-Header', ['value1', 'value2']);
        $headers->add('X-Header', ['value3']);
        $this->assertEquals(['value1', 'value2', 'value3'], $headers->get('X-Header'));
    }

    /**
     * 指定したキーのデータを削除できる
     */
    public function test_delete()
    {
        $headers = new Headers();
        $headers->set('X-Header-1', 'value1');
        $headers->set('X-Header-2', 'value2');
        $headers->set('X-Header-3', 'value3');
        $deleted = $headers->delete('X-Header-2');
        $this->assertEquals(['value2'], $deleted);
        $this->assertEquals(['X-Header-1', 'X-Header-3'], $headers->names());
    }

    /**
     * hasName()
     */
    public function test_hasName()
    {
        $headers = new Headers();

        $this->assertFalse($headers->hasName('Content-Type'));

        $headers->set('content-type', 'text/html');
        $this->assertTrue($headers->hasName('Content-Type'));
        $this->assertTrue($headers->hasName('content-type'));

        $headers->delete('Content-Type');
        $this->assertFalse($headers->hasName('Content-Type'));
        $this->assertFalse($headers->hasName('content-type'));
    }

    /**
     * キーの一覧を取得できる
     */
    public function test_names()
    {
        $headers = new Headers();
        $headers->set('X-Header-1', 'value1');
        $headers->set('X-Header-2', 'value2');
        $headers->set('X-Header-3', 'value3');
        $this->assertEquals(['X-Header-1', 'X-Header-2', 'X-Header-3'], $headers->names());
    }

    /**
     * メッセージヘッダのフィールド一覧を取得できる
     */
    public function test_fields()
    {
        $headers = new Headers();
        $headers->set('Host', 'example.jp');
        $headers->set('Content-Type', 'text/html');

        $expect = [
            'Host: example.jp',
            'Content-Type: text/html',
        ];
        $this->assertEquals($expect, $headers->fields());
    }

    /**
     * データの結合(Headersオブジェクト)
     */
    public function test_append_headers()
    {
        $headers0 = new Headers();
        $headers0->add('scalar', 'scalar_preset');
        $headers0->add('vector', 'vector_preset');
        $headers0->add('orig_key', 'orig_key_preset');

        $headers1 = new Headers();
        $headers1->add('scalar', 'scalar_value');
        $headers1->add('vector', ['vector_value1', 'vector_value2']);
        $headers1->add('new_key', 'new_key_value');

        $headers0->append($headers1);

        $expectNames = ['scalar', 'vector', 'orig_key', 'new_key'];
        sort($expectNames, SORT_STRING);

        $actualNames = $headers0->names();
        sort($actualNames, SORT_STRING);

        $this->assertEquals($expectNames, $actualNames);

        $this->assertEquals(['scalar_preset', 'scalar_value'], $headers0->get('scalar'));
        $this->assertEquals(['vector_preset', 'vector_value1', 'vector_value2'], $headers0->get('vector'));
        $this->assertEquals(['orig_key_preset'], $headers0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $headers0->get('new_key'));
    }

    /**
     * データの結合(hash)
     */
    public function test_append_hash()
    {
        $headers0 = new Headers();
        $headers0->add('scalar', 'scalar_preset');
        $headers0->add('vector', 'vector_preset');
        $headers0->add('orig_key', 'orig_key_preset');

        $arrayData = [
            'scalar' => 'scalar_value',
            'vector' => ['vector_value1', 'vector_value2'],
            'new_key' => 'new_key_value',
        ];

        $headers0->append($arrayData);

        $expectNames = ['scalar', 'vector', 'orig_key', 'new_key'];
        sort($expectNames, SORT_STRING);

        $actualNames = $headers0->names();
        sort($actualNames, SORT_STRING);

        $this->assertEquals($expectNames, $actualNames);

        $this->assertEquals(['scalar_preset', 'scalar_value'], $headers0->get('scalar'));
        $this->assertEquals(['vector_preset', 'vector_value1', 'vector_value2'], $headers0->get('vector'));
        $this->assertEquals(['orig_key_preset'], $headers0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $headers0->get('new_key'));
    }

    /**
     * データの結合(vector)
     */
    public function test_append_vector()
    {
        $headers0 = new Headers();
        $headers0->add('scalar', 'scalar_preset');
        $headers0->add('vector', 'vector_preset');
        $headers0->add('orig_key', 'orig_key_preset');

        $arrayData = [
            'scalar: scalar_value',
            'vector: vector_value1',
            'vector: vector_value2',
            'new_key: new_key_value',
        ];

        $headers0->append($arrayData);

        $expectNames = ['scalar', 'vector', 'orig_key', 'new_key'];
        sort($expectNames, SORT_STRING);

        $actualNames = $headers0->names();
        sort($actualNames, SORT_STRING);

        $this->assertEquals($expectNames, $actualNames);

        $this->assertEquals(['scalar_preset', 'scalar_value'], $headers0->get('scalar'));
        $this->assertEquals(['vector_preset', 'vector_value1', 'vector_value2'], $headers0->get('vector'));
        $this->assertEquals(['orig_key_preset'], $headers0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $headers0->get('new_key'));
    }

    /**
     * データの統合(Headersオブジェクト)
     */
    public function test_merge_headers()
    {
        $headers0 = new Headers();
        $headers0->add('scalar', 'scalar_preset');
        $headers0->add('vector', 'vector_preset');
        $headers0->add('orig_key', 'orig_key_preset');

        $headers1 = new Headers();
        $headers1->add('scalar', 'scalar_value');
        $headers1->add('vector', ['vector_value1', 'vector_value2']);
        $headers1->add('new_key', 'new_key_value');

        $headers0->merge($headers1);

        $expectNames = ['scalar', 'vector', 'orig_key', 'new_key'];
        sort($expectNames, SORT_STRING);

        $actualNames = $headers0->names();
        sort($actualNames, SORT_STRING);

        $this->assertEquals($expectNames, $actualNames);

        $this->assertEquals(['scalar_value'], $headers0->get('scalar'));
        $this->assertEquals(['vector_value1', 'vector_value2'], $headers0->get('vector'));
        $this->assertEquals(['orig_key_preset'], $headers0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $headers0->get('new_key'));
    }

    /**
     * データの統合(hash)
     */
    public function test_merge_array()
    {
        $headers0 = new Headers();
        $headers0->add('scalar', 'scalar_preset');
        $headers0->add('vector', 'vector_preset');
        $headers0->add('orig_key', 'orig_key_preset');

        $arrayData = [
            'scalar' => 'scalar_value',
            'vector' => ['vector_value1', 'vector_value2'],
            'new_key' => 'new_key_value',
        ];

        $headers0->merge($arrayData);

        $expectNames = ['scalar', 'vector', 'orig_key', 'new_key'];
        sort($expectNames, SORT_STRING);

        $actualNames = $headers0->names();
        sort($actualNames, SORT_STRING);

        $this->assertEquals($expectNames, $actualNames);

        $this->assertEquals(['scalar_value'], $headers0->get('scalar'));
        $this->assertEquals(['vector_value1', 'vector_value2'], $headers0->get('vector'));
        $this->assertEquals(['orig_key_preset'], $headers0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $headers0->get('new_key'));
    }

    /**
     * データの結合(vector)
     */
    public function test_merge_vector()
    {
        $headers0 = new Headers();
        $headers0->add('scalar', 'scalar_preset');
        $headers0->add('vector', 'vector_preset');
        $headers0->add('orig_key', 'orig_key_preset');

        $arrayData = [
            'scalar: scalar_value',
            'vector: vector_value1',
            'vector: vector_value2',
            'new_key: new_key_value',
        ];

        $headers0->merge($arrayData);

        $expectNames = ['scalar', 'vector', 'orig_key', 'new_key'];
        sort($expectNames, SORT_STRING);

        $actualNames = $headers0->names();
        sort($actualNames, SORT_STRING);

        $this->assertEquals($expectNames, $actualNames);

        $this->assertEquals(['scalar_value'], $headers0->get('scalar'));
        $this->assertEquals(['vector_value1', 'vector_value2'], $headers0->get('vector'));
        $this->assertEquals(['orig_key_preset'], $headers0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $headers0->get('new_key'));
    }

    /**
     * メッセージヘッダのフィールドでの設定
     */
    public function test_setFromField()
    {
        $headers = new Headers();
        $headers->setFromField('Content-Type: text/plain');
        $headers->setFromField('Content-Type: text/html');
        $this->assertEquals(['text/html'], $headers->get('Content-Type'));
    }

    /**
     * メッセージヘッダのフィールドでの設定
     */
    public function test_addFromField()
    {
        $headers = new Headers();
        $headers->addFromField('Set-Cookie: hoge=hogehoge');
        $headers->addFromField('Set-Cookie: fuga=fugafuga');
        $this->assertEquals(['hoge=hogehoge', 'fuga=fugafuga'], $headers->get('Set-Cookie'));
    }

    /**
     * メッセージヘッダの文字列
     */
    public function test_toString()
    {
        $headers = new Headers();
        $this->assertEquals('', strval($headers));
        $this->assertEquals('', $headers->toString());

        $headers->set('Host', 'example.jp');
        $headers->set('Content-Type', 'text/html');

        $expect  = 'Host: example.jp' . "\r\n";
        $expect .= 'Content-Type: text/html' . "\r\n";
        $this->assertEquals($expect, strval($headers));
        $this->assertEquals($expect, $headers->toString());
    }

    /**
     * ヘッダarray
     */
    public function test_toArray()
    {
        $headers = new Headers();
        $this->assertEquals([], $headers->toArray());

        $headers->add('Host', 'example.jp');
        $headers->add('Content-Type', 'text/html');
        $headers->add('X-Header', 'value1');
        $headers->add('X-Header', 'value2');

        $expects = [
            'Host' => ['example.jp'],
            'Content-Type' => ['text/html'],
            'X-Header' => ['value1', 'value2'],
        ];
        $this->assertEquals($expects, $headers->toArray());
    }

    /**
     * インスタンス初期化時のパラメータ(文字列)
     */
    public function test_initialize_by_string()
    {
        $initValue  = 'Host: example.jp' ."\r\n";
        $initValue .= 'Content-Type: text/html' ."\r\n";

        $headers = new Headers($initValue);
        $this->assertEquals(['example.jp'], $headers->get('Host'));
        $this->assertEquals(['text/html'], $headers->get('Content-Type'));
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
        $headers = new Headers($initValue);
        $this->assertEquals(['example.jp'], $headers->get('Host'));
        $this->assertEquals(['text/html'], $headers->get('Content-Type'));
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
        $headers = new Headers($initValue);
        $this->assertEquals(['example.jp'], $headers->get('Host'));
        $this->assertEquals(['text/html'], $headers->get('Content-Type'));
    }
}
