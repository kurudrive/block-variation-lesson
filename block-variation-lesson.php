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


