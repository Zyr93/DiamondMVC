<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class View extends Snippet {
	
	/**
	 * Associated controller
	 * @var Controller
	 */
	protected $controller = null;
	
	/**
	 * View name
	 * @var string
	 */
	private $name;
	
	/**
	 * Type of the view. Usually one of the following:
	 *  - html
	 *  - json
	 * @var string
	 */
	protected $type = 'html';
	
	/**
	 * Meta tags. Unlikely to be needed.
	 * @var array
	 */
	protected $meta = '';
	
	
	/**
	 * Constructs a new view with a particular name. The view name is the same as the HTML template file's name.
	 * @param Controller $controller Associated controller
	 * @param string     $name       of the view
	 * @param string     $type       Render type of the view. Defaults to html.
	 */
	public function __construct( $controller, $name, $type = 'html' ) {
		parent::__construct('');
		
		if( !($controller instanceof Controller) ) {
			throw new InvalidArgumentException('Expected parameter 1 (controller) to be a Controller');
		}
		
		$this->controller = $controller;
		$this->name       = $name;
		$this->type       = $type;
	}
	
	/**
	 * Gets the name of this view.
	 * @return string
	 */
	public function getName( ) {
		return $this->name;
	}
	
	/**
	 * Gets the generated path to this view's associated template file. If the file does not exist,
	 * returns an empty string.
	 * @return string
	 */
	public function getPath( ) {
		$jail = realpath(DIAMONDMVC_ROOT . '/views/' . strToLower($this->controller->getName()) . '/templates');
		if( $jail === false ) {
			return '';
		}
		
		// Add suffix unless we're viewing a simple HTML template
		$file = strToLower($this->name);
		if( strToLower($this->type) !== 'html' ) {
			$file .= '.' . strToLower($this->type);
		}
		$file .= '.php';
		
		return jailpath($jail, $file);
	}
	
	/**
	 * Checks if this view's associated template file exists.
	 * @return boolean
	 */
	public function exists( ) {
		return file_exists($this->getPath());
	}
	
	/**
	 * Reads the HTML template of this view into memory. The template is searched
	 * based on the view's name.
	 * Template parameters ought to be set prior to the invokation of this method.
	 * @return View             Diese Instanz zur Methodenverkettung.
	 */
	public function read( ) {
		ob_start();
		
		$path = $this->getPath();
		if( !empty($path) ) {
			include($path);
		}
		else {
			$lang = i18n::load('diamondmvc');
			echo $lang->get('VIEW_NOT_FOUND');
		}
		
		$this->buffer = ob_get_contents();
		ob_end_clean();
		return $this;
	}
	
	/**
	 * Adds another meta tag to the website.
	 * @param  string $meta Content of the meta tag excluding "<meta" and closing ">"
	 * @return View         This instance to enable method chaining.
	 */
	public function addMeta( $meta ) {
		$this->meta .= "<meta $meta>";
		return $this;
	}
	
	/**
	 * Gets the meta tags this view adds to the website.
	 * @return array of meta tags
	 */
	public function getMeta( ) {
		return $this->meta;
	}
	
	
	public function getBaseUrl( ) {
		return DIAMONDMVC_URL . "/views/" . $this->controller->getName() . "/templates";
	}
	
}
