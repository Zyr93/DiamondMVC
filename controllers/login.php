<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ControllerLogin extends Controller {
	
	public function __construct( ) {
		parent::__construct('login');
	}
	
	protected function action_main( ) {
		$this->action_login();
	}
	
	protected function action_login( ) {
		$user = DiamondMVC::instance()->getCurrentUser();
		
		if( $user->isLoggedIn() ) {
			if( isset($_REQUEST['returnto']) ) {
				redirect(urldecode($_REQUEST['returnto']));
			}
			else {
				redirect(DIAMONDMVC_URL . Config::main()->get('DEFAULT_LOGIN_REDIRECT'));
			}
			return;
		}
		
		// Wurden Daten übermittelt?
		if( isset($_REQUEST['login']) ) {
			if( $user->login($_REQUEST['username'], $_REQUEST['password']) ) {
				if( isset($_REQUEST['returnto']) ) {
					redirect(urldecode($_REQUEST['returnto']));
				}
				else {
					redirect(DIAMONDMVC_URL . Config::main()->get('DEFAULT_LOGIN_REDIRECT'));
				}
				return;
			}
			else {
				$this->addMessage('Error', 'The given email-password combination was not found.', 'error');
			}
		}
	}
	
	protected function action_logout( ) {
		session_destroy();
	}
	
}
