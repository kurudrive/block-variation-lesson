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
			backgroundColor: 'accent'
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
			backgroundColor: 'accent'
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

# 効かん！

## インナーブロック

インナーブロック を指定する事ができます。
以下の例ではインナーブロックにデフォルトで見出しブロックと段落ブロックを配置しています。

```
wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'media-text-default',
		title: 'Media & Text',
		attributes: {
			align: 'wide',
			backgroundColor: 'accent'
		},
		innerBlocks: [
			[
				'core/heading',
				{
					level: 3,
					placeholder: 'Heading'
				} 
			],
			[
				'core/paragraph',
				{
					placeholder: 'Enter content here...'
				} 
			],
		],
		isDefault: true,
	}
);
```

## isActive property

次に以下の例では、画像が右のブロックがババリエーションとして追加されます。
名前は 元が「メディアとテキスト」ですが、今回は「Text & Media」としています。

```
wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'text-media',
		title: 'Text & Media',
		attributes: {
			align: 'wide',
			backgroundColor: 'tertiary',
			mediaPosition: 'right'
		}
	}
);
```

しかし、この方法はユーザーがコアかバリエーションかを判断できず、混乱を招きやすい。
数が増えればなおさらである。
そこで特定のプロパティに isActive を指定すると、その値によってバリエーションブロックを判断し、ブロック名が切り替わります。

以下のようにバリエーションを配置してみてください。

```
wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'text-media',
		title: 'Text & Media',
		attributes: {
			align: 'wide',
			backgroundColor: 'tertiary',
			mediaPosition: 'right'
		},
		isActive: [ 'mediaPosition' ]
	}
);
```

画像が右の時はブロック名が 'Text & Media' になる事が確認できます。

## カスタムアイコンの追加

カスタムバリエーションを追加する場合は、多くの場合独自のアイコンを登録したいと思います。
24px で svg でなくてはなりません。
以下のような形式です。

```
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
    <path d="M21 17.5L21 6L13 6L13 17.5L21 17.5ZM10 14.5L3 14.5L3 16L10 16L10 14.5ZM3 11L10 11L10 12.5L3 12.5L3 11ZM10 7.5L3 7.5L3 9L10 9L10 7.5Z"></path>
</svg>
```

WordPress のダッシュアイコンは ストーリーブック からブラウザの検証ツールで抽出する事もできます。
https://wordpress.github.io/gutenberg/?path=/story/icons-icon--library
これを実際に反映するにはまずは、以下のように登録します。

```
// Define the icon for the Text & Media block variation.
const textMediaIcon = wp.element.createElement(
	wp.primitives.SVG,
	{ xmlns: "http://www.w3.org/2000/svg", viewBox: "0 0 24 24" },
	wp.element.createElement(
		wp.primitives.Path,
		{
			d: "M21 17.5L21 6L13 6L13 17.5L21 17.5ZM10 14.5L3 14.5L3 16L10 16L10 14.5ZM3 11L10 11L10 12.5L3 12.5L3 11ZM10 7.5L3 7.5L3 9L10 9L10 7.5Z",
		}
	)
);
```

ただし、上記コードでは wp.element と wp.primitives を使っているので、
php で wp_enqueue_script している部分で依存配列に 'wp-element' と 'wp-primitives' を追加する必要があります。

```
function example_enqueue_block_variations() {
	wp_enqueue_script(
		'example-enqueue-block-variations',
		plugin_dir_url( __FILE__ ) . '/build/index.js',
		array( 'wp-blocks', 'wp-dom-ready', 'wp-element', 'wp-primitives' ),
		__FILE__ . 'build/index.asset.php',
		false
	);
}
add_action( 'enqueue_block_editor_assets', 'example_enqueue_block_variations' );

```

最後に js ファイルに戻って、registerBlockVariation を書いてある部分に icon を指定します。

```
wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'text-media',
		title: 'Text & Media',
		icon: textMediaIcon,
		attributes: {
			align: 'wide',
			backgroundColor: 'accent',
			mediaPosition: 'right'
		},
		isActive: [ 'mediaPosition' ]
	}
);
```

これでビルドすると...

こんな感じでアイコンが反映されます。
ただ、この書き方では実用的ではないので

### WordPress のアイコンをもう少しスマートに使う

WordPress の ダッシュアイコンを使うのであれば

```
npm install @wordpress/icons --save
```

した上で以下のように書くと、アイコンを読み込めます。

```
// Define the icon for the Text & Media block variation.
import { pullRight } from '@wordpress/icons';

wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'text-media',
		title: 'Text & Media',
		icon: pullRight,
		attributes: {
			align: 'wide',
			backgroundColor: 'accent',
			mediaPosition: 'right'
		},
		isActive: [ 'mediaPosition' ]
	}
);
```

アイコンの種類は前述の通り ストーリーブック から調べられます。
https://wordpress.github.io/gutenberg/?path=/story/icons-icon--library

### 自作のアイコンを使う

自作した svg を使う場合は、ビルド前の js ファイルを同じディレクトリに icon.svg で保存した場合、
以下のように最初に ReactComponent で読み込んだ上で使います。

```
// Define the icon for the Text & Media block variation.
import { ReactComponent as Icon } from './icon.svg';

wp.blocks.registerBlockVariation(
	'core/media-text',
	{
		name: 'text-media',
		title: 'Text & Media',
		icon: <Icon />,
		attributes: {
			align: 'wide',
			backgroundColor: 'accent',
			mediaPosition: 'right'
		},
		isActive: [ 'mediaPosition' ]
	}
);
```