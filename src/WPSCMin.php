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
	 * Whether HtmlMin library has been loaded
	 */
	protected $loaded_html_min = false;

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
		if ( class_exists( 'voku\helper\HtmlMin' ) ){
			$this->loaded_html_min = true;
		}else if ( class_exists( 'Minify_HTML' ) ){
			$this->loaded_minify = true;
		}

		/**
		 * library loaded
		 */
		$this->lib_loaded = (
			$this->loaded_html_min
			||
			$this->loaded_minify
		);

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
	 * static WPSCMin::minify_page() function
	 * Given string $html, returns minified version.
	 * Preserves HTML comments appended by WP Super Cache
	 * 
	 * @param String $html 
	 * @return String $html - minified HTML
	 */
	public static function minify_page( $html ) {
		
		/**
		 * If loading of Minify library failed - exit
		 */
		if ( ! self::instance()->lib_loaded )
			return $html;

		/**
		 * set minifier instance
		 */
		if ( self::instance()->loaded_html_min ){
			self::instance()->minifier = new WPSCMin\Minifier\HtmlMin();
		}else if ( self::instance()->loaded_minify ){
			self::instance()->minifier = new WPSCMin\Minifier\Minify();
		}

		/**
		 * Run non-static minify function (html sent by reference)
		 */
		self::instance()->minifier->minify( $html );
		return $html;
	}

	/**
	 * minify
	 * 
	 * Given string $html, returns minified version.
	 * 
	 * @param String $html
	 * @return Void $html is minified by reference
	 */
	public function minify( & $html ){

		/**
		 * $html passed by reference(!)
		 * dunno why? - probably because of how original WPSCMin mrclay/Minify was implemented
		 */
		$this->minifier->minify( $html );
	}

}