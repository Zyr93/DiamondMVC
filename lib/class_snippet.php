<?php
/**
 * @package  Revinary
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Adds menus to different parts of the website. The main menu can be found on nearly
 * every page.
 * 
 * There are two kinds of simple customizations: parameters and binds.
 * 
 * Parameters can be accessed directly by the PHP in the snippet while
 */
defined('DIAMONDMVC') or die();

/**
 * Represents an HTML snippet. It is similar to the Template class.
 */
class Snippet {
	
	/**
	 * File containing the HTML snippet.
	 * @var string
	 */
	protected $file = '';
	
	/**
	 * Contains the actual HTML output of the snippet.
	 * @var string
	 */
	protected $buffer = '';
	
	/**
	 * HTML template parameters for simple customization, such as color.
	 * @var array
	 */
	protected $params = array();
	
	/**
	 * Values and references for placeholders in the snippet for simple
	 * customization.
	 * @var array
	 */
	protected $bind   = array();
	
	/**
	 * Custom data passed to the snippet for access within the HTML snippet.
	 * @var mixed
	 */
	protected $data = null;
	
	
	/**
	 * Script IDs manually added.
	 * @var array
	 */
	protected $scripts = array();
	
	/**
	 * Stylesheets manually added.
	 * @var array
	 */
	protected $stylesheets = array();
	
	
	public function __construct( $file ) {
		$this->file = $file;
	}
	
	/**
	 * Sets data for access within the snippet.
	 * @param  mixed   $data Any kind of data
	 * @return Snippet       This instance to enable method chaining.
	 */
	public function setData( $data ) {
		$this->data = $data;
		return $this;
	}
	
	/**
	 * Gets the custom data previously set.
	 * @return mixed
	 */
	public function getData( ) {
		return $this->data;
	}
	
	
	/**
	 * Sets the value for a placeholder.
	 * Developer's note: the placeholder is actually replaced when the output is returned using {@link #getContents()}.
	 * @param  string  $placeholder Name of the placeholder to replace.
	 * @param  mixed   $value       Value to bind to the placeholder. Must be stringifyable.
	 * @return Snippet              This instance to enable method chaining.
	 */
	public function replace( $placeholder, $value ) {
		$this->bind[$placeholder] = $value;
		return $this;
	}
	
	/**
	 * Binds a variable reference to the placeholder.
	 * @param  string  $placeholder Name of the placeholder
	 * @param  any     &$reference  Reference to a stringifyable variable
	 * @return Snippet              This instance to enable method chaining.
	 */
	public function bind( $placeholder, &$reference ) {
		$this->bind[$placeholder] = &$reference;
		return $this;
	}
	
	/**
	 * Removes a placeholder replacement or variable reference from the placeholder.
	 * @param  string  $placeholder Name of the placeholder to clear.
	 * @return Snippet              This instance to enable method chaining.
	 */
	public function unbind( $placeholder ) {
		unset($this->bind[$placeholder]);
		return $this;
	}
	
	/**
	 * Sets the value of the named parameter.
	 * @param  string  $name
	 * @param  mixed   $value
	 * @return Snippet This instance to enable method chaining.
	 */
	public function setParam( $name, $value ) {
		$this->params[$name] = $value;
		return $this;
	}
	
	/**
	 * Gets the value of the named paramter. If the parameter wasn't previously
	 * set, the passed $default value is returned.
	 * @param  string $name    of the parameter to retrieve
	 * @param  mixed  $default Value to return if the parameter is unset.
	 * @return mixed           Value of the parameter or the given $default.
	 */
	public function getParam( $name, $default = '' ) {
		if( isset($this->params[$name]) ) {
			return $this->params[$name];
		}
		return $default;
	}
	
	/**
	 * Clears the value of the named parameter.
	 * @param  string  $name of the parameter to clear.
	 * @return Snippet       This instance to enable method chaining.
	 */
	public function clearParam( $name ) {
		unset($this->params[$name]);
		return $this;
	}
	
	/**
	 * Adds another JavaScript module ID. Other program code must explicitly
	 * use these in order to add them to the website.
	 */
	public function addScript( $url ) {
		if( startsWith($url, './') ) {
			$url = '../' . str_replace(DIAMONDMVC_URL, '/', $this->getBaseUrl()) . '/js/' . substr($url, 2);
		}
		else if( startsWith($url, '/') ) {
			$url = "..$url";
		}
		$url = preg_replace('/\/{2,}/', '/', $url);
		
		if( !in_array($url, $this->scripts) ) {
			$this->scripts[] = $url;
		}
		
		return $this;
	}
	
	/**
	 * Gets the scripts previously added.
	 * @return array
	 */
	public function getScripts( ) {
		return $this->scripts;
	}
	
	/**
	 * Adds a stylesheet URL to the snippet. Other program code must explicitly
	 * use these in order to add them to the website.
	 */
	public function addStylesheet( $url, $mime = '' ) {
		$url = trim($url);
		if( empty($url) ) {
			return $this;
		}
		
		$add = $this->makeUrlAbsolute($url, 'css');
		if( !empty($mime) ) {
			$add .= ';' . $mime;
		}
		
		if( !in_array($add, $this->stylesheets) ) {
			$this->stylesheets[] = $add;
		}
		return $this;
	}
	
	/**
	 * Gets the stylesheets previously added.
	 * @return array
	 */
	public function getStylesheets( ) {
		return $this->stylesheets;
	}
	
	
	/**
	 * Reads the contents of the snippet into memory.
	 * @return Snippet This instance to enable method chaining.
	 */
	public function read( ) {
		ob_start();
		include($this->file);
		$this->buffer = ob_get_contents();
		ob_end_clean();
		return $this;
	}
	
	/**
	 * Outputs the contents of the snippet (from memory).
	 * @return Snippet This instance to enable method chaining.
	 */
	public function render( ) {
		$evt = new RenderEvent($this);
		DiamondMVC::instance()->trigger($evt);
		if( !$evt->isDefaultPrevented() ) {
			echo $this->getContents();
		}
		return $this;
	}
	
	/**
	 * Gets the last read HTML output of this Snippet.
	 * @return string
	 */
	public function getContents( ) {
		$result = $this->buffer;
		foreach( $this->bind as $placeholder => $value ) {
			$result = str_replace('${' . $placeholder . '}', $value, $result);
		}
		$result = preg_replace('/\$\{[^\}]*\}/', '', $result);
		return $result;
	}
	
	
	/**
	 * Gets the base URL from which this snippet was loaded.
	 * @return string
	 */
	public function getBaseUrl( ) {
		$result = dirname($this->file);
		$result = str_replace(DIAMONDMVC_ROOT, '', $result);
		return $result;
	}
	
	/**
	 * Converts a relative URL into an absolute one. Absolute URLs remain untouched.
	 * URLs relative to the system root are prefixed with DIAMONDMVC_URL.
	 * @param  string $url  Absolute or relative URL
	 * @param  string $home Path relative to the base URL of this snippet to append to the base URL of this snippet.
	 * @return string       Absolute URL
	 */
	protected function makeUrlAbsolute( $url, $home = '' ) {
		return makeUrlAbsolute($url, $this->getBaseUrl() . "/$home");
	}
	
	
	/**
	 * Magic method to both read and output the contents of this snippet.
	 * @return string
	 */
	public function __toString( ) {
		return $this->read()->getContents();
	}
	
}
