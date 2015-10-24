<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

final class Cache {
	
	/**
	 * DBO to read and write information.
	 * @var Database
	 */
	static private $db = null;
	
	/**
	 * Initiates the singleton instance.
	 * @param  Database $db Database interface object to use to cache information.
	 */
	static public function init( Database $db ) {
		self::$db = $db;
	}
	
	/**
	 * Gets a value stored in the shared cache.
	 * @param  string $id      Cache key
	 * @param  mixed  $default value to return if the key does not exist or is expired.
	 * @return mixed           Cached value or the default value
	 */
	static public function get( $id, $default = null ) {
		$db = &self::$db;
		
		$db->pushState()->select('sys_cache')->fields('name, value, expires')
		   ->filter('name=?')->bind('s', $id)
		   ->ignoreDeleted()->ignoreHidden()
		   ->seek();
		if( !$db->found() ) {
			$result = $default;
		}
		else {
			$data    = $db->getData();
			$model   = (new ModelRow($db, '', '', ''))->read($data);
			$expires = intval($model->get('expires'));
			
			if( $expires < round(microtime(true)) ) {
				self::clear($id);
				$result = $default;
			}
			else {
				$result = $model->get('value');
			}
		}
		$db->popState();
		
		return $result;
	}
	
	/**
	 * Stores a value in the shared cache.
	 * @param string  $id       Cache key
	 * @param mixed   $value    Any serializable value
	 * @param integer $lifetime Optional. The default value is read from the config file. Determines how long the value will be stored in cache.
	 */
	static public function set( $id, $value, $lifetime = 0 ) {
		self::clear($id);
		
		if( $lifetime <= 0 ) {
			$lifetime = Config::main()->get('DEFAULT_CACHE_LIFETIME');
		}
		
		$db = &self::$db;
		
		$db->pushState()->select('sys_cache')->fields('name, value, expires')->fielddef('ssi')->append($id, $value, round(microtime(true) + $lifetime));
		if( !$db->found() ) {
			logMsg("Cache: failed to store $id in cache with value $value", 2, 5);
		}
		$db->popState();
	}
	
	/**
	 * Clears a key stored in shared cache.
	 * @param string $id Cache key
	 */
	static public function clear( $id ) {
		self::$db->pushState()->select('sys_cache')->filter('name=?')->bind('s', $id)
		         ->ignoreDeleted()->ignoreHidden()
		         ->realDelete()->popState();
	}
	
	/**
	 * Refreshes the cache entry with the given name by the provided life time.
	 * Note: the life time starts from the time of the call to this method.
	 * @param  string  $id       Unique of this cache entry
	 * @param  integer $lifetime New lifetime of this cache entry
	 * @return boolean           Whether the cache entry could be updated.
	 */
	static public function refresh( $id, $lifetime = 0 ) {
		if( $lifetime <= 0 ) {
			$lifetime = intval(Config::main()->get('DEFAULT_CACHE_LIFETIME'));
		}
		
		self::$db->pushState()->select('sys_cache')->fields('expires')->filter('name=?')->bind('s', $id)->replace(round(microtime(true) + $lifetime));
		$result = self::$db->found();
		self::$db->popState();
		
		if( !$result ) {
			logMsg("Cache: failed to refresh cache entry $id", 2, 5);
		}
		return $result;
	}
	
	/**
	 * Purges the cache from expired entries.
	 */
	static public function purge( ) {
		self::$db->pushState()->select('sys_cache')->filter('expires <= ?')->bind('i', round(microtime(true)))
				 ->ignoreDeleted()->ignoreHidden()
		         ->realDelete()->popState();
	}
	
	/**
	 * Resets/empties the entire cache.
	 */
	static public function reset( ) {
		self::$db->pushState()->select('sys_cache')->realTruncate()->popState();
	}
	
	/**
	 * Gets a value stored in the local cache. Its values cannot be accessed between
	 * sessions and/or instances.
	 * @param  string $id      Cache key
	 * @param  mixed  $default value to return if the key does not exist or is expired.
	 * @return mixed           Cached value or the default value
	 */
	static public function getLocal( $id, $default = null ) {
		if( !isset($_SESSION['__CACHE']['__VALUES'][$id]) ) {
			return $default;
		}
		
		if( intval($_SESSION['__CACHE']['__EXPIRES'][$id]) < round(microtime(true)) ) {
			self::clearLocal($id);
			return $default;
		}
		
		return $_SESSION['__CACHE']['__VALUES'][$id];
	}
	
	/**
	 * Stores a value in the local cache. Its values cannot be accessed between
	 * sessions and/or instances.
	 * @param string  $id       Cache key
	 * @param mixed   $value    Any serializable value
	 * @param integer $lifetime Optional. The default value is read from the config file. Determines how long the value will be stored in cache
	 */
	static public function setLocal( $id, $value, $lifetime = 0 ) {
		if( $lifetime <= 0 ) {
			$lifetime = intval(Config::main()->get('DEFAULT_CACHE_LIFETIME'));
		}
		
		$_SESSION['__CACHE']['__VALUES'][$id] = $value;
		$_SESSION['__CACHE']['__EXPIRES'][$id] = round(microtime(true) + $lifetime);
	}
	
	/**
	 * Clears a key stored in local cache.
	 * @param string $id Cache key
	 */
	static public function clearLocal( $id ) {
		unset($_SESSION['__CACHE']['__VALUES'][$id]);
		unset($_SESSION['__CACHE']['__EXPIRES'][$id]);
	}
	
	/**
	 * Refreshes the given local cache entry by the provided lifetime.
	 * Note: the life time starts from the time of the call to this method.
	 * @param  string  $id       Name of the cache entry.
	 * @param  integer $lifetime New lifetime of this cache entry.
	 * @return boolean           Whether the cache entry was successfully updated.
	 */
	static public function refreshLocal( $id, $lifetime = 0 ) {
		if( !isset($_SESSION['__CACHE']['__VALUES'][$id]) ) {
			return false;
		}
		
		if( $lifetime <= 0 ) {
			$lifetime = Config::main()->get('DEFAULT_CACHE_LIFETIME');
		}
		
		$_SESSION['__CACHE']['__EXPIRES'][$id] = round(microtime(true) + $lifetime);
		return true;
	}
	
}
