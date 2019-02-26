<?php

namespace Maruamyu\Core\Http\Message;

class QueryStringTest extends \PHPUnit\Framework\TestCase
{
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
        $kvs = new QueryString();
        $kvs->add('mobage', 'シンデレラガールズ');
        $kvs->add('gree', 'ミリオンライブ！');

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&gree=' . rawurlencode('ミリオンライブ！');
        $this->assertEquals($expect, strval($kvs));
    }

    /**
     * 同じキーがあるQUERY_STRINGの生成
     */
    public function test_toStringDuplicateKey()
    {
        $kvs = new QueryString();
        $kvs->add('mobage', 'シンデレラガールズ');
        $kvs->add('mobage', 'サイドエム');

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&mobage=' . rawurlencode('サイドエム');

        $this->assertEquals($expect, strval($kvs));
    }

    /**
     * 同じキーがあるQUERY_STRINGをPHPの独自形式で生成
     */
    public function test_toPHPQueryString()
    {
        $kvs = new QueryString();
        $kvs->add('trysail', 'もちょ');
        $kvs->add('trysail', '天');
        $kvs->add('trysail', 'ナンス');

        $expect = rawurlencode('trysail[0]') . '=' . rawurlencode('もちょ')
            . '&' . rawurlencode('trysail[1]') . '=' . rawurlencode('天')
            . '&' . rawurlencode('trysail[2]') . '=' . rawurlencode('ナンス');

        $this->assertEquals($expect, $kvs->toPHPQueryString());
    }

    /**
     * OAuthの署名に使うQUERY_STRINGを生成
     */
    public function test_toOAuthQueryString()
    {
        $kvs = new QueryString();
        $kvs->add('7', 'ナンス');
        $kvs->add('10', '天');

        # 文字列順で並び替えるので '10' が先
        $expect = '10=' . rawurlencode('天')
            . '&7=' . rawurlencode('ナンス');

        $this->assertEquals($expect, $kvs->toOAuthQueryString());
    }

    /**
     * multipart/form-data の生成
     */
    public function test_buildMultiPartData()
    {
        $kvs = new QueryString();
        $kvs->add('"Mobage"', 'モバコイン');
        $kvs->add('iPhone', 'iTunes store カード');
        $kvs->add('Android', 'Google Play カード');

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

        $this->assertEquals($expect, $kvs->toMultiPartFormData($boundary));
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
