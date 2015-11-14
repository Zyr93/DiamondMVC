<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ControllerTest extends Controller {
	
	public function __construct( $db = null ) {
		parent::__construct('test', $db);
	}
	
	protected function action_main( ) {
		if( !Config::main()->get('DEBUG_MODE') ) {
			return $this->redirect();
		}
		
		$result = array();
		
		$dir = opendir(DIAMONDMVC_ROOT . DS . 'unittest');
		while( ($item = readdir($dir)) ) {
			if( $item === '.' or $item === '..' ) continue;
			
			$result[] = $item;
		}
		
		$this->result = $result;
	}
	
	protected function action_unit( ) {
		if( !Config::main()->get('DEBUG_MODE') ) {
			return $this->redirect();
		}
		
		$result = array();
		
		foreach( $_REQUEST as $unit => $dummy ) {
			$path = DIAMONDMVC_ROOT . "/unittest/$unit/test.php";
			if( file_exists($path) ) {
				$result[] = $path;
			}
		}
		
		$this->result = $result;
	}
	
	protected function action_temporary( ) {
		if( !Config::main()->get('DEBUG_MODE') ) {
			return $this->redirect();
		}
	}
	
	protected function redirect( ) {
		redirect(DIAMONDMVC_URL . '/error?status=403&msg=' . urlencode('Site not in debug mode. Restricted access to the unit tests.'));
	}
	
}
