<?php
/**
 * Preserve Canonical
 * 
 * HtmlMin DomObserver used to keep full href on <link rel=canonical>
 */

use voku\helper\HtmlMinDomObserverInterface;
use voku\helper\SimpleHtmlDomInterface;
use voku\helper\HtmlMinInterface;

final class PreserveCanonical implements HtmlMinDomObserverInterface
{
	/**
	 * @var String
	 */
	private $canonicalHref;

	private function match(SimpleHtmlDomInterface $element){
		return ( $element->tag === 'link'
			&&
			$element->hasAttribute('rel')
			&&
			$element->getAttribute('rel') === 'canonical'
		);
	}


    /**
     * Receive dom elements before the minification.
     *
     * @param SimpleHtmlDomInterface $element
     * @param HtmlMinInterface       $htmlMin
     *
     * @return void
     */
    public function domElementBeforeMinification(SimpleHtmlDomInterface $element, HtmlMinInterface $htmlMin){
		if ( $this->match($element) ){
			$this->canonicalHref = $element->getAttribute('href');
		}
	}

    /**
     * Receive dom elements after the minification.
     *
     * @param SimpleHtmlDomInterface $element
     * @param HtmlMinInterface       $htmlMin
     *
     * @return void
     */
    public function domElementAfterMinification(SimpleHtmlDomInterface $element, HtmlMinInterface $htmlMin){
		// do nothing
		if ( $this->match($element) ){
			$element->setAttribute('href', $this->canonicalHref);

			$attributes = '';
			$elementAttributes = $element->getAllAttributes();

			/**
			* @var string $attributeName
			* @var string $attributeValue
			*/
			foreach ($elementAttributes as $attributeName => $attributeValue) {
				$attributes .= $attributeName . '="' . $attributeValue . '"';                
			}
			$element->outerhtml = '<link ' . $attributes . '>';
		}
	}
}