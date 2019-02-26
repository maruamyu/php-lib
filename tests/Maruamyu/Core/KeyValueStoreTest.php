<?php

namespace Maruamyu\Core;

class KeyValueStoreTest extends \PHPUnit\Framework\TestCase
{
    /**
     * setした値をgetで取り出せる
     */
    public function test_set_get()
    {
        $kvs = new KeyValueStore();
        $kvs->set('765pro', 'haruka');
        $this->assertEquals(['haruka'], $kvs->get('765pro'));
    }

    /**
     * 前の値を破棄してsetできる
     */
    public function test_set_overwrite()
    {
        $kvs = new KeyValueStore();
        $kvs->set('jupiter', '961pro');
        $kvs->set('jupiter', '315pro');
        $this->assertEquals(['315pro'], $kvs->get('jupiter'));
    }

    /**
     * 同じキーでsetした2つの値を取り出せる
     */
    public function test_add()
    {
        $kvs = new KeyValueStore();

        $afterElemCount = $kvs->add('346pro', 'udzuki');
        $this->assertEquals(1, $afterElemCount);
        $this->assertEquals(['udzuki'], $kvs->get('346pro'));

        $afterElemCount = $kvs->add('346pro', 'rin');
        $this->assertEquals(2, $afterElemCount);
        $this->assertEquals(['udzuki', 'rin'], $kvs->get('346pro'));
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
        $this->assertEquals(['3 star'], $deleted);

        $this->assertEquals(['vocal', 'visual'], $kvs->keys());
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
        try {
            $kvs->get(0);
            $this->assertTrue(true);
        } catch (\Exception $exception) {
            $this->assertTrue(false);
        }
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
        try {
            $kvs->set(0, 'zero');
            $this->assertTrue(true);
        } catch (\Exception $exception) {
            $this->assertTrue(false);
        }
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
        try {
            $kvs->delete(0);
            $this->assertTrue(true);
        } catch (\Exception $exception) {
            $this->assertTrue(false);
        }
    }

    /**
     * データサイズ
     */
    public function test_count()
    {
        $kvs = new KeyValueStore();

        $this->assertEquals(0, $kvs->count());

        $kvs->set('LOVE LAIKA', 'minami');
        $this->assertEquals(1, $kvs->count());

        $kvs->set('LOVE LAIKA', 'anastasia');
        $this->assertEquals(1, $kvs->count());

        $kvs->set('Rosenburg Engel', 'ranko');
        $this->assertEquals(2, $kvs->count());
    }

    /**
     * 要素のデータサイズ
     */
    public function test_valueCount()
    {
        $kvs = new KeyValueStore();

        $kvs->add('CANDY ISLAND', 'anzu');
        $this->assertEquals(1, $kvs->valueCount('CANDY ISLAND'));

        $kvs->add('CANDY ISLAND', 'kanako');
        $kvs->add('CANDY ISLAND', 'chieri');
        $this->assertEquals(3, $kvs->valueCount('CANDY ISLAND'));

        $kvs->add('Asterisk', 'miku');
        $kvs->add('Asterisk', 'riina');
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
     * データの結合(KVS)
     */
    public function test_append_kvs()
    {
        $kvs1 = new KeyValueStore();
        $kvs1->add('arcade', 'first');
        $kvs1->add('xbox360', 'first');

        $kvs2 = new KeyValueStore();
        $kvs2->add('xbox360', '2');
        $kvs2->add('ps3', '2');

        #----------

        $kvs0 = new KeyValueStore();
        $kvs0->append($kvs1);

        $expectKeys = ['arcade', 'xbox360'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $kvs0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $kvs0->get('arcade'));
        $this->assertEquals(['first'], $kvs0->get('xbox360'));

        #----------

        $kvs0->append($kvs2);

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
     * データの結合(配列)
     */
    public function test_append_array()
    {
        $kvs0 = new KeyValueStore();
        $kvs0->add('scalar', 'scalar_preset');
        $kvs0->add('vector', 'vector_preset');
        $kvs0->add('hash', 'hash_preset');
        $kvs0->add('orig_key', 'orig_key_preset');

        $arrayData = [
            'scalar' => 'scalar_value',
            'vector' => ['vector_value1', 'vector_value2'],
            'hash' => ['hash_key' => 'hash_value'],
            'new_key' => 'new_key_value',
        ];

        $kvs0->append($arrayData);

        $expectKeys = ['scalar', 'vector', 'hash', 'orig_key', 'new_key'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $kvs0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['scalar_preset', 'scalar_value'], $kvs0->get('scalar'));
        $this->assertEquals(['vector_preset', 'vector_value1', 'vector_value2'], $kvs0->get('vector'));
        $this->assertEquals(['hash_preset', ['hash_key' => 'hash_value']], $kvs0->get('hash'));
        $this->assertEquals(['orig_key_preset'], $kvs0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $kvs0->get('new_key'));
    }

    /**
     * データの統合(KVS)
     */
    public function test_merge_kvs()
    {
        $kvs0 = new KeyValueStore();
        $kvs0->add('scalar', 'scalar_preset');
        $kvs0->add('vector', 'vector_preset');
        $kvs0->add('hash', 'hash_preset');
        $kvs0->add('orig_key', 'orig_key_preset');

        $kvs1 = new KeyValueStore();
        $kvs1->add('scalar', 'scalar_value');
        $kvs1->add('vector', 'vector_value1');
        $kvs1->add('vector', 'vector_value2');
        $kvs1->add('hash', ['hash_key' => 'hash_value']);
        $kvs1->add('new_key', 'new_key_value');

        $kvs0->merge($kvs1);

        $expectKeys = ['scalar', 'vector', 'hash', 'orig_key', 'new_key'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $kvs0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['scalar_value'], $kvs0->get('scalar'));
        $this->assertEquals(['vector_value1', 'vector_value2'], $kvs0->get('vector'));
        $this->assertEquals([['hash_key' => 'hash_value']], $kvs0->get('hash'));
        $this->assertEquals(['orig_key_preset'], $kvs0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $kvs0->get('new_key'));
    }

    /**
     * データの統合(配列)
     */
    public function test_merge_array()
    {
        $kvs0 = new KeyValueStore();
        $kvs0->add('scalar', 'scalar_preset');
        $kvs0->add('vector', 'vector_preset');
        $kvs0->add('hash', 'hash_preset');
        $kvs0->add('orig_key', 'orig_key_preset');

        $arrayData = [
            'scalar' => 'scalar_value',
            'vector' => ['vector_value1', 'vector_value2'],
            'hash' => ['hash_key' => 'hash_value'],
            'new_key' => 'new_key_value',
        ];

        $kvs0->merge($arrayData);

        $expectKeys = ['scalar', 'vector', 'hash', 'orig_key', 'new_key'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $kvs0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['scalar_value'], $kvs0->get('scalar'));
        $this->assertEquals(['vector_value1', 'vector_value2'], $kvs0->get('vector'));
        $this->assertEquals([['hash_key' => 'hash_value']], $kvs0->get('hash'));
        $this->assertEquals(['orig_key_preset'], $kvs0->get('orig_key'));
        $this->assertEquals(['new_key_value'], $kvs0->get('new_key'));
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
