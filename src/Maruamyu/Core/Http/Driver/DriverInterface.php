<?php

namespace Maruamyu\Core\Http\Driver;

use Maruamyu\Core\Http\Message\Request;
use Maruamyu\Core\Http\Message\Response;

/**
 * HTTP通信 処理クラス インタフェース
 */
interface DriverInterface
{
    /**
     * インスタンスを初期化する.
     *
     * @param Request $request リクエスト情報
     */
    public function __construct(Request $request = null);

    /**
     * リクエスト情報を設定する.
     *
     * @param Request $request リクエスト情報
     */
    public function setRequest(Request $request);

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
