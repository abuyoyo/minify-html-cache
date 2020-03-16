<?php
/**
 * WPSCMin minifier - HtmlMin
 * 
 * use voku\HtmlMin
 */
namespace WPSCMin\Minifier;

use PreserveCanonical;

class HtmlMin implements MinifierInterface
{
	/**
	 * minify with voku\HtmlMin
	 */
	function minify( & $html ){
		
		$htmlMin = new \voku\helper\HtmlMin();

		$htmlMin->doRemoveHttpPrefixFromAttributes();
		$htmlMin->doRemoveHttpsPrefixFromAttributes();
		$htmlMin->doMakeSameDomainsLinksRelative( [ $_SERVER['SERVER_NAME'] ] );

		$htmlMin->attachObserverToTheDomLoop(new PreserveCanonical);

		$html = $htmlMin->minify($html);
	
	}
}