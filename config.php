<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */

require_once(DIAMONDMVC_ROOT . '/lib/class_ini.php');

class Config extends ini {
	
	/**
	 * Source file from which the configuration was read.
	 * @var string
	 */
	private $source = '';
	
	/**
	 * Database interface object which provides a connection to the configured database.
	 * @var database
	 */
	private $db = null;
	
	/**
	 * Main instance of the configuration.
	 * @var config
	 */
	static private $instance = null;
	
	
	public function __construct( $inifile = '' ) {
		if( empty($inifile) ) {
			$inifile = dirname(__FILE__) . '/ini.php';
		}
		
		$this->source = $inifile;
		$this->read($inifile);
		
		// Initiate database. If not possible an exception is thrown.
		$this->getDBO();
	}
	
	/**
	 * Get the main configuration.
	 * @return Config
	 */
	static public function main( ) {
		if( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Clears the current configuration contents and reloads the file from disk.
	 * @return Config This instance to enable method chaining.
	 */
	public function reload( ) {
		$this->data = array();
		$this->db   = null;
		$this->read($this->source);
		$this->getDBO();
		return $this;
	}
	
	
	/**
	 * Checks whether the server settings DEBUG_MODE flag is set.
	 * @return boolean
	 */
	public function isDebugMode( ) {
		return $this->get('DEBUG_MODE');
	}
	
	
	/**
	 * Gets the database interface object which can be used to query the database.
	 * @return database
	 */
	public function getDBO( ) {
		if( $this->db !== null ) {
			return $this->db;
		}
		
		$iniDatabase = $this->def('DATABASE', 'DEFAULT');
		$category    = 'DATABASE.' . $iniDatabase;
		$host        = $this->def('HOST',   $category, '127.0.0.1');
		$port        = $this->def('PORT',   $category, 3306);
		$user        = $this->def('USER',   $category, 'diamondmvc');
		$pass        = $this->def('PASS',   $category, '');
		$database    = $this->def('DB',     $category, 'diamondmvc');
		$prefix      = $this->def('PREFIX', $category, '');
		
		$this->db = (new database($user, $pass, $host, $port, $prefix))->useDB($database);
		
		return $this->db;
	}
	
}
