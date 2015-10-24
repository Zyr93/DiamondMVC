<?php
class Config {
	
	/**
	 * Associative array containing the settings of the server.
	 * @var array
	 */
	private $ini = null;
	
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
		
		$this->ini = readIni($inifile);
		
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
		
		$iniDatabase = $this->ini['DATABASE'];
		
		if( !isset($this->ini['DATABASE.' . $iniDatabase]) || empty($this->ini['DATABASE.' . $iniDatabase]) ) {
			throw new Exception("The chosen database configuration was not found");
		}
		
		$iniDbConfig = $this->ini['DATABASE.' . $iniDatabase];
		$this->db = (new database($iniDbConfig['USER'], $iniDbConfig['PASS'], $iniDbConfig['HOST'], $iniDbConfig['PORT']))
			->useDB($iniDbConfig['DB']);
		return $this->db;
	}
	
	/**
	 * Gets a configuration property. If the given key does not exist, an exception is thrown.
	 * @param  string $key      INI key
	 * @param  string $category INI category of the key. Optional.
	 * @return mixed            Value
	 */
	public function get( $key, $category = '' ) {
		if( !empty($category) and isset($this->ini[$category]) ) {
			return $this->ini[$category][$key];
		}
		return $this->ini[$key];
	}
	
}
