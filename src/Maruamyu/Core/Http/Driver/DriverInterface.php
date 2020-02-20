<?php

namespace Maruamyu\Core\Http\Driver;

use Maruamyu\Core\Http\Message\Response;
use Psr\Http\Message\RequestInterface;

/**
 * HTTP通信 処理クラス インタフェース
 */
interface DriverInterface
{
    /**
     * インスタンスを初期化する.
     *
     * @param RequestInterface $request リクエスト情報
     */
    public function __construct(RequestInterface $request = null);

    /**
     * リクエスト情報を設定する.
     *
     * @param RequestInterface $request リクエスト情報
     */
    public function setRequest(RequestInterface $request);

    /**
     * リクエストを実行する.
     *
     * @return Response レスポンス
     */
    public function execute();

    /**
     * 最後のレスポンスを取得する.
     *
     * @return Response レスポンス
     */
    public function getLatestResponse();
}
