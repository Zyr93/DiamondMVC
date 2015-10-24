<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
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
		
		$dir = opendir(DIAMONDMVC_ROOT . DS . 'unittest');
		// TODO
	}
	
	protected function action_temporary( ) {
		if( !Config::main()->get('DEBUG_MODE') ) {
			return $this->redirect();
		}
		
		$this->result = $this->getIndexedFileName(DIAMONDMVC_ROOT . '/foo/bar.bitch', 12);
	}
	
	protected function redirect( ) {
		redirect(DIAMONDMVC_URL . '/error?status=403&msg=' . urlencode('Site not in debug mode. Restricted access to the unit tests.'));
	}
	
}
