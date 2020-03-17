<?php
/**
 * WPSCMin minifier - Minify
 * 
 * use mrclay\Minify
 */

namespace WPSCMin\Minifier;

use \ReflectionClass;
use \Minify_HTML;

class Minify implements MinifierInterface
{

	/**
	 * 
	 * @var String classname of JS minifier used (JShrink | JSPlus)
	 */
	private $js_minifier;

	/**
	 * @var Array for holding escaped (non-modified) strings
	 */
	private $escapedStrings = array();

	/**
	 * minify
	 * 
	 * Minifies string referenced by $html
	 * 
	 * @param String & $html
	 * @return void ($html string modified by reference)
	 */
	public function minify( & $html ) {
		
		/**
		 * Add min/lib to include_path for CSS.php to be able to find components
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
	 * in $this->minify() to capture [minify-skip] tag
	 */
	private function minify_skip_capture( $matches ) {
		$placeholder = 'X_WPSCMin_escaped_string_' . count( $this->escapedStrings );
		$this->escapedStrings[$placeholder] = $matches[1];
		return $placeholder;
	}
}