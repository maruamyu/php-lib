<?php

namespace Maruamyu\Core\Http\Message;

use Psr\Http\Message\UriInterface as PsrUriInterface;

/**
 * 独自実装を含む PSR-7準拠 URLオブジェクト
 */
class Uri implements UriInterface
{
    protected static $schemeToDefaultPort = [
        'http' => 80,
        'https' => 443,
        'tls' => 443,
    ];

    private $scheme;
    private $host;
    private $port;
    private $userName;
    private $password;
    private $path;
    private $queryString;
    private $fragment;

    /**
     * @param null|string|array|PsrUriInterface $uri URLデータ
     * @throws \InvalidArgumentException 指定されたURLデータが正しくないとき
     */
    public function __construct($uri = null)
    {
        if (is_null($uri)) {
            $this->initialize();
        } elseif (is_string($uri)) {
            $this->setFromString($uri);
        } elseif (is_array($uri)) {
            $this->setFromParsed($uri);
        } elseif ($uri instanceof PsrUriInterface) {
            $this->setFromInstance($uri);
        } else {
            $type = (is_object($uri)) ? get_class($uri) : gettype($uri);
            throw new \InvalidArgumentException('invalid: ' . $type . ' (expect string or Uri)');
        }
    }

    /**
     * clone時のデータコピー.
     */
    public function __clone()
    {
        $this->queryString = clone $this->queryString;
    }

    /**
     * @return string URL
     * @see toString()
     */
    public function __toString()
    {
        return $this->toString();
    }

    /**
     * @return string URL
     */
    public function toString()
    {
        $url = '';

        $authority = $this->getAuthority();
        if (strlen($authority) > 0) {
            $scheme = $this->getScheme();
            if (strlen($scheme) > 0) {
                $url .= $scheme . ':';
            }
            $url .= '//' . $authority;
        }

        $path = strval($this->getPath());
        if (substr($path, 0, 1) !== '/') {
            $path = '/' . $path;
        }
        $url .= $path;

        $queryString = $this->getQueryString();
        if ($queryString->hasAny()) {
            $url .= '?' . $queryString->toString();
        }

        $fragment = $this->getFragment();
        if (strlen($fragment) > 0) {
            $url .= '#' . $fragment;
        }

        return $url;
    }

    /**
     * @return string プロトコル
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * "[認証パラメータ@]ホスト[:ポート]"
     * 認証パラメータは, 未指定の場合省略される.
     * ポートは, 未指定またはデフォルト(http:80等)の場合, 省略される.
     *
     * @return string ホスト・ポート・認証パラメータを含む文字列
     */
    public function getAuthority()
    {
        $host = $this->getHost();
        if (strlen($host) < 1) {
            return '';
        }
        $authority = '';

        $userInfo = $this->getUserInfo();
        if (strlen($userInfo) > 0) {
            $authority = $userInfo . '@';
        }

        $authority .= $host;

        $port = $this->getPort();
        if ($port) {
            $authority .= ':' . $port;
        }

        return $authority;
    }

    /**
     * @return string 認証パラメータ(ユーザー名[:パスワード])
     */
    public function getUserInfo()
    {
        $userInfo = '';
        if (strlen($this->userName) > 0) {
            $userInfo .= $this->userName;
            if (strlen($this->password) > 0) {
                $userInfo .= ':' . $this->password;
            }
        }
        return $userInfo;
    }

    /**
     * @return string ホスト
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int|null ポート
     *   (未指定またはデフォルト(http:80等)の場合, nullが返る.)
     */
    public function getPort()
    {
        $port = intval($this->port, 10);
        if ($port == 0) {
            return null;
        }
        $scheme = $this->getScheme();
        if (static::isNotDefaultPort($scheme, $port)) {
            return $port;
        } else {
            return null;
        }
    }

    /**
     * @return string パス
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string QUERY_STRING部の文字列
     *   注意: PHPの独自拡張形式ではない文字列を(意図的に)出力します
     * @see QueryString::__toString()
     */
    public function getQuery()
    {
        return strval($this->queryString);
    }

    /**
     * @return string fragment
     */
    public function getFragment()
    {
        return strval($this->fragment);
    }

    /**
     * @param string $scheme プロトコル
     * @return static 指定のプロトコルを設定した新しいインスタンス
     * @throws \InvalidArgumentException プロトコルが正しくない, または未指定のとき
     */
    public function withScheme($scheme)
    {
        $newInstance = clone $this;
        $newInstance->setScheme($scheme);
        return $newInstance;
    }

    /**
     * @param string $user ユーザー名
     * @param null|string $password パスワード
     * @return static 指定の認証パラメータを設定した新しいインスタンス
     */
    public function withUserInfo($user, $password = null)
    {
        $newInstance = clone $this;
        $newInstance->setUserInfo($user, $password);
        return $newInstance;
    }

    /**
     * @param string $host ホスト
     * @return static 指定のホストを設定した新しいインスタンス
     * @throws \InvalidArgumentException ホストが正しくないとき
     */
    public function withHost($host)
    {
        $newInstance = clone $this;
        $newInstance->setHost($host);
        return $newInstance;
    }

    /**
     * @param int|null $port ポート
     * @return static 指定のポートを設定した新しいインスタンス
     * @throws \InvalidArgumentException ポートが正しくないとき
     */
    public function withPort($port)
    {
        $newInstance = clone $this;
        $newInstance->setPort($port);
        return $newInstance;
    }

    /**
     * @param string $path パス
     * @return static 指定のパスを設定した新しいインスタンス
     * @throws \InvalidArgumentException パスが正しくないとき
     */
    public function withPath($path)
    {
        $newInstance = clone $this;
        $newInstance->setPath($path);
        return $newInstance;
    }

    /**
     * @param string $query QUERY_STRING部の文字列
     * @return static 指定のQUERY_STRINGを設定した新しいインスタンス
     * @throws \InvalidArgumentException QUERY_STRINGが正しくないとき
     */
    public function withQuery($query)
    {
        $newInstance = clone $this;
        $newInstance->setQueryString($query);
        return $newInstance;
    }

    /**
     * @param string $fragment fragment
     * @return static 指定のfragmentを設定した新しいインスタンス
     */
    public function withFragment($fragment)
    {
        $newInstance = clone $this;
        $newInstance->setFragment($fragment);
        return $newInstance;
    }

    /**
     * @param Uri|string $uri 比較対象
     * @return bool 比較対象と同じときtrue, それ以外はfalse
     */
    public function equals($uri)
    {
        return (strcmp(strval($this), strval($uri)) == 0);
    }

    /**
     * @return QueryString QUERY_STRINGデータ
     */
    public function getQueryString()
    {
        return clone $this->queryString;
    }

    /**
     * @param string|array|QueryString $queryString QUERY_STRINGデータ
     * @return static 指定のQUERY_STRINGデータを設定した新しいインスタンス
     * @throws \InvalidArgumentException QUERY_STRINGデータが正しくないとき
     */
    public function withQueryString($queryString)
    {
        $newInstance = clone $this;
        $newInstance->setQueryString($queryString);
        return $newInstance;
    }

    /**
     * @param string|array|QueryString $queryString QUERY_STRINGデータ
     * @return static 指定のQUERY_STRINGデータをマージした新しいインスタンス
     * @throws \InvalidArgumentException QUERY_STRINGデータが正しくないとき
     */
    public function withAddedQueryString($queryString)
    {
        $newInstance = clone $this;
        $newInstance->appendQueryString($queryString);
        return $newInstance;
    }

    /**
     * 内部状態の初期化
     */
    private function initialize()
    {
        $this->scheme = '';
        $this->host = '';
        $this->port = null;
        $this->userName = '';
        $this->password = '';
        $this->path = '';
        $this->queryString = new QueryString();
        $this->fragment = '';
    }

    /**
     * @param string $url URL
     * @throws \InvalidArgumentException URLが正しくないとき
     * @see parse_url()
     */
    private function setFromString($url)
    {
        $parsed = parse_url($url);
        if (!$parsed) {
            throw new \InvalidArgumentException('invalid URL: ' . $url);
        }
        $this->setFromParsed($parsed);
    }

    /**
     * @param array $parsed {parse_url()}の戻り値
     * @throws \InvalidArgumentException URLが正しくないとき
     * @see parse_url()
     */
    private function setFromParsed($parsed)
    {
        $this->initialize();

        if (isset($parsed['scheme'])) {
            $this->setScheme($parsed['scheme']);
        }
        if (isset($parsed['host'])) {
            $this->setHost($parsed['host']);
        }
        if (isset($parsed['port'])) {
            $this->setPort($parsed['port']);
        }
        if (isset($parsed['user'])) {
            if (isset($parsed['pass'])) {
                $this->setUserInfo($parsed['user'], $parsed['pass']);
            } else {
                $this->setUserInfo($parsed['user']);
            }
        }
        if (isset($parsed['path'])) {
            $this->setPath($parsed['path']);
        }
        if (isset($parsed['query'])) {
            $this->setQueryString($parsed['query']);
        }
        if (isset($parsed['fragment'])) {
            $this->setFragment($parsed['fragment']);
        }
    }

    /**
     * @param PsrUriInterface $uri URLオブジェクト インスタンス
     * @throws \InvalidArgumentException 型が正しくないとき
     */
    private function setFromInstance(PsrUriInterface $uri)
    {
        $this->setScheme($uri->getScheme());
        $this->setHost($uri->getHost());
        $this->setPort($uri->getPort());
        list($userName, $password) = explode(':', strval($uri->getUserInfo()), 2);
        $this->setUserInfo($userName, $password);
        $this->setPath($uri->getPath());
        if ($uri instanceof UriInterface) {
            $this->setQueryString($uri->getQueryString());
        } else {
            $this->setQueryString($uri->getQuery());
        }
        $this->setFragment($uri->getFragment());
    }

    /**
     * @param string $scheme プロトコル
     */
    private function setScheme($scheme)
    {
        $this->scheme = strval($scheme);
    }

    /**
     * @param string $host ホスト
     */
    private function setHost($host)
    {
        $this->host = strval($host);
    }

    /**
     * @param int|null $port ポート
     */
    private function setPort($port)
    {
        $port = intval($port, 10);
        if ($port == 0) {
            $this->port = null;
        } elseif ($port >= 1 && $port <= 65535) {
            $this->port = $port;
        } else {
            throw new \InvalidArgumentException('invalid port: ' . $port);
        }
    }

    /**
     * @param string $path パス
     */
    private function setPath($path)
    {
        $this->path = strval($path);
    }

    /**
     * @param string $userName ホストの認証ユーザー名
     * @param string $password ホストの認証パスワード
     */
    private function setUserInfo($userName, $password = null)
    {
        $this->userName = strval($userName);
        $this->password = strval($password);
    }

    /**
     * @param string|array|QueryString $queryString QUERY_STRINGデータ
     */
    private function setQueryString($queryString)
    {
        if ($queryString instanceof QueryString) {
            $this->queryString = clone $queryString;
        } else {
            $this->queryString = new QueryString($queryString);
        }
    }

    /**
     * @param string|array|QueryString $queryString QUERY_STRINGデータ
     */
    private function appendQueryString($queryString)
    {
        if ($queryString instanceof QueryString) {
            $workQueryString = $queryString;
        } else {
            $workQueryString = new QueryString($queryString);
        }
        if ($workQueryString->isEmpty()) {
            return;
        }

        $newQueryString = $this->getQueryString();
        $newQueryString->append($workQueryString);
        $this->setQueryString($newQueryString);
    }

    /**
     * @param string $fragment fragment
     */
    private function setFragment($fragment)
    {
        $this->fragment = strval($fragment);
    }

    /**
     * @param string $scheme プロトコル
     * @param int $port ポート
     * @return bool 標準以外のポートだったらtrue, それ以外はfalse
     */
    protected static function isNotDefaultPort($scheme, $port)
    {
        if (strlen($scheme) < 1) {
            return true;
        }
        if (isset(static::$schemeToDefaultPort[$scheme])) {
            if ($port !== static::$schemeToDefaultPort[$scheme]) {
                return true;
            }
        }
        return false;
    }
}
