<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die;

class ControllerDocs extends Controller {
	
	public function __construct( $db = null ) {
		parent::__construct('docs', $db);
	}
	
	public function action_main( ) {}
	public function action_getting_started( ) {}
	public function action_controllers( ) {}
	public function action_views( ) {}
	public function action_models( ) {}
	public function action_plugins( ) {}
	public function action_i18n( ) {}
	public function action_permissions( ) {}
	public function action_extensions( ) {}
	
}
