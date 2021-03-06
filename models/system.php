<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * System administration controller. Manages all sorts of installations, including plugin priority.
 */
defined('DIAMONDMVC') or die();

class ModelSystem extends Model {
	
	public function __construct( $db ) {
		
	}
	
	public function read( $from = null ) {
		throw new UnsupportedOperationException('This model does not read generic data!');
	}
	
}
