<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Handles automatic loading of unloaded classes. A fallback function
 * is provided for systems which do not natively support {@link spl_autoload_register()}.
 */
defined('DIAMONDMVC') or die();

final class AutoloadRegistry {
	
	static private $_instance = null;
	
	/**
	 * All registered Autoloader function names.
	 * @var array
	 */
	private $loaders = array();
	
	
	static public function instance( ) {
		if( self::$_instance === null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
	
	
	/**
	 * Registers a new autoloader function. Provides a fallback method if
	 * {@link spl_autoload_register()} is not available to this system.
	 * @param string $fnName Name of the function to call.
	 */
	public function registerAutloader( $fnName ) {
		if( function_exists('spl_autoload_register') ) {
			spl_autoload_register($fnName, true);
		}
		else {
			$loaders[] = $fnName;
		}
	}
	
	/**
	 * If the fallback method is used, this method invokes all registered autoloaders.
	 */
	public function invokeRegisteredAutoloaders( $class ) {
		foreach( $this->loaders as $loader ) {
			try {
				call_user_func($loader, $class);
			}
			catch( Exception $ex ) {
				logMsg('[CORE] Autoloader ' . (is_array($loader) ? $loader[1] : $loader) . ' threw an exception while loading class ' . $class);
			}
		}
	}
	
}


/**
 * Fallback autoloader. Immitates the behavior of spl_autoload_register.
 * Do not register this function itself!
 * @param string $class Class to load.
 */
function __autoload( $class ) {
	AutoloadRegistry::instance()->invokeRegisteredAutoloaders($class);
}

function mainAutoLoader( $class ) {
	// Exceptions werden leicht besonders behandelt.
	if( right($class, 9) === 'Exception' ) {
		$file = DIAMONDMVC_ROOT . "/exceptions/$class.php";
	}
	// Modelle, Kontroller und System-Klassen
	else {
		$class = strToLower($class);
		if( left($class, 10) === 'controller' and $class !== 'controller' ) {
			$class = substr($class, 10);
			$file = DIAMONDMVC_ROOT . "/controllers/$class.php";
		}
		else if( left($class, 5) === 'model' and $class !== 'model' ) {
			$class = substr($class, 5);
			$file = DIAMONDMVC_ROOT . "/models/$class.php";
		}
		else if( left($class, 6) === 'module' and $class !== 'module' ) {
			$class = substr($class, 6);
			$file = DIAMONDMVC_ROOT . "/modules/$class/$class.php";
		}
		else {
			$file = DIAMONDMVC_ROOT . "/lib/class_$class.php";
		}
	}
	
	if( file_exists($file) ) {
		include_once($file);
	}
}

AutoloadRegistry::instance()->registerAutloader('mainAutoLoader');
