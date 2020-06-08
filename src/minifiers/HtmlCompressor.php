<?php
/**
 * WPSCMin minifier - HtmlCompressor
 * 
 * use wyrihaximus\HtmlCompressor
 * 
 * @since 1.1
 */

namespace WPSCMin\Minifier;

use PreserveCanonical;
use voku\helper\HtmlMin;
use WyriHaximus\HtmlCompress\Factory;

class HtmlCompressor implements MinifierInterface
{
	/**
	 * minify with WyriHaximus\HtmlCompress\HtmlCompressor
	 * 
	 * @since 1.1
	 */
	function minify( & $html ){
		$htmlMin = new HtmlMin();

		$htmlMin->doRemoveHttpPrefixFromAttributes();
		$htmlMin->doRemoveHttpsPrefixFromAttributes();
		$htmlMin->doMakeSameDomainsLinksRelative( [ $_SERVER['SERVER_NAME'] ] );

		$htmlMin->attachObserverToTheDomLoop(new PreserveCanonical);


		$parser = Factory::construct()->withHtmlMin($htmlMin);
		// $parser = Factory::constructSmallest(false);

		$html = $parser->compress($html);

	}
}