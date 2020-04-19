<?php
/*  class WPSuperCacheStatic
 *  based on original class WPSCMin from original plugin
 *
 *  Singleton class add-on to WP Super Cache to interface with HTML Minify
 *  Author: Joel Hardi
 *  Author URI: http://lyncd.com/wpscmin/
 *  Version 0.7
 *
 *  WP Super Cache is a static caching plugin for WordPress
 *    For more information, see: http://ocaoimh.ie/wp-super-cache/
 *
 *  Minify is an HTML/CSS/JS whitespace compression library in PHP
 *    For more information, see: http://code.google.com/p/minify/
 *
 *  This plugin to WP Super Cache is a simple Singleton class that adds 
 *  minification of static HTML and gzipped HTML files that WP Super Cache 
 *  saves to the filesystem. It also adds a on/off configuration panel to 
 *  WP Super Cache's WordPress settings page in the WordPress backend.
 *
 *  It requires that you download and install Minify into the WP Super Cache 
 *  plugins directory. See http://lyncd.com/wpscmin/ for instructions.
 */
class WPSuperCacheStatic
{

	/**
	 * Whether Minify is enabled
	 */
	private $enabled = FALSE;

	/**
	 * Whether value of $enabled has been changed
	 */
	private $changed = FALSE;

	/**
	 * Full path and filename of wp-cache-config.php
	 * (currently set from global var $wp_cache_config_file)
	 */
	private $wp_cache_config_file;

	/**
	 * Name of global var (optionally) setting minify_path in wp-cache-config.php
	 * (if doesn't exist, constructor sets minify_path to Super Cache plugin dir)
	 */
	private $config_varname_minify_path = 'cache_minify_path';

	/**
	 * Set to TRUE if $wp_cache_not_logged_in is enabled and 
	 * wp_cache_get_cookies_values() returns a non-empty string.
	 * See wp-cache-phase2.php for "Not caching for known user."
	 * 
	 */
	private $skipping_known_user = FALSE;

	/**
	 * Name of global config var set in wp-cache-config.php
	 */
	public static $config_varname = 'cache_minify';

	/**
	 * Static instance
	 */
	private static $instance;

	/**
	 * Will run once only, since private and called only by instance()
	 */
	private function __construct() {

		/**
		 * vars from wp-cache-config.php are initialized in global scope, so just
		 * get initial value of $enabled from there
		 */
		if ( isset( $GLOBALS[self::$config_varname] ) and $GLOBALS[self::$config_varname] )
			$this->enabled = TRUE;

		/**
		 * Set location of WP Super Cache config file wp-cache-config.php from global var
		 */
		if ( isset( $GLOBALS['wp_cache_config_file'] ) and file_exists( $GLOBALS['wp_cache_config_file'] ) )
			$this->wp_cache_config_file = $GLOBALS['wp_cache_config_file'];

		
		/**
		 * Sanity check - make sure a minifier library is loaded
		 */
		if ( ! WPSCMin::instance()->is_lib_loaded() )
			$this->enabled = false;
	}

	/**
	 * instance
	 * 
	 * @return object instance of WPSuperCacheStatic
	 */
	public static function instance() {
		if ( empty( self::$instance ) )
			self::$instance = new self();
		return self::$instance;
	}

	public static function skip_known_user() {
		self::instance()->skipping_known_user = TRUE;
	}

	public static function is_skipping_known_user() {
		return self::instance()->skipping_known_user;
	}

	public static function is_enabled() {
		return self::instance()->enabled;
	}

	public function update_option( $value ){
		$enabled = (bool) $value;
		if ( $enabled != $this->enabled ){
			$this->enabled = $enabled;
			$this->changed = TRUE;
			wp_cache_replace_line(
				'^ *\$'.self::$config_varname,
				"\$".self::$config_varname ." = " . var_export( $enabled, TRUE ) . ";",
				$this->wp_cache_config_file
			);
		}
	}

	public function print_options_form( $action ) {
		$id = 'htmlminify-section';
		?>
		<fieldset id="<?php echo $id; ?>" class="options">
		<h4>HTML Minify</h4>
		<?php if ( ! WPSCMin::instance()->is_lib_loaded() ): ?>
			<p><strong>Minify library components could not be loaded. Minify HTML will not work. Please set $<?=$this->config_varname_minify_path?> variable in <?=$this->wp_cache_config_file?> to point to Minify library directory. Or autoload mrclay/minify v2.3 via Composer.</strong></p>
		<?php else: ?>
			<form name="wp_manager" action="<?php echo $action.'#'.$id; ?>" method="post">
				<label><input type="radio" name="<?= self::$config_varname ?>" value="1" <?php checked( $this->enabled, true ) ?>/> Enabled</label>
				<label><input type="radio" name="<?= self::$config_varname ?>" value="0" <?php checked( $this->enabled, false ) ?>/> Disabled</label>
				<p>Enables or disables <a target="_blank" href="http://code.google.com/p/minify/">Minify</a> (stripping of unnecessary comments and whitespace) of cached HTML output. Disable this if you encounter any problems or need to read your source code.</p>
				<?php if ( $this->changed ): ?>
				<p><strong>HTML Minify is now <?= ($this->enabled) ? 'enabled' : 'disabled' ?>.</strong></p>
				<?php endif; ?>
				<div class="submit">
					<input <?= SUBMITDISABLED ?> class="button-primary" type="submit" value="Update" />
				</div>
				<?php wp_nonce_field( 'wp-cache' ); ?>
			</form>
		<?php endif; ?>
		</fieldset>
		<?php
	}
	
}