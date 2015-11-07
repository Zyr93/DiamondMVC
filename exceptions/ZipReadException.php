<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ZipReadException extends ZipException {
	
	public function __construct( $zip, $code = 0, $prev = null ) {
		parent::__construct("Failed to read entries of ZIP at $zip. See error code for more information.", $code, $prev);
	}
	
}
