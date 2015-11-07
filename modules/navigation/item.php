<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

/**
 * Ein Eintrag in der Navigation.
 */
abstract class ModuleNavigationItem {
	
	/**
	 * Elternobjekt, welches in {@link #back()} zurückgegeben wird.
	 * @var object
	 */
	protected $parent = null;
	
	/**
	 * Anzeige des Eintrags.
	 * @var string
	 */
	protected $display = '';
	
	
	public function __construct( $parent, $display ) {
		$this->parent  = $parent;
		$this->display = $display;
	}
	
	
	/**
	 * Gets the stylesheet URLs used by this item. URLs must be either relative to the
	 * website's root or absolute.
	 * @return array
	 */
	public function getStylesheets( ) {
		return array();
	}
	
	/**
	 * Gets the script IDs used by this menu item. IDs must be relative to the website's
	 * assets directory or absolute.
	 * @return array
	 */
	public function getScripts( ) {
		return array();
	}
	
	
	/**
	 * Liefert das Elternobjekt zurück, um per Methodenverkettung dessen Eigenschaften
	 * weiter zu bearbeiten.
	 * @return object Elternobjekt.
	 */
	public function back( ) {
		return $this->parent;
	}
	
	/**
	 * Setzt das Elternobjekt.
	 * @param  object               $parent Das Elternobjekt.
	 * @return ModuleNavigationItem         Diese Instanz zur Methodenverkettung.
	 */
	public function setParent( $parent ) {
		$this->parent = $parent;
		return $this;
	}
	
	abstract public function __toString( );
	
}
