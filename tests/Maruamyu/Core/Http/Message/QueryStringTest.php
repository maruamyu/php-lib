<?php

namespace Maruamyu\Core\Http\Message;

class QueryStringTest extends \PHPUnit_Framework_TestCase
{
    /**
     * シンプルなQUERY_STRINGのパース
     */
    public function test_parseQueryStringBySimpleString()
    {
        $actual = QueryString::parseQueryString('kasuga=mirai&mogami=shizuka');
        $expect = [
            'kasuga' => ['mirai'],
            'mogami' => ['shizuka'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * 同じキーがあるQUERY_STRINGのパース
     */
    public function test_parseQueryStringByDuplicateKey()
    {
        $actual = QueryString::parseQueryString('haruka=amami&haruka=yamazaki');
        $expect = [
            'haruka' => ['amami', 'yamazaki'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * PHPの独自形式なQUERY_STRINGを意図的にそのままパース(配列の添え字なし)
     */
    public function test_parseQueryStringByPHPFormatWithoutIndex()
    {
        $actual = QueryString::parseQueryString('haruka[]=tomatsu&haruka[]=yoshimura');
        $expect = [
            'haruka[]' => ['tomatsu', 'yoshimura'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * PHPの独自形式なQUERY_STRINGを意図的にそのままパース(配列の添え字あり)
     */
    public function test_parseQueryStringByPHPFormatWithIndex()
    {
        $actual = QueryString::parseQueryString('nao[0]=yokoyama&nao[1]=kamiya');
        $expect = [
            'nao[0]' => ['yokoyama'],
            'nao[1]' => ['kamiya'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * ignore invalid key
     */
    public function test_parseQueryString_ignoreInvalidKey()
    {
        $actual = QueryString::parseQueryString('emily=stuart&=julia');
        $expect = [
            'emily' => ['stuart'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * ignore invalid key-value pair
     */
    public function test_parseQueryString_ignoreInvalidKVPair()
    {
        $actual = QueryString::parseQueryString('&imai=asami&&imai=asaka&');
        $expect = [
            'imai' => ['asami', 'asaka'],
        ];
        $this->assertEquals($expect, $actual);
    }

    /**
     * empty value
     */
    public function test_parseQueryString_emptyValue()
    {
        $actual = QueryString::parseQueryString('name=&name=value1&name=&name=value2&name=');
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
        $kvs->set('mobage', 'シンデレラガールズ');
        $kvs->set('gree', 'ミリオンライブ！');

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&gree=' . rawurlencode('ミリオンライブ！');
        $this->assertEquals($expect, (string)$kvs);
    }

    /**
     * 同じキーがあるQUERY_STRINGの生成
     */
    public function test_toStringDuplicateKey()
    {
        $kvs = new QueryString();
        $kvs->set('mobage', 'シンデレラガールズ');
        $kvs->set('mobage', 'サイドエム');

        $expect = 'mobage=' . rawurlencode('シンデレラガールズ')
            . '&mobage=' . rawurlencode('サイドエム');

        $this->assertEquals($expect, (string)$kvs);
    }

    /**
     * 同じキーがあるQUERY_STRINGをPHPの独自形式で生成
     */
    public function test_toPHPQueryString()
    {
        $kvs = new QueryString();
        $kvs->set('trysail', 'もちょ');
        $kvs->set('trysail', '天');
        $kvs->set('trysail', 'ナンス');

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
        $kvs->set('7', 'ナンス');
        $kvs->set('10', '天');

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
        $kvs->set('"Mobage"', 'モバコイン');
        $kvs->set('iPhone', 'iTunes store カード');
        $kvs->set('Android', 'Google Play カード');

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
}
