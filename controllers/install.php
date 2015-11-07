<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Installation procedure. After installation the controller removes itself.
 */
defined('DIAMONDMVC') or die();

class ControllerIndex extends Controller {
	
	public function __construct( $db = null ) {
		parent::__construct('index', $db);
	}
	
	protected function action_main( ) {
		
	}
	
	protected function action_finish( ) {
		unlink(DIAMONDMVC_ROOT . '/controllers/install.php');
		unlink(DIAMONDMVC_ROOT . '/models/install.php');
		rmdirs(DIAMONDMVC_ROOT . '/views/install');
	}
	
	protected function addLeftSidebarModule( $module ) {
		if( !isset($this->modules['sidebar-left']) ) {
			$this->modules['sidebar-left'] = array();
		}
		$this->modules['sidebar-left'][] = $module;
		return $this;
	}
	protected function addRightSidebarModule( $module ) {
		if( !isset($this->modules['sidebar-right']) ) {
			$this->modules['sidebar-right'] = array();
		}
		$this->modules['sidebar-right'] = array();
		return $this;
	}
	
}
