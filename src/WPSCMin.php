<?php
/*  class WPSCMin
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

class WPSCMin {

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
	 * Whether Minify library has been loaded
	 */
	private $loaded_minify = false;

	/**
	 * Set to TRUE if $wp_cache_not_logged_in is enabled and 
	 * wp_cache_get_cookies_values() returns a non-empty string.
	 * See wp-cache-phase2.php for "Not caching for known user."
	 * 
	 */
	private $skipping_known_user = FALSE;

	/**
	 * Array for holding escaped (non-modified) strings
	 */
	private $escapedStrings = array();

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
		 * Minify library loaded
		 */
		if ( class_exists( 'Minify_HTML' ) ){
			$this->loaded_minify = true;
		}

		/**
		 * library loaded
		 */
		$this->lib_loaded = ( $this->loaded_minify );

		if ( ! $this->lib_loaded )
			$this->enabled = false;
	}

	/**
	 * instance
	 * 
	 * @return object instance of WPSCMin
	 */
	public static function instance() {
		if ( empty( self::$instance ) )
			self::$instance = new self();
		return self::$instance;
	}

	public static function skip_known_user() {
		self::instance()->skipping_known_user = TRUE;
	}

	/**
	 * minify_page
	 * 
	 * Given string $html, returns minified version.
	 * Preserves HTML comments appended by WP Super Cache
	 * 
	 * @param String $html 
	 * @return String $html - minified HTML
	 */
	public static function minify_page( $html ) {
		self::instance()->minify( $html );
		return $html;
	}


	/**
	 * minify
	 * 
	 * Minifies string referenced by $html, if $this->enabled is TRUE
	 * 
	 * @param reference string $html
	 * @return void $html string modified by reference
	 */
	public function minify( & $html ) {
		if ( ! $this->enabled or $this->skipping_known_user )
			return;

		/**
		 * If loading of Minify library failed - exit
		 */
		if ( ! $this->loaded_minify )
			return;

		
		/**
		 * Add min/lib to include_path for CSS.php to be able to find components
		 * 
		 * @todo this only needs to run when doing minify
		 */
		if ( class_exists('Minify_CSS') ){
			$minify_path = dirname( ( new ReflectionClass('Minify_CSS') )->getFileName() );
			ini_set( 'include_path', ini_get( 'include_path' ) . PATH_SEPARATOR . $minify_path );
		}

		/**
		 * Minify ~3.0 uses a different JS minifier than Minify ~2.3
		 */
		if ( class_exists( 'Minify\\JS\\JShrink' ) ){ // Minify ~3.0
			$this->js_minifier = 'Minify\\JS\\JShrink';
		}else if ( class_exists( 'JSMinPlus' ) ){ // Minify ~2.3
			$this->js_minifier = 'JSMinPlus';
		}

		/**
		 * Protect from minify any fragments escaped by
		 * <!--[minify_skip]-->   protected text  <!--[/minify_skip]-->
		 */
		$this->escapedStrings = array();
		$html = preg_replace_callback(
			'#<\!--\s*\[minify_skip\]\s*-->((?:[^<]|<(?!<\!--\s*\[/minify_skip\]))+?)<\!--\s*\[/minify_skip\]\s*-->#i',
			[ $this, 'minify_skip_capture' ],
			$html
		);

		$html = Minify_HTML::minify(
			$html,
			[
				'cssMinifier' => [ 'Minify_CSS', 'minify' ],
				'jsMinifier' => [ 'JSMinPlus', 'minify' ]
			]
		);

		// Restore any escaped fragments
		$html = str_replace(
			array_keys( $this->escapedStrings ),
			$this->escapedStrings,
			$html
		);
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
		</fieldset>
		<?php
	}

	/**
	 * minify_skip_capture
	 * 
	 * used as callbak by preg_replace_callback()
	 * in Minify() to capture [minify-skip] tag
	 */
	private function minify_skip_capture( $matches ) {
		$placeholder = 'X_WPSCMin_escaped_string_' . count( $this->escapedStrings );
		$this->escapedStrings[$placeholder] = $matches[1];
		return $placeholder;
	}
}


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
	if (isset($_POST[WPSCMin::$config_varname]))
		WPSCMin::instance()->update_option($_POST[WPSCMin::$config_varname]);

	// Print HTML Minify configuration section
	WPSCMin::instance()->print_options_form($_SERVER['REQUEST_URI']);
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
		WPSCMin::skip_known_user();
	}
	return $string;
}

add_cacheaction('wp_cache_get_cookies_values', 'wpscmin_check_known_user');

endif;