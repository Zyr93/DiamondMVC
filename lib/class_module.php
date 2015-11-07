<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * A module is a specialized snippet used for displaying various information in the different
 * positions of a template, for example a sidebar. An example for a module are the navigation
 * or login modules.
 */
defined('DIAMONDMVC') or die();

class Module extends Snippet {
	
	/**
	 * Constructing controller
	 * @var Controller
	 */
	protected $controller = null;
	
	/**
	 * Name / type of the module
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * Module template
	 * @var string
	 */
	protected $tpl = 'default';
	
	
	/**
	 * Creates a new module.
	 * @param Controller $controller Controller which constructed the module. Usually unused. Potential subject to removal.
	 * @param string     $name       Name of the module
	 * @param string     $tpl        Module template
	 */
	public function __construct( $controller, $name, $tpl = 'default' ) {
		parent::__construct('');
		
		$this->controller = $controller;
		$this->name = $name;
		$this->tpl  = $tpl;
	}
	
	
	/**
	 * Reads the HTML contents of the module
	 * @return Module This instance to enable method chaining.
	 */
	public function read( ) {
		$this->file = DIAMONDMVC_ROOT . '/modules/' . $this->name . '/templates/' . $this->tpl . '.php';
		return parent::read();
	}
	
	/**
	 * Gets the template the module was constructed with.
	 * @return string
	 */
	public function getTemplate( ) {
		return $this->tpl;
	}
	
	/**
	 * Gets the name of the module it was constructed with.
	 * @return string
	 */
	public function getName( ) {
		return $this->name;
	}
	
	public function getBaseUrl( ) {
		return DIAMONDMVC_URL . "/modules/{$this->name}";
	}
	
}
