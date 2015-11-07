<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ModuleNavigationTitle extends ModuleNavigationItem {
	
	public function __construct( $parent, $display ) {
		parent::__construct($parent, $display);
	}
	
	public function __toString( ) {
		return '<li>' . $this->display . '</li>';
	}
	
}
