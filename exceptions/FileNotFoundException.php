<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

class FileNotFoundException extends BaseException {
	
	public function __construct( $file, $code = 0, $prev = null ) {
		parent::__construct('File Not Found Exception', "File $file not found", $code, $prev);
	}
	
}
