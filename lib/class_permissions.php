<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Permissions system for DiamondMVC similar to TeamSpeak3's permissions system.
 */
defined('DIAMONDMVC') or die();

final class Permissions {
	
	/**
	 * Database interface object to use to read, create and alter permissions.
	 * @var Database
	 */
	static private $db = null;
	
	
	private function __construct( ) {}
	
	
	/**
	 * Initiates the permissions system singleton.
	 * @param  Database $db DBO to use to read, create and alter permissions.
	 */
	static public function init( $db ) {
		self::$db = $db;
	}
	
	/**
	 * Registers a new permission with the system directly in the database.
	 * @param  Permission $perm
	 * @return boolean    Whether the permission was successfully added.
	 */
	static public function register( $perm ) {
		$db = &self::$db;
		
		$db->pushState()->select('sys_perms')->fields('name, display, description, type')->append($perm->name(), $perm->display(), $perm->description(), $perm->type());
		$result = $db->found();
		$db->popState();
		
		if( !$result ) {
			logMsg('Permissions: failed to register a new permission', 3, 5);
		}
		
		return $result;
	}
	
	/**
	 * Removes an existing permission from the system, i.e. directly from the database.
	 * @param  string  $name Unique name of the permission.
	 * @return boolean       Whether the permission was successfully removed.
	 */
	static public function remove( $name ) {
		self::$db->pushState()->bind('s', $name)->query('DELETE FROM sys_perms WHERE name=?');
		$result = self::$db->found();
		self::$db->popState();
		
		return $result;
	}
	
	/**
	 * Checks whether the given user ID has the requested permission.
	 * @param  integer $userid Optional. Unique ID of the user to check. If omitted simply tests the current user.
	 * @param  string  $name   Name of the requested permission.
	 * @param  integer $level  Minimum required permission grant.
	 * @return boolean         Whether the user actually has the permission.
	 */
	static public function has( $userid, $name = '', $level = 1 ) {
		$db = &self::$db;
		
		if( is_string($userid) ) {
			$name   = $userid;
			$userid = DiamondMVC::instance()->getCurrentUser()->getUID();
		}
		
		$val = self::get($userid, 'sys_admin');
		if( $val ) {
			return true;
		}
		
		$val = self::get($userid, $name);
		return $val >= $level;
	}
	
	/**
	 * Gets the permission level of the indicated user.
	 * @param  integer $userid Unique ID of the user to check for. If omitted defaults to the current user.
	 * @param  string  $name   of the permission to check for.
	 * @return integer         Permission level of the associated user.
	 */
	static public function get( $userid, $name = '' ) {
		$db = &self::$db;
		
		if( is_string($userid) ) {
			$name   = $userid;
			$userid = DiamondMVC::instance()->getCurrentUser()->getUID();
		}
		
		$result = 0;
		
		// Check if the user has the requested permission set directly.
		$db->pushState()->select('sys_user_perms')->fields('level')
						->join('sys_perms')->on('permid=sys_perms.uid')->ignoreDeleted()->ignoreHidden()->back()
						->filter('userid=? and sys_perms.name=?')->bind('is', $userid, $name)
						->ignoreDeleted()->ignoreHidden()
						->seek();
		if( $db->found() ) {
			$model = (new ModelRow($db, '', '', ''))->read($db->getData());
			$db->popState();
			return intval($model->get('level'));
		}
		$db->popState();
		
		// Check if the user inherits the requested permission through one of their groups.
		$db->pushState()->select('sys_group_perms')->fields('level')->order('level desc')->limit(1)
						->join('sys_perms')->on('permid=sys_perms.uid')->ignoreDeleted()->ignoreHidden()
						->more('user_groups')->on('user_groups.groupid=sys_group_perms.groupid')->ignoreDeleted()->ignoreHidden()->back()
						->ignoreDeleted()->ignoreHidden()
						->filter('user_groups.userid=? and sys_perms.name=?')->bind('is', $userid, $name)
						->seek();
		if( $db->found() ) {
			$model = (new ModelRow($db, '', '', ''))->read($db->getData());
			$db->popState();
			return intval($model->get('level'));
		}
		$db->popState();
		
		return 0;
	}
	
	/**
	 * Sets the permission level for the associated user.
	 * @param  integer $userid Unique ID of the user to set the permission level for. If omitted defaults to the current user.
	 * @param  string  $name   of the permission to check for
	 * @param  integer $level  Permission level to set
	 * @return boolean         True if the permission was successfully set, otherwise false. False is also returned if a database error occurred or the requested permission does not exist.
	 */
	static public function set( $userid, $name = '', $level = 0 ) {
		$db = &self::$db;
		
		if( is_string($userid) ) {
			$level  = $name;
			$name   = $userid;
			$userid = DiamondMVC::instance()->getCurrentUser()->getUID();
		}
		
		// Get the permission ID for easier access
		$db->pushState()->select('sys_perms')->fields('uid')
						->filter('name=?')->bind('s', $name)
						->ignoreDeleted()->ignoreHidden()
						->seek();
		if( !$db->found() ) {
			$db->popState();
			logMsg("Permissions: Permission $name not found", 9, 5);
			return false;
		}
		
		$model  = (new ModelRow($db, '', '', ''))->read($db->getData());
		$permid = intval($model->get('uid'));
		$db->popState();
		
		// Check if the user already has this permission granted. If so, we'll simply update it.
		$db->pushState()->select('sys_user_perms')->fields('level')->fielddef('i')
						->filter('userid=? and permid=?')->bind('is', $userid, $permid)
						->ignoreDeleted()->ignoreHidden()
						->seek();
		if( $db->found() ) {
			$db->replace($level);
			if( !$db->found() ) {
				logMsg('Permissions: Failed to update user permission ' . $name . ' for user with ID ' . $userid, 9, 5);
				$db->popState();
				return false;
			}
			$db->popState();
			return true;
		}
		$db->popState();
		
		// Otherwise we'll create a new data set for it.
		$db->pushState()->select('sys_user_perms')->fields('permid, userid, level')->fielddef('iii')
						->ignoreDeleted()->ignoreHidden()
						->append($permid, $userid, $level);
		if( !$db->found() ) {
			logMsg('Permissions: Failed to grant user permission ' . $name . ' to user with ID ' . $userid, 9, 5);
			$db->popState();
			return false;
		}
		$db->popState();
		return true;
	}
	
	/**
	 * Gets the permission level for the given group. Used mainly for administration.
	 * @param  integer $groupid UID of the group
	 * @param  string  $name    Unique permission name to get the granted level for
	 * @return integer          Granted permission level
	 */
	static public function getForGroup( $groupid, $name ) {
		$db = &self::$db;
		
		$result = 0;
		
		$db->pushState()->select('sys_group_perms')->fields('level')
						->join('sys_perms')->on('permid=sys_perms.uid')->ignoreDeleted()->ignoreHidden()->back()
						->filter('groupid=? and sys_perms.name=?')->bind('is', $groupid, $name)
						->ignoreDeleted()->ignoreHidden()
						->seek();
		if( $db->found() ) {
			$model  = (new ModelRow($db, '', '', ''))->read($db->getData());
			$result = intval($model->get('level'));
		}
		$db->popState();
		
		return $result;
	}
	
	/**
	 * Sets the permission level for the given group. Used mainly for administration.
	 * @param  integer $groupid Unique ID of the group
	 * @param  string  $name    Unique permission name
	 * @param  integer $level   Permission level to set
	 * @return boolean          True if the permission was successfully set, otherwise false. Also returns false if the permission does not exist or a database error occurred.
	 */
	static public function setForGroup( $groupid, $name, $level ) {
		$db = &self::$db;
		
		// Get the permission's UID for easy use
		$db->pushState()->select('sys_perms')->fields('uid')->filter('name=?')->bind('s', $name)
						->ignoreDeleted()->ignoreHidden()
						->seek();
		if( !$db->found() ) {
			$db->popState();
			logMsg("Permissions: Permission $name not found", 9, 5);
			return false;
		}
		$model  = (new ModelRow($db, '', '', ''))->read($db->getData());
		$permid = intval($model->get('uid'));
		
		// If the group already has the permission, we'll simply update it.
		$db->pushState()->select('sys_group_perms')->fields('level')->fielddef('i')
						->filter('groupid=? and permid=?')->bind('ii', $groupid, $permid)
						->ignoreDeleted()->ignoreHidden()
						->seek();
		if( $db->found() ) {
			$db->replace($level);
			if( !$db->found() ) {
				$db->popState();
				logMsg("Permissions: Failed to update permission $name for group with ID $groupid", 9, 5);
				return false;
			}
			$db->popState();
			return true;
		}
		$db->popState();
		
		// Otherwise we'll create a new data set for it.
		$db->pushState()->select('sys_group_perms')->fields('groupid, permid, level')->fielddef('iii')
						->ignoreDeleted()->ignoreHidden()
						->append($groupid, $permid, $level);
		if( !$db->found() ) {
			$db->popState();
			logMsg("Permissions: Failed to grant group permission $name to group with ID $groupid", 9, 5);
			return false;
		}
		$db->popState();
		return true;
	}
	
}
