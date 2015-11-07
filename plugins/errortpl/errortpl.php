<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Switches the website template for the error controller.
 */
defined('DIAMONDMVC') or die();

class PluginErrorTpl extends Plugin {
	
	public function __construct( $db = null ) {
		parent::__construct('errortpl', $db);
		DiamondMVC::instance()->on('system::action', $this);
	}
	
	
	public function handle( $evt ) {
		if( $evt->controller instanceof ControllerError ) {
			$evt->template = 'error';
		}
	}
	
}
