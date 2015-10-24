<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

function autoloadEvents( $class ) {
	if( strToLower(right($class, 5)) !== 'event' ) {
		return;
	}
	
	require_once(jailpath(DIAMONDMVC_ROOT . '/classes/events', strToLower(substr($class, 0, strlen($class) - 5)) . '.php'));
}

AutoloadRegistry::instance()->registerAutloader('autoloadEvents');
