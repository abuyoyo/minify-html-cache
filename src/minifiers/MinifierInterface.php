<?php
/**
 * WPSCMin Minifier Interface
 */
namespace WPSCMin\Minifier;

interface MinifierInterface{

	public function minify ( & $html );
}