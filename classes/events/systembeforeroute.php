<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * @param View $this
 * 
 * Triggered before routing the request parameters to controller, action, view, template, etc.
 * If the default is prevented, routing is skipped.
 */
defined('DIAMONDMVC') or die();

class SystemBeforeRouteEvent extends Event {
	
	public function __construct( ) {
		parent::__construct('system::before-route');
	}
	
}
