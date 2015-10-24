<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

class BaseException extends Exception {
	
	protected $prefix = 'Generic exception';
	
	
	public function __construct( $prefix, $message, $code = 0, Exception $prev = null ) {
		parent::__construct($message, $code, $prev);
		$this->prefix = $prefix;
	}
	
	public function __toString( ) {
		$result = $this->prefix;
		if( $this->code ) {
			$result .= " [{$this->code}]";
		}
		if( !empty($this->message) ) {
			$result .= ": " . $this->message;
		}
		return $result;
	}
	
}
