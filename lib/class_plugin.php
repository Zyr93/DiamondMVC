<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * A plugin is a mostly independent piece of code run by the system at startup. It can
 * register with the DiamondMVC singleton to react to particular events in the system.
 * 
 * Plugins may have a priority which determines when it is called by the system in response
 * to a triggered event. The earlier a plugin is called, the less information will have been
 * previously manipulated.
 */
defined('DIAMONDMVC') or die();

abstract class Plugin {
	
	private $name = '';
	
	private $priority = -1;
	
	protected $db = null;
	
	
	protected function __construct( $name, $db = null ) {
		$this->name = $name;
		
		if( !$db ) {
			$db = Config::main()->getDbo();
		}
		$this->db = $db;
		
		// Read the plugin's priority from the database.
		$db->pushState()->select('sys_plugin_meta')->fields('priority')->filter('name=?')->bind('s', $name)->limit(1)->seek();
		if( $db->found() ) {
			$row = (new ModelRow($db, '', '', ''))->read($db->getData());
			$this->priority = intval($row->get('priority'));
		}
		else {
			logMsg('Failed to read plugin priority from the database for ' . $name . ' - assigning priority 99999', 9, 5);
			$this->priority = 99999;
		}
		$db->popState();
	}
	
	/**
	 * This method is called by the DiamondMVC in order to handle triggered events.
	 * @see Event
	 * @param string $event Event object providing additional information on the event, including name (and namespace).
	 */
	abstract public function handle( $event );
	
	public function getName( ) {
		return $this->name;
	}
	
	public function getPriority( ) {
		return $this->priority;
	}
	
	
	/**
	 * Reads all plugins in the plugins directory of the server root.
	 * @return array
	 */
	static public function getPlugins( $db = null ) {
		if( !$db ) {
			$db = Config::main()->getDBO();
		}
		
		$result = array();
		$path   = DIAMONDMVC_ROOT . '/plugins';
		$dir    = opendir($path);
		
		while( $curr = readdir($dir) ) {
			if( $curr === '.' or $curr === '..' ) continue;
			
			if( !is_dir("$path/$curr") ) {
				logMsg('Plugin ist kein Ordner: $path/$curr', 5);
				continue;
			}
			
			if( !file_exists("$path/$curr/$curr.php") ) {
				logMsg("Plugin-Hauptskript nicht gefunden: $path/$curr/$curr.php");
				continue;
			}
			
			include_once("$path/$curr/$curr.php");
			if( !class_exists("Plugin$curr") ) {
				logMsg("Plugin-Hauptklasse nicht gefunden: $curr");
				continue;
			}
			
			$className = "plugin$curr";
			$result[] = new $className($db);
		}
		
		return $result;
	}
	
}
