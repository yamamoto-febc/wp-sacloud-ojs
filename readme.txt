=== SakuraCloud ObjectStorage Plugin ===
Contributors: yamamotofebc
Tags: SakuraCloud, object storage
Requires at least: 0.1
Tested up to: 0.1
Stable tag: 0.1
License: GPLv2 or later.
License URI: http://www.gnu.org/licenses/gpl-2.0.html

SakuraCloud ObjectStorage Plugin is a simple plugin for WordPress that helps you to synchronizes media files with SakuraCloud Object Storage.

== Description ==

This WordPress plugin allows you to upload media files from the library to [SakuraCloud](http://cloud.sakura.ad.jp) Object Storage.

These files then load from the Object Storage and optimize the your site/blog performance.


= Features =

* Synchronization media files with the Object Storage.
* Automatically rewrite the media url to the endpoint url.

= For Japanese users. =

WordPressのメディアファイル(画像など)をさくらのクラウドのオブジェクトストレージで扱うためのWordPressプラグインです。

WordPressの管理画面からメディアを追加すると、自動的にオブジェクトストレージにアップロードを行います。オブジェクトストレージは容量無制限なため、空き容量を気にすること無くメディアファイルを扱うことができます。

また、このプラグインはメディアファイルのURLを変更し、オブジェクトストレージから直接配信するように設定します。これにより、WordPressを運用しているサーバに負荷をかけずに、メディアファイルを配信することができます。

さくらのクラウド オブジェクトストレージの機能である、SSL配信、キャッシュ配信にも対応しています。

== Installation ==

1. Run the following command.
2. Activate the plugin through the 'Plugins' menu in WordPress

* cd [WORDPRESS_ROOT]/wp-content/plugins
* git clone https://github.com/yamamoto-febc/wp-sacloud-ojs
* cd wp-sacloud-ojs
* curl -sS https://getcomposer.org/installer | php
* ./composer.phar install

== Upgrade Notice ==

No upgrade, so far.

== Screenshots ==

1. Edit settings through the 'Settings' menu as you like.

== Changelog ==

= 0.1 =
* First release.
