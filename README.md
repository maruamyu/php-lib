まるあみゅ.ねっと PHPライブラリ
===============================

[![Build Status](https://travis-ci.org/maruamyu/php-lib.svg?branch=master)](https://travis-ci.org/maruamyu/php-lib)
[![Latest Stable Version](https://img.shields.io/packagist/v/maruamyu/web-app-lib.svg)](https://packagist.org/packages/maruamyu/web-app-lib)

## 概要

[まるあみゅ.ねっと](http://maruamyu.net/)で利用している,
PHPのWebアプリ向けの便利ライブラリを公開しています.

現在のところ `master` ブランチは v2.\* になっています.

v2.\* はnightly buildの位置付けで, 破壊的な変更が行われる可能性がありますので, あしからずご了承を.

## モジュール

名前空間 `Maruamyu\Core\` の下に各モジュールが配置されています.

- `Cipher` : RSAなどデジタル署名や暗号化 (OpenSSLのラッパー)
- `Http` : PSR-7準拠, 独自拡張ありのHTTP関連処理 (cURLのラッパー)
- `OAuth1` : OAuth1.0 関連処理
- `OAuth2` : OAuth2.0, OpenID Connect 関連処理 JWTなど

## インストール

```
composer require maruamyu/web-app-lib
```

## ライセンス

リポジトリ内のコードはMITライセンスです.
