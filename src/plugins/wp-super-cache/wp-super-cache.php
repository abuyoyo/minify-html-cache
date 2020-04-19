<?php
/**
 * WP Super Cache functions file
 * 
 * Only run if wp-super-cache is enabled
 */
if ( function_exists('add_cacheaction') ):

/* function wpscmin_settings
	*
	* Inserts an "on/off switch" for HTML Minify into the WP Super Cache
	* configuration screen in WordPress' settings section.
	*
	* Must be defined as a function in global scope to be usable with the
	* add_cacheaction() plugin hook system of WP Super Cache that is documented
	* here:
	*
	* http://ocaoimh.ie/wp-super-cache-developers/
	*/

function wpscmin_settings() {
	// Update option if it has been changed
	if (isset($_POST[WPSuperCacheStatic::$config_varname]))
		WPSuperCacheStatic::instance()->update_option($_POST[WPSuperCacheStatic::$config_varname]);

	// Print HTML Minify configuration section
	WPSuperCacheStatic::instance()->print_options_form($_SERVER['REQUEST_URI']);
}

add_cacheaction('cache_admin_page', 'wpscmin_settings');


/* function wpscmin_minify
	*
	* Adds filter to minify the WP Super Cache buffer when wpsupercache_buffer
	* filters are executed in wp-cache-phase2.php.
	*
	* Must be defined as a function in global scope to be usable with the
	* add_cacheaction() plugin hook system of WP Super Cache.
	*/

function wpscmin_minify() {
	if ( ! WPSuperCacheStatic::is_enabled() or WPSuperCacheStatic::is_skipping_known_user() )
		return;

	add_filter('wpsupercache_buffer', array('WPSCMin', 'minify_page'));
}

add_cacheaction('add_cacheaction', 'wpscmin_minify');


/* function wpscmin_check_known_user
	*
	* Checks filtered $_COOKIE contents and global var $wp_cache_not_logged_in 
	* to skip minification of dynamic page contents for a detected known user. 
	* Action is called inside wp_cache_get_cookies_values().
	*
	* Must be defined as a function in global scope to be usable with the
	* add_cacheaction() plugin hook system of WP Super Cache.
	*/

function wpscmin_check_known_user($string) {
	if ($GLOBALS['wp_cache_not_logged_in'] and $string != '') {
		// Detected known user per logic in wp-cache-phase2.php 
		// (see line 378 in WP Super Cache 0.9.9.8)
		WPSuperCacheStatic::skip_known_user();
	}
	return $string;
}

add_cacheaction('wp_cache_get_cookies_values', 'wpscmin_check_known_user');

endif;