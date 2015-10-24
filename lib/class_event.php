<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * An event can be created and triggered via DiamondMVC by any component. Plugins can then
 * manipulate or react to the information stored in this object to customize the behavior
 * of algorithms.
 */
defined('DIAMONDMVC') or die();

DiamondMVC::instance()->loadLibrary('events');

class Event {
	
	private $namespace = '';
	private $name = '';
	
	private $isDefaultPrevented = false;
	
	private $stopPropagation = false;
	
	
	public function __construct( $name ) {
		if( ($index = strrpos($name, '::')) !== false ) {
			$this->namespace = substr($name, 0, $index);
			$this->name      = substr($name, $index + 2);
		}
		else {
			$this->name      = $name;
		}
	}
	
	public function getNamespace( ) {
		return $this->namespace;
	}
	
	public function getName( ) {
		return $this->name;
	}
	
	public function getFullName( ) {
		return "{$this->namespace}::{$this->name}";
	}
	
	public function preventDefault( ) {
		$this->isDefaultPrevented = true;
		return $this;
	}
	
	public function isDefaultPrevented( ) {
		return $this->isDefaultPrevented;
	}
	
	public function stopPropagation( ) {
		$this->stopPropagation = true;
		return $this;
	}
	
	public function isPropagationStopped( ) {
		return $this->stopPropagation;
	}
	
}
