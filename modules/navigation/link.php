<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ModuleNavigationLink extends ModuleNavigationItem {
	
	/**
	 * URL des Links.
	 * @var string
	 */
	protected $url = DIAMONDMVC_URL;
	
	/**
	 * Titel des Links, der bei einem Mouseover angezeigt wird.
	 * @var string
	 */
	protected $title = '';
	
	
	/**
	 * Erzeugt einen neuen verlinkten Navigationseintrag.
	 * @param string $display Anzeigetext des Eintrags.
	 * @param string $url     Link des Eintrags.
	 */
	public function __construct( $parent, $display, $url, $title = '' ) {
		parent::__construct($parent, $display);
		$this->url = $url;
		$this->title = $title;
	}
	
	public function title( $title = '' ) {
		if( !empty($title) ) {
			$this->title = $title;
			return $this;
		}
		return $this->title;
	}
	
	public function __toString( ) {
		$result = '<li class="module-navigation-item module-navigation-link item link"><a href="' . $this->url . '"';
		if( !empty($this->title) ) {
			$result .= ' title="' . $this->title . '"';
		}
		$result .= '>' . $this->display . '</a></li>';
		return $result;
	}
	
}
