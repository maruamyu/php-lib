<?php

namespace Maruamyu\Core\Http;

use Maruamyu\Core\Http\Driver\DriverFactory;
use Maruamyu\Core\Http\Driver\DriverInterface;
use Maruamyu\Core\Http\Message\Header;
use Maruamyu\Core\Http\Message\NormalizeMessageTrait;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;
use Maruamyu\Core\Http\Message\UriInterface;

/**
 * HTTPクライアント
 */
class Client
{
    use NormalizeMessageTrait;

    /**
     * @var Header
     */
    protected $defaultHeaders;

    /**
     * @var DriverFactory
     */
    protected $httpDriverFactory = null;

    /**
     * @var Response
     */
    protected $latestResponse = null;

    /**
     * @param array $config 設定
     */
    public function __construct(array $config = [])
    {
        if (isset($config['headers'])) {
            $this->defaultHeaders = new Header($config['headers']);
        } else {
            $this->defaultHeaders = null;
        }
    }

    /**
     * 指定されたリクエストメッセージを送信し, レスポンスを返す.
     *
     * @param Request $request リクエスト情報
     * @return Response レスポンス
     */
    public function send(Request $request)
    {
        if (!$request->hasHeader('User-Agent')) {
            $defaultUserAgent = $this->defaultUserAgent();
            if (strlen($defaultUserAgent) > 0) {
                $request = $request->withHeader('User-Agent', $defaultUserAgent);
            }
        }

        $httpDriver = $this->getHttpDriver($request);
        $response = $httpDriver->execute();

        $this->latestResponse = $response;

        return $response;
    }

    /**
     * 指定されたメソッドとURLでリクエストを行う.
     *
     * @param string $method HTTPメソッド
     * @param string|UriInterface $uri URL
     * @param array $options オプション
     * @return Response レスポンス
     */
    public function request($method, $uri, array $options = [])
    {
        if ($options && isset($options['headers'])) {
            if ($this->defaultHeaders) {
                $headers = clone $this->defaultHeaders;
                $headers->merge($options['headers'], true);
            } else {
                $headers = new Header($options['headers']);
            }
        } else {
            $headers = $this->defaultHeaders;
        }
        $request = new Request($method, $uri, null, $headers);
        return $this->send($request);
    }

    /**
     * 最後のレスポンスを取得する.
     *
     * @return Response レスポンス
     */
    public function getLatestResponse()
    {
        return $this->latestResponse;
    }

    /**
     * User-Agent
     *
     * @return string User-Agent
     */
    protected function defaultUserAgent()
    {
        # デフォルト: HTTP処理クラス内で設定
        return null;
    }

    /**
     * @param Request $request リクエスト
     * @return DriverInterface HTTP処理クラス
     */
    protected function getHttpDriver(Request $request = null)
    {
        if (!$this->httpDriverFactory) {
            $this->httpDriverFactory = new DriverFactory();
        }
        return $this->httpDriverFactory->getDriver($request);
    }
}
