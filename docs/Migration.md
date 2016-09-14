## 既存WordPressサイトへの導入手順

![eye-catch.jpg](images/eye-catch.jpg)

## 既存WordPressサイトへの導入手順の例

ここではすでに稼働しているWordPressサイトに対し`wp-sacloud-ojs`を度運輸する手順を解説します。

[README.md](README.md)を参照し、`wp-sacloud-ojs`のインストール、設定を完了しておいてください。

**念のため、既存WordPressサイトのバックアップを取得しておくことを強くお勧めいたします。**

なお、この手順では導入作業中はサイトの閲覧ができなくなることがあります。

現在のWordPressサイトをコピーしたステージング環境を用意してから作業する方法がお勧めです。

## 必要な作業/手順

以下の作業/手順が必要です。

  - 1) WP-CLIのインストール
  - 2) 既存のメディアファイルをオブジェクトストレージへアップロード
  - 3) 既存の記事内にあるメディアファイルのURLを書き換え

1) WP-CLIのインストール

WP-CLIのPharファイルをダウンロード、インストールします。詳細は[WP-CLI公式サイトのインストール手順](http://wp-cli.org/ja/#section-1)を参照ください。

配置先のパスなどは適宜置き換えてください。

```bash
# WP-CLIのpharファイルをダウンロード
$ curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

# 実行権付与
$ chmod +x wp-cli.phar

# PATHの通ったディレクトリに配置
$ sudo mv wp-cli.phar /usr/local/bin/wp
```

インストールしたら`wp --info`コマンドを実行してみましょう。
インストールに成功していれば以下のような表示が行われます。

```
$ wp --info
PHP binary:    /usr/bin/php5
PHP version:    5.5.9-1ubuntu4.14
php.ini used:   /etc/php5/cli/php.ini
WP-CLI root dir:        /home/wp-cli/.wp-cli
WP-CLI packages dir:    /home/wp-cli/.wp-cli/packages/
WP-CLI global config:   /home/wp-cli/.wp-cli/config.yml
WP-CLI project config:
WP-CLI version: 0.23.0
```


## 2) 既存のメディアファイルをオブジェクトストレージへアップロード

WP-CLIを用いてオブジェクトストレージへのアップロードを行います。
WordPressを配置しているディレクトリに移動し、`wp`コマンドを実行します。

以下はWordPressを`/var/www/html/blog`に配置している場合の例です。

```bash

# WordPress配置先ディレクトリに移動
$ cd /var/www/html/blog

# アップロード実行
$ wp sacloud-ojs upload-all

```

成功すれば以下のような表示になります。

```bash
$ wp sacloud-ojs upload-all
2016-09-P6044534.jpg                               : [success]
2016-09-P6044534-150x150.jpg                       : [success]
2016-09-P6044534-300x225.jpg                       : [success]
2016-09-P6044534-768x576.jpg                       : [success]
2016-09-P6044534-1024x768.jpg                      : [success]
2016-09-P6044534-1200x900.jpg                      : [success]
Success: アップロードが完了しました

```

**注意**

WP-CLIはrootで実行しようとすると以下のようなエラーとなります。

```bash

Error: YIKES! It looks like you're running this as root. You probably meant to run this as the user that your WordPress install exists under.

If you REALLY mean to run this as root, we won't stop you, but just bear in mind that any code on this site will then have full control of your server, making it quite DANGEROUS.

If you'd like to continue as root, please run this again, adding this flag:  --allow-root

If you'd like to run it as the user that this site is under, you can run the following to become the respective user:

    sudo -u USER -i -- wp <command>

```

この場合、

  - root外のユーザーで実行する
  - `--allow-root`オプションを用いる
  - `sudo`コマンドで一般ユーザーとして実行する

などの対応を行ってください。

## 3) 既存の記事内にあるメディアファイルのURLを書き換え

これから作成する記事については、添付ファイルのURLは自動でオブジェクトストレージのものになります。
しかし、既存の記事についてはURLを書き換える必要があります。

書き換えには様々な方法がありますが、ここでは例としてWP-CLIの`search-replace`コマンドを用いた方法を紹介いたします。

書き換えを行うには、以下の値が必要です。

  - 1) 既存記事内のメディアファイルの基底URL
  - 2) オブジェクトストレージの基底URL

1)は既存の記事内の画像部分などを参照して調べておいてください。
通常、ドキュメントルート直下にインストールした場合は`http(s)://ドメイン名/wp-content/uploads/`などになっています。

2)のオブジェクトストレージのURLは、設定により以下の値をとります。

キャッシュ配信URLを利用する場合

    http(s)://バケット名.c.sakurastorage.jp/

キャッシュ配信URLを利用しない場合

    http(s)://b.sakurastorage.jp/バケット名/


### コマンド書式

```bash
$ wp search-replace "1)既存記事内のメディアファイルのURL" "2)オブジェクトストレージのURL" --include-columns=post_content
```

`--dry-run`オプションをつけることで、テストを行うこともできます。

#### 実行例

以下のような構成の場合、

  - 既存記事内のメディアファイルのURL : `http://example.com/wp-content/uploads/`
  - オブジェクトストレージのURL : `https://bucket-name.c.sakurastorage.jp/` 
  
コマンドは以下のようになります。  

```bash
$ wp search-replace "http://example.com/wp-content/uploads/" "https://bucket-name.c.sakurastorage.jp/" --include-columns=post_content
```

うまくいけば既存の記事内のURLが書き換えられています。
