<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

class ModelUser extends ModelRow {
	
	public function __construct( $db ) {
		parent::__construct($db, '', '', '');
	}
	
	/**
	 * Unsupported operation! Use {@link #login()} instead.
	 */
	public function read( $from = null ) {
		throw new UnsupportedOperationException();
	}
	
	/**
	 * Checks if a user is currently logged in.
	 * @return boolean True if so, otherwise false.
	 */
	public function isLoggedIn( ) {
		return isset($_SESSION['user']) and (!$this->isSessionExpired() or ($this->destroySession() and false));
	}
	
	/**
	 * Attempts to log in the user with the provided information.
	 * @param  string  $emauk Name of the user.
	 * @param  string  $pass  Password of the user.
	 * @return boolean        True if the user was successfully logged in, otherwise false.
	 */
	public function login( $email, $pass ) {
        $this->db->pushState()->select('users')
        		 ->filter('email=? AND password=?')->bind('ss', $email, hash('sha256', $pass)) // TODO: Salt?
        		 ->ignoreHidden()
        		 ->seek();
		$data = $this->db->getData();
		
		$this->db->popState();
		if( empty($data) ) {
			return false;
		}
		
		$result = array();
		foreach( $data as $key => $value ) {
			if( is_numeric($key) )
				continue;
			
			$result[strToLower($key)] = $value;
		}
		
		$this->data = $_SESSION['user'] = $result;
		$this->refreshSession();
		return true;
	}
	
	/**
	 * Restores the user from session.
	 * @return ModelAdmin This instance to enable method chaining.
	 */
	public function restoreSession( ) {
		$this->data = $_SESSION['user'];
		return $this;
	}
	
	/**
	 * Destroys the current user session.
	 * @return ModelAdmin This instance to enable method chaining.
	 */
	public function destroySession( ) {
		unset($_SESSION['user']);
		return $this;
	}
	
	/**
	 * Refreshes the expiration time of the current session.
	 * @return ModelAdmin This instance to enable method chaining.
	 */
	public function refreshSession( ) {
		$_SESSION['user']['last_action'] = round(microtime(true));
		return $this;
	}
	
	/**
	 * Checks if the current user has been inactive for too long (session expired).
	 * The session expiration time can be set in the configuration.
	 * @return boolean True if the user has been inactive for x seconds.
	 */
	public function isSessionExpired( ) {
		return round(microtime(true)) - intval($_SESSION['user']['last_action']) > intval(Config::main()->get('SESSION_TIMEOUT')) * 1000 * 60;
	}
	
	/**
	 * Gets the UID of the user if logged in, otherwise -1.
	 * @return integer UID of the user.
	 */
	public function getUid( ) {
		return $this->isLoggedIn() ? intval($this->data['uid']) : -1;
	}
	
	/**
	 * Gets the name of the currently logged in user or returns false if none
	 * is logged in.
	 * @return string|boolean Name of the user or false if none logged in.
	 */
	public function getName( ) {
		return $this->isLoggedIn() ? $this->data['username'] : false;
	}
	
}
