<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

final class Permission {
	
	/**
	 * Unique ID of the permission as it is registered in the permissions system.
	 * @var integer
	 */
	private $uid = 0;
	
	/**
	 * Unique name of the permission.
	 * @var string
	 */
	private $name = '';
	
	/**
	 * Human readable name of the permission.
	 * @var string
	 */
	private $display = '';
	
	/**
	 * Human readable description of the permission.
	 * @var string
	 */
	private $description = '';
	
	/**
	 * Type of the permission.
	 * 0 = value
	 * 1 = range
	 * 2 = checkbox
	 * @var integer
	 */
	private $type = 0;
	
	
	const TYPE_VALUE = 0;
	const TYPE_RANGE = 1;
	const TYPE_BOOL  = 2;
	
	
	/**
	 * Constructs a single permission.
	 * @param integer $uid     Unique ID of the permission
	 * @param string  $name    
	 * @param integer $type    
	 * @param integer $default value
	 */
	public function __construct( $uid ) {
		$this->uid = $uid;
	}
	
	/**
	 * Gets the UID of the current permission.
	 * @return integer
	 */
	public function getUid( ) {
		return $this->uid;
	}
	
	/**
	 * Gets or sets the name of this permission.
	 * @param  string|boolean    $name Optional. New name to set. Omit to get the current value.
	 * @return Permission|string       If used as a getter, returns the current name. If used as a setter, returns this instance to enable method chaining.
	 */
	public function name( $name = false ) {
		if( $name === false ) {
			return $this->name;
		}
		$this->name = $name;
		return $this;
	}
	
	/**
	 * Gets or sets the human readable name of this permission.
	 * @param  string|boolean    $display Optional. New human readable name. Omit to get the current value.
	 * @return Permission|string          If used as a getter, returns the current name. If used as a setter, returns this instance to enable method chaining.
	 */
	public function display( $display = false ) {
		if( $display === false ) {
			return $this->display;
		}
		$this->display = $display;
		return $this;
	}
	
	/**
	 * Gets or sets the human readable description of this permission.
	 * @param  string|boolean    $desc Optional. New human readable description of this permission. Omit to get the current value.
	 * @return Permission|string       If used as a getter, returns the current name. If used as a setter, returns this instance to enable method chaining.
	 */
	public function description( $desc = false ) {
		if( $desc === false ) {
			return $this->description;
		}
		$this->description = $desc;
		return $this;
	}
	
	/**
	 * Gets or sets the type of this permission.
	 * @param  string|boolean    $type Optional. New type of this permission. Omit to get the current value.
	 * @return Permission|string       If used as a getter, returns the current name. If used as a setter, returns this instance to enable method chaining.
	 */
	public function type( $type = false ) {
		if( $type === false ) {
			return $this->type;
		}
		$this->type = $type;
		return $this;
	}
	
}
