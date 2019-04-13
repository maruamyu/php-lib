<?php

namespace Maruamyu\Core\Http\Message;

class QueryStringTest extends \PHPUnit\Framework\TestCase
{
    /**
     * setした値をgetで取り出せる
     */
    public function test_set_get()
    {
        $queryString = new QueryString();
        $queryString->set('765pro', 'haruka');
        $this->assertEquals(['haruka'], $queryString->get('765pro'));
    }

    /**
     * 前の値を破棄してsetできる
     */
    public function test_set_overwrite()
    {
        $queryString = new QueryString();
        $queryString->set('jupiter', '961pro');
        $queryString->set('jupiter', '315pro');
        $this->assertEquals(['315pro'], $queryString->get('jupiter'));
    }

    /**
     * 同じキーでsetした2つの値を取り出せる
     */
    public function test_add()
    {
        $queryString = new QueryString();

        $afterElemCount = $queryString->add('346pro', 'udzuki');
        $this->assertEquals(1, $afterElemCount);
        $this->assertEquals(['udzuki'], $queryString->get('346pro'));

        $afterElemCount = $queryString->add('346pro', 'rin');
        $this->assertEquals(2, $afterElemCount);
        $this->assertEquals(['udzuki', 'rin'], $queryString->get('346pro'));
    }

    /**
     * キーの一覧を取得できる
     */
    public function test_keys()
    {
        $queryString = new QueryString();
        $queryString->set('765pro', 'takagi');
        $queryString->set('876pro', 'ishikawa');
        $queryString->set('346pro', 'mishiro');
        $queryString->set('315pro', 'saitou');

        $this->assertEquals(['765pro', '876pro', '346pro', '315pro'], $queryString->keys());
    }

    /**
     * 指定したキーのデータを削除できる
     */
    public function test_delete()
    {
        $queryString = new QueryString();
        $queryString->set('vocal', '5 star');
        $queryString->set('dance', '3 star');
        $queryString->set('visual', '1 star');

        $deleted = $queryString->delete('dance');
        $this->assertEquals(['3 star'], $deleted);

        $this->assertEquals(['vocal', 'visual'], $queryString->keys());
    }

    /**
     * キーの存在確認
     */
    public function test_has()
    {
        $queryString = new QueryString();

        $this->assertFalse($queryString->has('CANDY ISLAND'));

        $queryString->add('CANDY ISLAND', 'anzu');
        $this->assertTrue($queryString->has('CANDY ISLAND'));

        $queryString->add('CANDY ISLAND', 'kanako');
        $queryString->add('CANDY ISLAND', 'chieri');
        $this->assertTrue($queryString->has('CANDY ISLAND'));

        $queryString->delete('CANDY ISLAND');
        $this->assertFalse($queryString->has('CANDY ISLAND'));
    }

    /**
     * getでキーが空だと例外
     */
    public function test_exceptionByEmptyKeyGet()
    {
        $queryString = new QueryString();
        # for phpunit 4.*
        try {
            $queryString->get('');
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
        $queryString = new QueryString();
        try {
            $queryString->get(0);
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
        $queryString = new QueryString();
        # for phpunit 4.*
        try {
            $queryString->set('', true);
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
        $queryString = new QueryString();
        try {
            $queryString->set(0, 'zero');
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
        $queryString = new QueryString();
        # for phpunit 4.*
        try {
            $queryString->delete('');
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
        $queryString = new QueryString();
        try {
            $queryString->delete(0);
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
        $queryString = new QueryString();

        $this->assertEquals(0, $queryString->count());

        $queryString->set('LOVE LAIKA', 'minami');
        $this->assertEquals(1, $queryString->count());

        $queryString->set('LOVE LAIKA', 'anastasia');
        $this->assertEquals(1, $queryString->count());

        $queryString->set('Rosenburg Engel', 'ranko');
        $this->assertEquals(2, $queryString->count());
    }

    /**
     * 空かどうか調べるメソッド
     */
    public function test_isEmpty()
    {
        $queryString = new QueryString();

        $this->assertTrue($queryString->isEmpty());

        $queryString->set('gasha', 'SSRare');
        $this->assertFalse($queryString->isEmpty());
    }

    /**
     * 空でないかどうか調べるメソッド
     */
    public function test_hasAny()
    {
        $queryString = new QueryString();

        $this->assertFalse($queryString->hasAny());

        $queryString->set('gasha', 'SSRare');
        $this->assertTrue($queryString->hasAny());
    }

    /**
     * データの結合(QueryString)
     */
    public function test_append_QueryString()
    {
        $queryString1 = new QueryString();
        $queryString1->add('arcade', 'first');
        $queryString1->add('xbox360', 'first');

        $queryString2 = new QueryString();
        $queryString2->add('xbox360', '2');
        $queryString2->add('ps3', '2');

        #----------

        $queryString0 = new QueryString();
        $queryString0->append($queryString1);

        $expectKeys = ['arcade', 'xbox360'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $queryString0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $queryString0->get('arcade'));
        $this->assertEquals(['first'], $queryString0->get('xbox360'));

        #----------

        $queryString0->append($queryString2);

        $expectKeys = ['arcade', 'xbox360', 'ps3'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $queryString0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $queryString0->get('arcade'));
        $this->assertEquals(['first', '2'], $queryString0->get('xbox360'));
        $this->assertEquals(['2'], $queryString0->get('ps3'));
    }

    /**
     * データの結合(配列)
     */
    public function test_append_array()
    {
        $queryString0 = new QueryString();
        $queryString0->add('arcade', 'first');
        $queryString0->add('xbox360', 'first');

        $arrayData = [
            'xbox360' => '2',
            'ps3' => ['2', 'ofa'],
            'ps4' => ['platinum_stars', 'stella_stage'],
        ];

        $queryString0->append($arrayData);

        $expectKeys = ['arcade', 'xbox360', 'ps3', 'ps4'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $queryString0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $queryString0->get('arcade'));
        $this->assertEquals(['first', '2'], $queryString0->get('xbox360'));
        $this->assertEquals(['2', 'ofa'], $queryString0->get('ps3'));
        $this->assertEquals(['platinum_stars', 'stella_stage'], $queryString0->get('ps4'));
    }

    /**
     * データの統合(QueryString)
     */
    public function test_merge_QueryString()
    {
        $queryString0 = new QueryString();
        $queryString0->add('arcade', 'first');
        $queryString0->add('xbox360', 'first');

        $queryString1 = new QueryString();
        $queryString1->add('xbox360', '2');
        $queryString1->add('ps3', '2');
        $queryString1->add('ps3', 'ofa');
        $queryString1->add('ps4', 'platinum_stars');
        $queryString1->add('ps4', 'stella_stage');

        $queryString0->merge($queryString1);

        $expectKeys = ['arcade', 'xbox360', 'ps3', 'ps4'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $queryString0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $queryString0->get('arcade'));
        $this->assertEquals(['2'], $queryString0->get('xbox360'));  # overwritten
        $this->assertEquals(['2', 'ofa'], $queryString0->get('ps3'));
        $this->assertEquals(['platinum_stars', 'stella_stage'], $queryString0->get('ps4'));
    }

    /**
     * データの統合(配列)
     */
    public function test_merge_array()
    {
        $queryString0 = new QueryString();
        $queryString0->add('arcade', 'first');
        $queryString0->add('xbox360', 'first');

        $arrayData = [
            'xbox360' => '2',
            'ps3' => ['2', 'ofa'],
            'ps4' => ['platinum_stars', 'stella_stage'],
        ];

        $queryString0->merge($arrayData);

        $expectKeys = ['arcade', 'xbox360', 'ps3', 'ps4'];
        sort($expectKeys, SORT_STRING);

        $actualKeys = $queryString0->keys();
        sort($actualKeys, SORT_STRING);

        $this->assertEquals($expectKeys, $actualKeys);

        $this->assertEquals(['first'], $queryString0->get('arcade'));
        $this->assertEquals(['2'], $queryString0->get('xbox360'));  # overwritten
        $this->assertEquals(['2', 'ofa'], $queryString0->get('ps3'));
        $this->assertEquals(['platinum_stars', 'stella_stage'], $queryString0->get('ps4'));
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

        $queryString = new QueryString();
        foreach ($sourceData as $key => $value) {
            $queryString->set($key, $value);
        }
        $this->assertEquals($sourceData, $queryString->toArray());
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

        $queryString = new QueryString();
        foreach ($sourceData as $key => $value) {
            $queryString->set($key, $value);
        }
        $this->assertEquals(json_encode($sourceData), $queryString->toJson());
    }

    /**
     * シンプルなQUERY_STRINGのパース
     */
    public function test_parseBySimpleString()
    {
        $actual = QueryString::parse('kasuga=mirai&mogami=shizuka');
        $expect = [
            'kasuga' => ['mirai'],
            'mogami' => ['shizuka'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * 同じキーがあるQUERY_STRINGのパース
     */
    public function test_parseByDuplicateKey()
    {
        $actual = QueryString::parse('haruka=amami&haruka=yamazaki');
        $expect = [
            'haruka' => ['amami', 'yamazaki'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * PHPの独自形式なQUERY_STRINGを意図的にそのままパース(配列の添え字なし)
     */
    public function test_parseByPHPFormatWithoutIndex()
    {
        $actual = QueryString::parse('haruka[]=tomatsu&haruka[]=yoshimura');
        $expect = [
            'haruka[]' => ['tomatsu', 'yoshimura'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * PHPの独自形式なQUERY_STRINGを意図的にそのままパース(配列の添え字あり)
     */
    public function test_parseByPHPFormatWithIndex()
    {
        $actual = QueryString::parse('nao[0]=yokoyama&nao[1]=kamiya');
        $expect = [
            'nao[0]' => ['yokoyama'],
            'nao[1]' => ['kamiya'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * ignore empty key
     */
    public function test_parse_ignoreInvalidKey()
    {
        $queryString = '&=julia&roco=&'
            . rawurlencode('エミリー') . '=' . rawurlencode('スチュアート') . '&';
        $actual = QueryString::parse($queryString);
        $expect = [
            'roco' => [''],
            'エミリー' => ['スチュアート'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * key only
     */
    public function test_parse_keyOnly()
    {
        $queryString = 'emily&' . rawurlencode('ジュリア') . '&' . rawurlencode('ロコ');
        $actual = QueryString::parse($queryString);
        $expect = [
            'emily' => [''],
            'ジュリア' => [''],
            'ロコ' => [''],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * ignore invalid key-value pair
     */
    public function test_parse_ignoreInvalidKVPair()
    {
        $actual = QueryString::parse('&imai=asami&&imai=asaka&');
        $expect = [
            'imai' => ['asami', 'asaka'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * empty value
     */
    public function test_parse_emptyValue()
    {
        $actual = QueryString::parse('name=&name=value1&name=&name=value2&name=');
        $expect = [
            'name' => ['', 'value1', '', 'value2', ''],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * シンプルなQUERY_STRINGの生成
     */
    public function test_toStringSimple()
    {
        $queryString = new QueryString();
        $queryString->add('mobage', 'シンデレラガールズ');
        $queryString->add('gree', 'ミリオンライブ！');

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&gree=' . rawurlencode('ミリオンライブ！');
        $this->assertEquals($expect, strval($queryString));
    }

    /**
     * 同じキーがあるQUERY_STRINGの生成
     */
    public function test_toStringDuplicateKey()
    {
        $queryString = new QueryString();
        $queryString->add('mobage', 'シンデレラガールズ');
        $queryString->add('mobage', 'サイドエム');

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&mobage=' . rawurlencode('サイドエム');

        $this->assertEquals($expect, strval($queryString));
    }

    /**
     * 同じキーがあるQUERY_STRINGをPHPの独自形式で生成
     */
    public function test_toPHPQueryString()
    {
        $queryString = new QueryString();
        $queryString->add('trysail', 'もちょ');
        $queryString->add('trysail', '天');
        $queryString->add('trysail', 'ナンス');

        $expect = rawurlencode('trysail[0]') . '=' . rawurlencode('もちょ')
            . '&' . rawurlencode('trysail[1]') . '=' . rawurlencode('天')
            . '&' . rawurlencode('trysail[2]') . '=' . rawurlencode('ナンス');

        $this->assertEquals($expect, $queryString->toPHPQueryString());
    }

    /**
     * OAuthの署名に使うQUERY_STRINGを生成
     */
    public function test_toOAuthQueryString()
    {
        $queryString = new QueryString();
        $queryString->add('7', 'ナンス');
        $queryString->add('10', '天');

        # 文字列順で並び替えるので '10' が先
        $expect = '10=' . rawurlencode('天')
            . '&7=' . rawurlencode('ナンス');

        $this->assertEquals($expect, $queryString->toOAuthQueryString());
    }

    /**
     * multipart/form-data の生成
     */
    public function test_buildMultiPartData()
    {
        $queryString = new QueryString();
        $queryString->add('"Mobage"', 'モバコイン');
        $queryString->add('iPhone', 'iTunes store カード');
        $queryString->add('Android', 'Google Play カード');

        $boundary = bin2hex(openssl_random_pseudo_bytes(32));
        $expect = '';

        $expect .= '--' . $boundary . "\r\n";
        $expect .= 'Content-Disposition: form-data; name="\\"Mobage\\""' . "\r\n";
        $expect .= "\r\n";
        $expect .= 'モバコイン' . "\r\n";

        $expect .= '--' . $boundary . "\r\n";
        $expect .= 'Content-Disposition: form-data; name="iPhone"' . "\r\n";
        $expect .= "\r\n";
        $expect .= 'iTunes store カード' . "\r\n";

        $expect .= '--' . $boundary . "\r\n";
        $expect .= 'Content-Disposition: form-data; name="Android"' . "\r\n";
        $expect .= "\r\n";
        $expect .= 'Google Play カード' . "\r\n";

        $this->assertEquals($expect, $queryString->toMultiPartFormData($boundary));
    }

    /**
     * static function build QUERY_STRING
     */
    public function test_build()
    {
        $parameters = [
            'mobage' => ['シンデレラガールズ', 'サイドエム'],
            'gree' => 'ミリオンライブ！',
        ];

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&mobage=' . rawurlencode('サイドエム')
            . '&gree=' . rawurlencode('ミリオンライブ！');

        $this->assertEquals($expect, QueryString::build($parameters));
    }

    /**
     * static function build OAuth1 QUERY_STRING
     */
    public function test_buildOAuth1()
    {
        $parameters = [
            '7' => 'ナンス',
            '10' => '天',
        ];
        $expect = '10=' . rawurlencode('天')
            . '&7=' . rawurlencode('ナンス');
        $this->assertEquals($expect, QueryString::buildForOAuth1($parameters));
    }
}
