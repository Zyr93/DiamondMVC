<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Registers non-standard field classes with the form builder.
 * NOTE: The form related classes require a total revamp. They were custom tailored
 * for a particular website project and thus might be too specific for a DiamondMVC
 * core component.
 */
defined('DIAMONDMVC') or die();

class PluginFields extends Plugin {
	
	public function __construct( $db = null ) {
		parent::__construct('fields', $db);
		DiamondMVC::instance()->on('formbuilder::register-fields', $this);
	}
	
	
	public function handle( $evt ) {
		// This is where custom fields would be registered.
	}
	
}
