## 

今回はプラグインとして開発します。
ここでは block-variation-lesson という名前のプラグインを作ります。
ブロック関連の開発の場合は 

```
npx @wordpress/create-block block-variation-lesson
```

のように書けば雛形ができたりするのですが、ここではブロック自体は作成しなかったり、
他のプロジェクト（テーマなど）に組み込む場合も踏まえて対応できるように、手動でセットアップしていきます。

## プラグインディレクトリの作成

とりあえずプラグイン用のディレクトリを作ります。
ここでは block-variation-lesson にします。
開発環境が wp-env の人はどこでもいいです。local ( by flywheel ) などの人は wp-content/plugins/ ディレクトリ内につくってください。

## プラグインファイルの作成・有効化

block-variation-lesson.php を作って、以下のように書きます。

```
<?php
/**
 * Plugin Name:       Block Variation Lesson
 * Description:
 * Requires at least: 6.1
 * Requires PHP:      7.2
 * Version:           0.1.0
 * Author:
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       block-variation-lesson
 */
```

とりあえずこれでプラグインとして有効化できますので有効化

## ビルド環境の設定

続いてビルド環境をセットアップしていきましょう。
package.json の準備をします。

```
{
  "name": "block-variation-lesson",
  "version": "0.1.0",
  "description": "",
  "author": "",
  "license": "GPL-2.0-or-later",
  "main": "build/index.js",
  "scripts": {
  }
}
```

WordPress の開発用のスクリプトパッケージをインストールします。

```
npm install @wordpress/scripts --save-dev
```

package.json にビルド用のスクリプトを追加

```
		"start": "wp-scripts start",
		"build": "wp-scripts build"
```

---

## バリエーションを認識させてみる

### まずは作業用ファイルを用意してビルドしてみる

コアの「メディアとテキスト」ブロックのバリエーションを作ってみます。

src/index.js ファイルを用意します。

```
wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'media-text-custom',
		title: 'Media & Text Custom',
		attributes: {
			align: 'wide',
			backgroundColor: 'tertiary'
		},
	}
);
```

これを保存して

```
npm run build
```

してみると、

build
├index.asset.php
└index.js

ができます。

しかし、この js ファイルはまだどこからも読み込まれていないので、  
プラグインファイルから読み込みます。

block-variation-lesson.php に以下を追加します

```
function example_enqueue_block_variations() {
	wp_enqueue_script(
		'example-enqueue-block-variations',
		plugin_dir_url( __FILE__ ) . '/build/index.js',
		array( 'wp-blocks', 'wp-dom-ready' ),
		__FILE__ . 'build/index.asset.php',
		false
	);
}
add_action( 'enqueue_block_editor_assets', 'example_enqueue_block_variations' );
```

WordPressの記事編集画面でブロックを挿入してみます。

検索入力欄に media と入力すると、先程作った Media & Text Custom が選択できるようになっています。

## デフォルト値の変更

現状はブロックバリエーションとして登録しただけなので、挿入しても標準の メディアとテキスト と同じ状態です。
独自のバリエーションを登録する場合は先の方法で良いのですが、そもそもデフォルトのブロックの状態を上書きする事ができます。

src/index.js を以下のように書き換えてみます。
背景色を変更しています（テーマは Twenty Twenty-Three です）

```
wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'media-text-default',
		title: 'Media & Text',
		attributes: {
			align: 'wide',
			backgroundColor: 'tertiary'
		},
		isDefault: true
	}
);
```

これをビルドして挿入してみると...


背景に色がついている事が確認できます。
こちらはあくまでデフォルト値なので、ユーザーは変更可能です。

## ブロックバリエーションの削除

ブロックのバリエーションを削除する事も可能です。例えばコアのグループブロックは、通常、横並び、縦積みがあります。

```
wp.domReady( () => {
	wp.blocks.unregisterBlockVariation( 'core/group', 'group-stack' );
});
```