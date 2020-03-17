<?php
/**
 * Only run if WP-Optimize is on
 */
if ( function_exists('wpo_cache') ) :

global $cache_minify;
$cache_minify = true;

add_filter( 'wpo_pre_cache_buffer', array('WPSCMin', 'minify_page'), 20 );

endif;