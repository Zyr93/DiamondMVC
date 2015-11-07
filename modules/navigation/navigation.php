<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

$requires = array('item.php', 'link.php', 'title.php', 'sub.php', 'separator.php');
foreach( $requires as $require ) {
	require_once(dirname(__FILE__) . DS . $require);
}

class ModuleNavigation extends Module {
	
	/**
	 * List of navigation items. Templates have direct access to these.
	 * @var array
	 */
	protected $items = array();
	
	
	/**
	 * Creates a new navigation module.
	 * @param Controller $controller Controller used to generate this module.
	 * @param string     $tpl        Template to use for displaying this module.
	 */
	public function __construct( $controller, $tpl = 'default' ) {
		parent::__construct($controller, 'navigation', $tpl);
		$this->addStylesheet('navigation.css');
	}
	
	/**
	 * Override to merge required script IDs of items with script IDs of this module.
	 * @return array
	 */
	public function getScripts( ) {
		$scripts = parent::getScripts();
		foreach( $this->items as $item ) {
			$scripts = array_merge($scripts, $item->getScripts());
		}
		return $scripts;
	}
	
	/**
	 * Override to merge required stylesheets of items with stylesheets of this module.
	 * @return array
	 */
	public function getStylesheets( ) {
		$sheets = parent::getStylesheets();
		foreach( $this->items as $item ) {
			$sheets = array_merge($sheets, $item->getStylesheets());
		}
		return $sheets;
	}
	
	
	/**
	 * Just in case you prefer using a method instead of accessing the field directly.
	 * @return array
	 */
	protected function getItems( ) {
		return $this->items;
	}
	
	
	/**
	 * Creates a new top level item in the navigation.
	 * @param  string                $display text of the item.
	 * @param  string                $url     Self explanatory
	 * @return ModuleNavigation              This instance to enable method chaining.
	 */
	public function addLink( $display, $url, $title = '' ) {
		return $this->append(new ModuleNavigationLink($this, $display, $url, $title));
	}
	
	/**
	 * Creates a new heading in the navigation.
	 * @param  string           $display text of the heading.
	 * @return ModuleNavigation          This instance to enable method chaining.
	 */
	public function addTitle( $display ) {
		return $this->append(new ModuleNavigationTitle($display));
	}
	
	/**
	 * Creates a separator item.
	 * @return ModuleNavigation This instance to enable method chaining.
	 */
	public function addSeparator( ) {
		return $this->append(new ModuleNavigationSeparator());
	}
	
	/**
	 * Creates a sub menu.
	 * @param  string              $display Label of the sub menu
	 * @return ModuleNavigationSub          Generated sub menu
	 */
	public function addMenu( $display ) {
		$result = new ModuleNavigationSub($this, $display);
		$this->append($result);
		return $result;
	}
	
	/**
	 * Inserts an item into the navigation before the item with the given $index.
	 * @param  number               $beforeIndex Index of the item before which to insert the given $item.
	 * @param  ModuleNavigationItem $item        Item to insert
	 * @return ModuleNavigation                  This instance to enable method chaining.
	 */
	public function insert( $beforeIndex, $item ) {
		array_splice($this->items, $beforeIndex, 0, $item);
		return $this;
	}
	
	/**
	 * Appends an item to the end of the navigation.
	 * @param  ModuleNavigationItem $item to append
	 * @return ModuleNavigation           This instance to enable method chaining.
	 */
	public function append( $item ) {
		if( $item instanceof ModuleNavigationItem ) {
			$item->setParent($this);
			$this->items[] = $item;
		}
		else {
			logMsg('Attempted to add non-ModuleNavigationItem', 3, 5);
		}
		return $this;
	}
	
	/**
	 * Removes the item at $index from the navigation.
	 * @param  number           $index of the item to remove
	 * @return ModuleNavigation        This instance to enable method chaining.
	 */
	public function remove( $index ) {
		array_splice($this->items, $index, 1);
		return $this;
	}
	
}
