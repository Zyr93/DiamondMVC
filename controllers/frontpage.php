<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

class ControllerFrontpage extends Controller {
	
	public function __construct( $db = null ) {
		parent::__construct('frontpage', $db);
	}
	
}
