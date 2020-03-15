<?php
/** 
 * class WPSCMin
 *
 * This class loads the minifier. Either Minify or HtmlMin
 * This class no longer loads into the static cache engine.
 * This class only deals with loading and running the minification library.
 * 
 * API:
 * WSCMin::Minify($html)
 * 
 */

class WPSCMin {

	/**
	 * Whether Minify library has been loaded
	 */
	private $loaded_minify = false;

	/**
	 * Array for holding escaped (non-modified) strings
	 */
	private $escapedStrings = array();

	/**
	 * Static instance
	 */
	private static $instance;

	/**
	 * Will run once only, since private and called only by instance()
	 */
	private function __construct() {

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

	/**
	 * is_lib_loaded
	 * 
	 * @return Boolean - true if library is loaded
	 */
	public function is_lib_loaded(){
		return $this->lib_loaded;
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