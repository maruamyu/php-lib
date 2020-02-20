<?php

namespace Maruamyu\Core\Http\Driver;

use Psr\Http\Message\RequestInterface;

/**
 * HTTP通信処理クラスのインスタンスを取得する
 */
class DriverFactory
{
    /**
     * HTTP通信処理クラスのインスタンスを取得する.
     *
     * @param RequestInterface $request リクエスト情報
     * @return DriverInterface HTTPドライバ インスタンス
     * @throws \RuntimeException 必要なモジュールがロードされていないとき
     */
    public function getDriver(RequestInterface $request = null)
    {
        if (static::isAvailableCURL()) {
            return new CURL($request);
        } else {
            # TODO streamでの実装
            throw new \RuntimeException('cURL module not found');
        }
    }

    /**
     * @return bool cURLモジュールが利用可能ならtrue, それ以外はfalse
     */
    public static function isAvailableCURL()
    {
        return extension_loaded('curl');
    }
}
