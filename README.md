# WordPress plugin for SakuraCloud ObjectStorage.

WordPressのメディアファイル(画像など)をさくらのクラウドのオブジェクトストレージで扱うためのWordPressプラグインです。

WordPressの管理画面からメディアを追加すると、自動的にオブジェクトストレージにアップロードを行います。
オブジェクトストレージは容量無制限なため、空き容量を気にすること無くメディアファイルを扱うことができます。

また、このプラグインはメディアファイルのURLを変更し、オブジェクトストレージから直接配信するように設定します。
これにより、WordPressを運用しているサーバに負荷をかけずに、メディアファイルを配信することができます。

さくらのクラウド オブジェクトストレージの機能である、SSL配信、キャッシュ配信にも対応しています。

## スクリーンショット

![screenshot-2.png](screenshot-2.png)


## インストール

WordPressの管理ページからインストール可能です。


## インストール(手動)

手動でインストールする場合は次のようにしてください。

1. 以下のコマンドを実行してください。
2. 管理画面の「プラグイン」メニューから有効化してください。

```bash

# Move into WordPress root
cd [WORDPRESS_ROOT]/wp-content/plugins

# Clone plugin repository
git clone https://github.com/yamamoto-febc/wp-sacloud-ojs
cd wp-sacloud-ojs

# Install libraries. 
curl -sS https://getcomposer.org/installer | php
./composer.phar install

```

# License

GPLv2

# Copyright

Copyright 2016 Kazumichi Yamamoto
