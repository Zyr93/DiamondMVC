<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

function autoloadFields( $class ) {
	if( strToLower(left($class, 5)) !== 'field' ) {
		return;
	}
	
	require_once(jailpath(DIAMONDMVC_ROOT . '/classes/fields', strToLower(substr($class, 5)) . '.php'));
}

AutoloadRegistry::instance()->registerAutloader('autoloadFields');
