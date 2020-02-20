<?php

namespace Maruamyu\Core\Http\Driver;

use Maruamyu\Core\Http\Message\Headers;
use Maruamyu\Core\Http\Message\Response;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP通信 処理クラス (cURL)
 */
class CURL extends DriverAbstract
{
    /**
     * @var resource
     */
    private $cURLHandler = null;

    /**
     * @var int
     */
    private $execCount = 0;

    /**
     * インスタンスを初期化する.
     *
     * @param RequestInterface $request リクエスト情報
     * @throws \RuntimeException 必要なモジュールがロードされていないとき
     */
    public function __construct(RequestInterface $request = null)
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('cURL module not found');
        }
        parent::__construct($request);
    }

    /**
     * 後処理を行う
     */
    public function __destruct()
    {
        $this->closeCURLHander();
    }

    /**
     * リクエストを実行する.
     *
     * @param int $timeoutSec タイムアウト秒数
     * @return Response レスポンス
     * @throws \RuntimeException リクエストがセットされていないとき
     */
    public function execute($timeoutSec = 0)
    {
        if (!$this->request) {
            throw new \RuntimeException('request is null.');
        }

        if ($timeoutSec < 1) {
            $timeoutSec = static::DEFAULT_TIMEOUT_SEC;
        }

        $cURLHandler = $this->getCURLHander();

        curl_setopt($cURLHandler, CURLOPT_TIMEOUT, $timeoutSec);

        if ($this->isFollowRedirect) {
            curl_setopt($cURLHandler, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($cURLHandler, CURLOPT_MAXREDIRS, static::MAXREDIRS);
        } else {
            curl_setopt($cURLHandler, CURLOPT_FOLLOWLOCATION, false);
        }

        curl_setopt($cURLHandler, CURLOPT_URL, strval($this->request->getUri()));

        curl_setopt($cURLHandler, CURLOPT_HTTPGET, true);  # POSTFIELDSをリセットする
        $method = strtoupper($this->request->getMethod());
        if ($method === 'GET') {
            # do nothing
        } elseif ($method === 'HEAD') {
            curl_setopt($cURLHandler, CURLOPT_NOBODY, true);
        } elseif ($method === 'POST') {
            curl_setopt($cURLHandler, CURLOPT_POST, true);
        } elseif (strlen($method) > 0) {
            # PUT, PATCH, DELETE, ...
            curl_setopt($cURLHandler, CURLOPT_CUSTOMREQUEST, $method);
        }

        $multipartDataList = $this->request->getMultipartDataList();
        if (count($multipartDataList) > 0) {
            $requestBody = $this->buildMultipartFormDataAndSetContentType();
            curl_setopt($cURLHandler, CURLOPT_POSTFIELDS, $requestBody);
        } else {
            $requestBodyStream = $this->request->getBody();
            if ($requestBodyStream->getSize() > 0) {
                $requestBody = strval($requestBodyStream);
                curl_setopt($cURLHandler, CURLOPT_POSTFIELDS, $requestBody);
            }
        }

        curl_setopt($cURLHandler, CURLOPT_HTTPHEADER, $this->request->getHeaderFields());
        if (!$this->request->hasHeader('User-Agent')) {
            curl_setopt($cURLHandler, CURLOPT_USERAGENT, $this->defaultUserAgent());
        }

        $responseBodyStream = fopen('php://temp', 'c+b');
        curl_setopt($cURLHandler, CURLOPT_FILE, $responseBodyStream);

        $responseHeaderStream = fopen('php://temp', 'c+b');
        curl_setopt($cURLHandler, CURLOPT_WRITEHEADER, $responseHeaderStream);

        curl_exec($cURLHandler);
        $this->execCount++;

        $response = static::parseResponse($responseHeaderStream, $responseBodyStream);
        if (!$response->getStatusCode()) {
            $statusCode = intval(curl_getinfo($cURLHandler, CURLINFO_HTTP_CODE), 10);
            $response = $response->withStatus($statusCode);
        }
        $this->response = $response;

        return $response;
    }

    /**
     * User-Agent
     *
     * @return string User-Agent (cURLのバージョン)
     */
    private function defaultUserAgent()
    {
        $cURLVersion = curl_version();
        return 'cURL/' . $cURLVersion['version'];
    }

    /**
     * cURLのハンドラを取得する
     *
     * @return resource cURLのハンドラ
     */
    private function getCURLHander()
    {
        if ($this->cURLHandler) {
            return $this->cURLHandler;
        } else {
            return $this->initCURLHander();
        }
    }

    /**
     * cURLのハンドラを初期化する
     *
     * @return resource cURLのハンドラ
     * @throws \RuntimeException cURLの初期化に失敗したとき
     */
    private function initCURLHander()
    {
        $cURLHandler = curl_init();
        if (!$cURLHandler) {
            throw new \RuntimeException('curl_init() failed');
        }
        curl_setopt($cURLHandler, CURLOPT_FILETIME, true);
        curl_setopt($cURLHandler, CURLOPT_ENCODING, '');  # deflate,gzip

        $this->closeCURLHander();
        $this->cURLHandler = $cURLHandler;
        return $cURLHandler;
    }

    /**
     * cURLのハンドラを閉じる
     */
    private function closeCURLHander()
    {
        if ($this->cURLHandler) {
            curl_close($this->cURLHandler);
            $this->cURLHandler = null;
        }
    }

    /**
     * レスポンスを読み取る.
     *
     * @param resource $headerHandler ヘッダのハンドラ
     * @param resource $bodyHandler 本文のハンドラ
     * @return Response レスポンス
     */
    private static function parseResponse($headerHandler, $bodyHandler)
    {
        $headers = new Headers();
        $statusCode = 0;
        $statusReasonPhrase = '';
        $protocolVersion = '';

        fseek($headerHandler, 0, SEEK_SET);
        while (!feof($headerHandler)) {
            $line = trim(fgets($headerHandler));
            $delimiterPos = strpos($line, ': ', 0);
            if ($delimiterPos !== false) {
                $name = substr($line, 0, $delimiterPos);
                $value = substr($line, ($delimiterPos + 2));
                $headers->add($name, $value);
            } elseif (preg_match('#^HTTP/([0-9\.]+) (\d+) ?(.*)$#u', $line, $match)) {
                $statusCode = intval($match[2], 10);
                $statusReasonPhrase = $match[3];
                $protocolVersion = $match[1];
            }
        }

        return new Response($bodyHandler, $headers, $statusCode, $statusReasonPhrase, $protocolVersion);
    }
}
