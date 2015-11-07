<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * @param View $this
 * 
 * A simple utility class to simplify the construction of a render event and
 * standardize this kind of events.
 */
defined('DIAMONDMVC') or die();

class SystemActionEvent extends Event {
	
	/**
	 * Controller whose action is performed. Changes to this field are reflected in the
	 * run procedure.
	 * @var Controller
	 */
	public $controller;
	/**
	 * User requested action to be performed. Changes to this field are reflected in the
	 * run procedure.
	 * @var string
	 */
	public $action;
	/**
	 * User requested view for the action. Optional. Changes to this field are reflected in
	 * the run procedure.
	 * @var string
	 */
	public $view;
	
	/**
	 * User requested template. Optional. Changes to this field are reflected in the run
	 * procedure.
	 * @var string
	 */
	public $template;
	
	public function __construct( $controller, $action, $view = '', $tpl = '' ) {
		parent::__construct('system::action');
		$this->controller = $controller;
		$this->action     = $action;
		$this->view       = $view;
		$this->template   = $tpl;
	}
	
}
