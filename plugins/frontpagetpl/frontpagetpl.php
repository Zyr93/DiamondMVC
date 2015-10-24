<?php
/**
 * @package DiamondMVC official website & demo package
 * @author  Zyr
 * @license Public Domain
 */
defined('DIAMONDMVC') or die;

class PluginFrontpageTpl extends Plugin {
	
	public function __construct( $db = null ) {
		parent::__construct('frontpagetpl', $db);
		DiamondMVC::instance()->on('system::action', $this);
	}
	
	public function handle( $evt ) {
		if( $evt->controller->getName() === 'frontpage' ) {
			$evt->template = 'frontpage';
		}
	}
	
}
