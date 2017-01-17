=== wp-sacloud-ojs ===
Contributors: yamamotofebc
Donate link:
Tags: SakuraCloud, object storage, さくらのクラウド, さくらインターネット, オブジェクトストレージ
Requires at least: 4.5.3
Tested up to: 4.7.1
Stable tag: 0.0.9
License: GPLv2 or later.
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPressのメディアファイル(画像など)をさくらのクラウドのオブジェクトストレージで扱うためのプラグイン

== Description ==

WordPressの管理画面からメディアを追加すると、自動的にオブジェクトストレージにアップロードを行います。

オブジェクトストレージは容量無制限なため、空き容量を気にすること無くメディアファイルを扱うことができます。

また、このプラグインはメディアファイルのURLを変更し、オブジェクトストレージから直接配信するように設定します。

これにより、WordPressを運用しているサーバに負荷をかけずに、メディアファイルを配信することができます。

さくらのクラウド オブジェクトストレージの機能である、SSL配信、キャッシュ配信にも対応しています。

= Features =

* メディアファイルをオブジェクトストレージに自動でアップロード
* メディアファイルのURLをオブジェクトストレージからの直接配信に書き換え

= 使い方とサポート =

[GitHub](https://github.com/yamamoto-febc/wp-sacloud-ojs/tree/master/docs)では、プラグインのインストール方法や設定方法などを掲載しています。

== Installation ==

ダウンロードしたプラグインのZipファイルを、/wp-content/plugins/ディレクトリにアップロードします。

ワードプレスのダッシュボード内の「プラグインメニュー」からプラグインを有効にします。

ダッシュボードの『プラグイン新規追加』からの追加も可能です。

== Frequently Asked Questions ==

お問い合わせはGitHubのIssueにてお願い致します。
https://github.com/yamamoto-febc/wp-sacloud-ojs

== Screenshots ==
1. screenshot-1.png

== Changelog ==

0.0.7 : [srcset属性のURL書き換え対応](https://github.com/yamamoto-febc/wp-sacloud-ojs/releases/tag/v0.0.7)

0.0.6 : [アップロード先の年月ディレクトリ対応、キャッシュ配信関連バグ修正、移行手順追加](https://github.com/yamamoto-febc/wp-sacloud-ojs/releases/tag/v0.0.6)

0.0.5 : [WP-CLI対応、オプション値の内部構造変更、アップロード後のファイル削除オプション対応](https://github.com/yamamoto-febc/wp-sacloud-ojs/releases/tag/v0.0.5)

0.0.4 : [メディアファイル削除時のフィルタロジック修正](https://github.com/yamamoto-febc/wp-sacloud-ojs/releases/tag/v0.0.4)

0.0.3 : [メディアファイル削除時にuploadsディレクトリ内にファイルが残存するバグを修正](https://github.com/yamamoto-febc/wp-sacloud-ojs/releases/tag/v0.0.3)

0.0.2 : [再同期時にサムネイルを生成する機能追加](https://github.com/yamamoto-febc/wp-sacloud-ojs/releases/tag/v0.0.2)

0.0.1 : 初回リリース

== Upgrade Notice ==

== Arbitrary section 1 ==