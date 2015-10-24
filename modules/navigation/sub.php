<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

class ModuleNavigationSub extends ModuleNavigationItem {
	
	/**
	 * Liste der Untermenüpunkte dieses Untermenüs.
	 * @var array
	 */
	protected $items = array();
	
	
	/**
	 * Erzeugt ein neues Untermenü.
	 * @param object $parent  Elternobjekt, welches in {@link #back()} zurückgeliefert wird.
	 * @param string $display Anzeigetext des Untermenüs.
	 */
	public function __construct( $parent, $display ) {
		parent::__construct($parent, $display);
	}
	
	
	/**
	 * Hängt einen Link an das Ende des Untermenüs.
	 * @param  string              $display Anzeigetext der Navigation.
	 * @param  string              $url     URL des Links.
	 * @param  string              $title   Mouseover-Titel des Links. Optional.
	 * @return ModuleNavigationSub          Diese Instanz zur Methodenverkettung.
	 */
	public function addLink( $display, $url, $title = '' ) {
		return $this->append(new ModuleNavigationLink($this, $display, $url, $title));
	}
	
	/**
	 * Hängt eine neue Überschrift an das Ende des Untermenüs.
	 * @param [type] $display [description]
	 */
	public function addTitle( $display ) {
		return $this->append(new ModuleNavigationTitle($this, $display));
	}
	
	/**
	 * Hängt einen neuen visuellen Trennstrich an das Ende des Untermenüs.
	 */
	public function addSeparator( ) {
		return $this->append(new ModuleNavigationSeparator($this));
	}
	
	/**
	 * Erzeugt ein neues Untermenü, hängt dieses ans Ende dieses Utnermenüs und liefert das Neue zurück.
	 * @param ModuleNavigationSub $display Das erzeugte Untermenü.
	 */
	public function addMenu( $display ) {
		$result = new ModuleNavigationSub($this, $display);
		$this->append($result);
		return $result;
	}
	
	/**
	 * Fügt einen Eintrag in die Navigation vor den indizierten Eintrag ein.
	 * @param  number               $indexBefore Index des Eintrags, vor den der neue Eintrag einzufügen ist.
	 * @param  ModuleNavigationItem $item        Einzufügender Eintrag.
	 * @return ModuleNavigation                  Diese Instanz zur Methodenverkettung.
	 */
	public function insert( $indexBefore, $item ) {
		array_splice($this->items, $indexBefore, 0, $item);
		return $this;
	}
	
	/**
	 * Hängt einen Eintrag an das Ende dieses Untermenüs.
	 * @param  ModuleNavigationItem $item Anzuhängender Eintrag.
	 * @return ModuleNavigationSub        Diese Instanz zur Methodenverkettung.
	 */
	public function append( $item ) {
		$this->items[] = $item;
		return $this;
	}
	
	/**
	 * Entfernt den Eintrag an gegebenem Index aus der Navigation.
	 * @param  number              $index Index des Eintrags.
	 * @return ModuleNavigationSub        Diese Instanz zur Methodenverkettung.
	 */
	public function remove( $index ) {
		array_splice($this->items, $index, 1);
		return $this;
	}
	
	
	public function __toString( ) {
		$result = '<li class="dropdown">' .
			'<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">' .
			$this->display . '<span class="caret"></span>' .
			'</a>';
		$result .= '<ul class="dropdown-menu" role="menu">';
		foreach( $this->items as $item ) {
			$result .= $item;
		}
		$result .= '</ul>';
		$result .= '</li>';
		return $result;
	}
	
}
