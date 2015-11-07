<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Validator for the extension upload mask.
 */
defined('DIAMONDMVC') or die();

class PluginUploadExtension extends Plugin {
	
	public function __construct( $db = null ) {
		parent::__construct('uploadextension', $db);
		DiamondMVC::instance()->on('upload', $this);
	}
	
	public function handle( $evt ) {
		// We only want to handle a very specific file upload with a very specific name.
		if( $evt->getProp() !== 'install-extension_file' ) {
			return;
		}
		
		// The installer can currently only process ZIP files anyway.
		$name = $evt->getName();
		if( !endsWith($name, '.zip') ) {
			$evt->preventDefault();
		}
	}
	
}
