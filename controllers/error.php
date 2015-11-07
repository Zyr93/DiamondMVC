<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ControllerError extends Controller {
	
	public function __construct( $db = null ) {
		parent::__construct('error', $db);
	}
	
	protected function action_main( ) {
		$this->title = 'Error!';
		$lang = i18n::load('diamondmvc');
		
		$errors = array();
		if( isset($_SESSION['errors']) ) {
			$errors = $_SESSION['errors'];
			unset($_SESSION['errors']);
		}
		else if( isset($_SESSION['error']) ) {
			$errors[] = $_SESSION['error'];
			unset($_SESSION['error']);
		}
		else if( isset($_REQUEST['msg']) ) {
			$error = array();
			$error['title'] = isset($_REQUEST['title']) ? htmlspecialchars(urldecode($_REQUEST['title'])) : $lang->get('GENERIC_ERROR');
			$error['msg']   = htmlspecialchars(urldecode($_REQUEST['msg'])); // Prevent XSS attacks on our beloved clients
			$error['level'] = isset($_REQUEST['level']) ? $_REQUEST['level'] : 'warn'; // The addMessage method automatically sanitizes this
			$errors[] = $error;
		}
		
		foreach( $errors as $error ) {
			$this->addMessage($error['title'], $error['msg'], $error['level']);
		}
	}
	
}
