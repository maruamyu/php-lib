<?php

namespace Maruamyu\Core\Http\Driver;

use Maruamyu\Core\Http\Message\MultipartData;
use Maruamyu\Core\Http\Message\QueryString;
use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;

/**
 * HTTP通信 処理クラス
 */
abstract class DriverAbstract implements DriverInterface
{
    const DEFAULT_TIMEOUT_SEC = 5;

    const MAXREDIRS = 10;

    const LINE_END = "\r\n";

    protected static $allowProtocols = [
        'http' => true,
        'https' => true,
    ];

    /** @var Request */
    protected $request = null;

    /** @var Response */
    protected $response = null;

    /**
     * @var bool
     */
    protected $isFollowRedirect = false;

    /**
     * インスタンスを初期化する.
     *
     * @param Request $request リクエスト情報
     * @throws \RuntimeException 必要なモジュールがロードされていないとき
     *
     */
    public function __construct(Request $request = null)
    {
        if (!extension_loaded('openssl')) {
            throw new \RuntimeException('OpenSSL module not found');
        }
        if ($request) {
            $this->setRequest($request);
        }
    }

    /**
     * リクエスト情報を設定する.
     *
     * @param Request $request リクエスト情報
     * @throws \InvalidArgumentException 指定されたリクエストのURLが正しくないとき
     */
    public function setRequest(Request $request)
    {
        $requestUri = $request->getUri();
        $requestUriString = strval($requestUri);
        if (!($requestUri) || strlen($requestUriString) < 1) {
            throw new \InvalidArgumentException('invalid URL: ' . $requestUriString);
        }
        $protocol = $requestUri->getScheme();
        if (!($protocol) || !(static::$allowProtocols[$protocol])) {
            throw new \InvalidArgumentException('invalid protocol: ' . $protocol);
        }
        $this->request = clone $request;
    }

    /**
     * @param bool $isFollow リダイレクトを追いかける:true,
     *   リダイレクトを追いかけない:false
     */
    public function setFollowRedirect($isFollow)
    {
        $this->isFollowRedirect = !!($isFollow);
    }

    /**
     * リクエストを実行する.
     *
     * @return Response レスポンス
     */
    abstract public function execute();

    /**
     * 最後のレスポンスを取得する.
     *
     * @return Response レスポンス
     */
    public function getLatestResponse()
    {
        return $this->response;
    }

    /**
     * multipart/form-data を生成し, Content-Typeも設定する.
     */
    protected function buildMultipartFormDataAndSetContentType()
    {
        $multipartFormData = '';
        $boundary = bin2hex(openssl_random_pseudo_bytes(32));

        $curRequestBodyStream = $this->request->getBody();
        if ($curRequestBodyStream->getSize() > 0) {
            $curRequestBody = strval($curRequestBodyStream);
            list($curContentType) = explode(';', $this->request->getContentType());
            if (strlen($curContentType) < 1 || strcasecmp($curContentType, 'application/x-www-form-urlencoded') == 0) {
                $formData = new QueryString($curRequestBody);
                $multipartFormData .= $formData->toMultiPartFormData($boundary, static::LINE_END);
            } else {
                $multipartFormData .= '--' . $boundary . static::LINE_END;
                $multipartFormData .= 'Content-Type: ' . $curContentType . static::LINE_END;
                $multipartFormData .= static::LINE_END;
                $multipartFormData .= $curRequestBody . static::LINE_END;
            }
        }

        $multipartDataList = $this->request->getMultipartDataList();
        foreach ($multipartDataList as $multipartData) {
            if (!($multipartData instanceof MultipartData)) {
                continue;
            }
            $dataStream = $multipartData->getStream();
            $multipartFormData .= '--' . $boundary . static::LINE_END;
            $multipartFormData .= 'Content-Type: ' . $multipartData->getContentType() . static::LINE_END;
            $multipartFormData .= 'Content-Disposition: form-data';
            $multipartFormData .= '; name="' . str_replace('"', '\\"', $multipartData->getName()) . '"';
            $multipartFormData .= '; filename="' . str_replace('"', '\\"', $multipartData->getFileName());
            $multipartFormData .= static::LINE_END;
            $multipartFormData .= static::LINE_END;
            $multipartFormData .= strval($dataStream) . static::LINE_END;
        }

        $multipartFormData .= '--' . $boundary . '--';  # 終端

        $newContentType = 'multipart/form-data; boundary=' . $boundary;
        $this->request = $this->request->withHeader('Content-Type', $newContentType);

        return $multipartFormData;
    }
}
