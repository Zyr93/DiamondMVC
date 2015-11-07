<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ModuleNavigationSeparator extends ModuleNavigationItem {
	
	public function __construct( $parent ) {
		parent::__construct($parent, '');
	}
	
	public function __toString( ) {
		return '<li class="divider"></li>';
	}
	
}
