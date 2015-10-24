<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * @param View $this
 * 
 * A simple utility class to simplify the construction of a render event and
 * standardize this kind of events.
 */
defined('DIAMONDMVC') or die();

class RenderEvent extends Event {
	
	protected $source  = '';
	
	public function __construct( $source ) {
		parent::__construct('render');
		$this->source  = $source;
	}
	
	public function getSource( ) {
		return $this->source;
	}
	
}
