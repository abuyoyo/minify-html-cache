<?php
/**
 * Plugin Name: Minify HTML Cache
 * Description: HTML minifier add-on plugin to WP Super Cache and WP-Optimize cache plugins. Minifies HTML on static cache pages.
 * Author: abuyoyo
 * Author URI: https://github.com/abuyoyo
 * Plugin URI: https://github.com/abuyoyo/minify-html-cache
 * License: GPL-2
 */
if ( ! defined( 'ABSPATH' ) ) 
	die( 'No soup for you!' );

use WPHelper\PluginCore;

new PluginCore(
	__FILE__,
	[
		'title' => 'Minify HTML Cache',
		'slug' => 'minify-html-cache',
		'update_checker' => true,
	]
);

require_once 'src/WPSCMin.php';

include_once 'src/plugins/wp-super-cache/wp-super-cache.php';
include_once 'src/plugins/wp-super-cache/WPSuperCacheStatic.php';
include_once 'src/plugins/wp-optimize/wp-optimize.php';

include_once 'src/minifiers/MinifierInterface.php';