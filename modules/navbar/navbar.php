<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Special {@link ModuleNavigation} which simply uses the navbar template but grants a little
 * deeper control of things, i.e. aligning menu items left and/or right.
 */
defined('DIAMONDMVC') or die();

class ModuleNavbar extends ModuleNavigation {
	
	/**
	 * Items ment for the right side of the navigation bar.
	 * @var array
	 */
	protected $itemsRight = array();
	
	
	/**
	 * Creates a new navigation module.
	 * @param Controller $controller Controller used to generate this module.
	 * @param string     $tpl        Template to use for displaying this module.
	 */
	public function __construct( $controller ) {
		parent::__construct($controller, 'navbar');
		$this->addStylesheet(DIAMONDMVC_URL . '/modules/navigation/css/navigation.css');
	}
	
	/**
	 * Adds a top level item in the navigation aligned on the left side.
	 * @param  string       $display text of the item.
	 * @param  string       $url     the item leads to.
	 * @param  string       $title   Tooltip shown upon hovering above the item. Optional.
	 * @return ModuleNavbar          This instance to enable method chaining.
	 */
	public function addLinkLeft( $display, $url, $title = '' ) {
		return $this->addLink($display, $url, $title);
	}
	
	/**
	 * Adds a top level item in the navigation aligned on the right side.
	 * @param string        $display text of the item.
	 * @param string        $url     the item leads to.
	 * @param string        $title   Tooltip shown upon hovering above the item. Optional.
	 * @return ModuleNavbar          This instance to enable method chaining.
	 */
	public function addLinkRight( $display, $url, $title = '' ) {
		return $this->appendRight(new ModuleNavigationLink($this, $display, $url, $title));
	}
	
	/**
	 * Creates a new heading in the navigation aligned on the left side.
	 * @param  string       $display text of the heading
	 * @return ModuleNavbar          This instance to enable method chaining.
	 */
	public function addTitleLeft( $display ) {
		return $this->addTitle($display);
	}
	
	/**
	 * Creates a new heading in the navigation aligned on the right side.
	 * @param  string       $display text of the heading
	 * @return ModuleNavbar          This instance to enable method chaining.
	 */
	public function addTitleRight( $display ) {
		return $this->appendRight(new ModuleNavigationTitle($display));
	}
	
	/**
	 * Adds a separator in the left menu alignment block.
	 */
	public function addSeparatorLeft( ) {
		return $this->addSeparator();
	}
	
	/**
	 * Adds a separator in the right menu alignment block.
	 */
	public function addSeparatorRight( ) {
		return $this->appendRight(new ModuleNavigationSeparator());
	}
	
	/**
	 * Creates a sub menu aligned on the left side of the nabar.
	 * @param  string              $display text to show as the label of the sub menu.
	 * @return ModuleNavigationSub          Generated sub menu to further customize. Use {@link ModuleNavigationSub#back()} to get this instance again.
	 */
	public function addMenuLeft( $display ) {
		return $this->addMenu($display);
	}
	
	/**
	 * Creates a sub menu aligned on the right side of the navbar.
	 * @param  string              $display text to show as the label of the sub menu.
	 * @return ModuleNavigationSub          Generated sub menu to further customize. Use {@link ModuleNavigationSub#back()} to get this instance again.
	 */
	public function addMenuRight( $display ) {
		$result = new ModuleNavigationSub($this, $display);
		$this->appendRight($result);
		return $result;
	}
	
	/**
	 * Inserts an item into the left item block before the item with the given $index.
	 * @param  number               $beforeIndex Index of the item before which to insert the given $item.
	 * @param  ModuleNavigationItem $item        Item to insert
	 * @return ModuleNavigation                  This instance to enable method chaining.
	 */
	public function insertLeft( $beforeIndex, $item ) {
		array_splice($this->items, $beforeIndex, 0, $item);
		return $this;
	}
	
	/**
	 * Inserts an item into the right item block before the item with the given $index.
	 * @param  number               $beforeIndex Index of the item before which to insert the given $item.
	 * @param  ModuleNavigationItem $item        Item to insert
	 * @return ModuleNavigation                  This instance to enable method chaining.
	 */
	public function insertRight( $beforeIndex, $item ) {
		array_splice($this->itemsRight, $beforeIndex, 0, $item);
		return $this;
	}
	
	/**
	 * Appends an item to the end of the left aligned item block.
	 * @param  ModuleNavigationItem $item to append
	 * @return ModuleNavigation           This instance to enable method chaining.
	 */
	public function appendLeft( $item ) {
		return $this->append($item);
	}
	
	/**
	 * Appends an item to the end of the right aligned item block.
	 * @param  ModuleNavigationItem $item to append
	 * @return ModuleNavigation           This instance to enable method chaining.
	 */
	public function appendRight( $item ) {
		if( $item instanceof ModuleNavigationItem ) {
			$item->setParent($this);
			$this->itemsRight[] = $item;
		}
		else {
			logMsg('Attempted to add non-ModuleNavigationItem', 3, 5);
		}
		return $this;
	}
	
	/**
	 * Removes the item at $index from the navigation.
	 * @param  integer          $index of the item to remove
	 * @return ModuleNavigation        This instance to enable method chaining.
	 */
	public function removeLeft( $index ) {
		return $this->remove($index);
	}
	
	/**
	 * Removes the item at $index from the right aligned item block.
	 * @param  integer          $index of the item to remove
	 * @return ModuleNavigation        This instance to enable method chaining.
	 */
	public function removeRight( $index ) {
		array_splice($this->itemsRight, $index, 1);
		return $this;
	}
	
}
