<?php

namespace Maruamyu\Core;

class KeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * setした値をgetで取り出せる
     */
    public function test_setAndGet()
    {
        $kvs = new KeyValueStore();
        $afterElemCount = $kvs->set('765pro', 'haruka');

        $this->assertEquals(['haruka'], $kvs->get('765pro'));
        $this->assertEquals(1, $afterElemCount);
    }

    /**
     * 同じキーでsetした2つの値を取り出せる
     */
    public function test_setTwise()
    {
        $kvs = new KeyValueStore();
        $kvs->set('346pro', 'udzuki');
        $afterElemCount = $kvs->set('346pro', 'rin');

        $this->assertEquals(['udzuki', 'rin'], $kvs->get('346pro'));
        $this->assertEquals(2, $afterElemCount);
    }

    /**
     * 前の値を破棄してsetできる
     */
    public function test_setOverwrite()
    {
        $kvs = new KeyValueStore();
        $kvs->set('jupiter', '961pro');
        $afterElemCount = $kvs->set('jupiter', '315pro', true);

        $this->assertEquals(['315pro'], $kvs->get('jupiter'));
        $this->assertEquals(1, $afterElemCount);
    }

    /**
     * キーの一覧を取得できる
     */
    public function test_keys()
    {
        $kvs = new KeyValueStore();
        $kvs->set('765pro', 'takagi');
        $kvs->set('876pro', 'ishikawa');
        $kvs->set('346pro', 'mishiro');
        $kvs->set('315pro', 'saitou');

        $this->assertEquals(['765pro', '876pro', '346pro', '315pro'], $kvs->keys());
    }

    /**
     * 指定したキーのデータを削除できる
     */
    public function test_delete()
    {
        $kvs = new KeyValueStore();
        $kvs->set('vocal', '5 star');
        $kvs->set('dance', '3 star');
        $kvs->set('visual', '1 star');
        $deleted = $kvs->delete('dance');

        $this->assertEquals(['vocal', 'visual'], $kvs->keys());
        $this->assertEquals(['3 star'], $deleted);
    }

    /**
     * キーの存在確認
     */
    public function test_hasKey()
    {
        $kvs = new KeyValueStore();
        $this->assertFalse($kvs->hasKey('kotori'));

        $kvs->set('kotori', 'piyopiyo');
        $this->assertTrue($kvs->hasKey('kotori'));

        $kvs->delete('kotori');
        $this->assertFalse($kvs->hasKey('kotori'));
    }

    /**
     * getでキーが空だと例外
     */
    public function test_exceptionByEmptyKeyGet()
    {
        $kvs = new KeyValueStore();
        # for phpunit 4.*
        try {
            $kvs->get('');
        } catch (\InvalidArgumentException $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertFalse(true);
    }

    /**
     * getでキーがemptyに反応する値でも, 文字列として空でなければ例外にならない
     */
    public function test_notExceptionByZeroKeyGet()
    {
        $kvs = new KeyValueStore();
        $kvs->get(0);
    }

    /**
     * setでキーが空だと例外
     */
    public function test_exceptionByEmptyKeySet()
    {
        $kvs = new KeyValueStore();
        # for phpunit 4.*
        try {
            $kvs->set('', true);
        } catch (\InvalidArgumentException $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertFalse(true);
    }

    /**
     * setでキーがemptyに反応する値でも, 文字列として空でなければ例外にならない
     */
    public function test_notExceptionByZeroKeySet()
    {
        $kvs = new KeyValueStore();
        $kvs->set(0, 'zero');
    }

    /**
     * deleteでキーが空だと例外
     */
    public function test_exceptionByEmptyKeyDelete()
    {
        $kvs = new KeyValueStore();
        # for phpunit 4.*
        try {
            $kvs->delete('');
        } catch (\InvalidArgumentException $exception) {
            $this->assertTrue(true);
            return;
        }
        $this->assertFalse(true);
    }

    /**
     * deleteでキーがemptyに反応する値でも, 文字列として空でなければ例外にならない
     */
    public function test_notExceptionByZeroKeyDelete()
    {
        $kvs = new KeyValueStore();
        $kvs->delete(0);
    }

    /**
     * データサイズ
     */
    public function test_size()
    {
        $kvs = new KeyValueStore();

        $this->assertEquals(0, $kvs->size());
        $this->assertEquals(0, $kvs->count());

        $kvs->set('LOVE LAIKA', 'minami');
        $this->assertEquals(1, $kvs->size());
        $this->assertEquals(1, $kvs->count());

        $kvs->set('LOVE LAIKA', 'anastasia');
        $this->assertEquals(1, $kvs->size());
        $this->assertEquals(1, $kvs->count());

        $kvs->set('Rosenburg Engel', 'ranko');
        $this->assertEquals(2, $kvs->size());
        $this->assertEquals(2, $kvs->count());
    }

    /**
     * 要素のデータサイズ
     */
    public function test_valueCount()
    {
        $kvs = new KeyValueStore();

        $kvs->set('CANDY ISLAND', 'anzu');
        $this->assertEquals(1, $kvs->valueCount('CANDY ISLAND'));

        $kvs->set('CANDY ISLAND', 'kanako');
        $kvs->set('CANDY ISLAND', 'chieri');
        $this->assertEquals(3, $kvs->valueCount('CANDY ISLAND'));

        $kvs->set('Asterisk', 'miku');
        $kvs->set('Asterisk', 'riina');
        $this->assertEquals(2, $kvs->valueCount('Asterisk'));
    }

    /**
     * 空かどうか調べるメソッド
     */
    public function test_isEmpty()
    {
        $kvs = new KeyValueStore();

        $this->assertTrue($kvs->isEmpty());

        $kvs->set('gasha', 'SSRare');
        $this->assertFalse($kvs->isEmpty());
    }

    /**
     * 空でないかどうか調べるメソッド
     */
    public function test_hasAny()
    {
        $kvs = new KeyValueStore();

        $this->assertFalse($kvs->hasAny());

        $kvs->set('gasha', 'SSRare');
        $this->assertTrue($kvs->hasAny());
    }

    /**
     * データの統合
     */
    public function test_merge()
    {
        $kvs1 = new KeyValueStore();
        $kvs1->set('arcade', 'first');
        $kvs1->set('xbox360', 'first');

        $kvs2 = [
            'xbox360' => '2',
            'ps3' => '2',
        ];

        #----------

        $kvs0 = new KeyValueStore();
        $kvs0->merge($kvs1);

        $expectKeys = ['arcade', 'xbox360'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $kvs0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $kvs0->get('arcade'));
        $this->assertEquals(['first'], $kvs0->get('xbox360'));

        #----------

        $kvs0->merge($kvs2);

        $expectKeys = ['arcade', 'xbox360', 'ps3'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $kvs0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $kvs0->get('arcade'));
        $this->assertEquals(['first', '2'], $kvs0->get('xbox360'));
        $this->assertEquals(['2'], $kvs0->get('ps3'));
    }

    /**
     * arrayデータの生成
     */
    public function test_toArray()
    {
        $sourceData = [
            'boolean' => true,
            'integer' => 765,
            'float' => 76.5,
            'string' => '765pro',
            'array' => ['765pro', '876pro', '346pro', '315pro'],
            'hash' => [
                '765pro' => 'kotori',
                '346pro' => 'chihiro',
                '315pro' => 'yamamura',
            ],
        ];

        $kvs = new KeyValueStore();
        foreach ($sourceData as $key => $value) {
            $kvs->set($key, $value);
        }
        $this->assertEquals($sourceData, $kvs->toArray());
    }

    /**
     * JSON文字列の生成
     */
    public function test_toJson()
    {
        $sourceData = [
            'boolean' => true,
            'integer' => 765,
            'float' => 76.5,
            'string' => '765pro',
            'array' => ['765pro', '876pro', '346pro', '315pro'],
            'hash' => [
                '765pro' => 'kotori',
                '346pro' => 'chihiro',
                '315pro' => 'yamamura',
            ],
        ];

        $kvs = new KeyValueStore();
        foreach ($sourceData as $key => $value) {
            $kvs->set($key, $value);
        }
        $this->assertEquals(json_encode($sourceData), $kvs->toJson());
    }
}
