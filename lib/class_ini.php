<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * The IniGenerator is used in three places:
 *  1. Maintaining the system's configuration file (ini.php)
 *  2. Tweaking an existing or creating a new language file
 *  3. Compiling multiple language files into one while using prefixes for uniqueness
 */
defined('DIAMONDMVC') or die;

class ini {
	
	/**
	 * Prefix to all subsequent categories.
	 * @var string
	 */
	protected $prefix = '';
	
	/**
	 * Holds the data of the ini.
	 * @var array
	 */
	protected $data = array();
	
	
	/**
	 * Parser temporary variable
	 * Holds the lines of the ini string as it is parsed line by line. False if currently not parsing.
	 * @var array|boolean
	 */
	protected $parser_lines = false;
	
	/**
	 * Parser temporary variable
	 * Remembers the index of the current line in parsing. False if currently not parsing.
	 * @var integer|boolean
	 */
	protected $parser_index = false;
	
	/**
	 * Parser temporary variable
	 * Stores the category we are currently parsing. False if not in a category.
	 * @var string|boolean
	 */
	protected $parser_currentCategory = false;
	
	/**
	 * Parser temporary variable
	 * Stores the current value in case it is multiline.
	 * @var string
	 */
	protected $parser_currentValue = false;
	
	
	/**
	 * Sets the prefix for all subsequent categories.
	 * @param string $prefix Optional. Omit to get the current value.
	 * @return string|IniGenerator If used as a getter, returns the current prefix. If used as a setter, returns this instance to enable method chaining.
	 */
	public function prefix( $prefix = '' ) {
		if( !func_num_args() ) {
			return $this->prefix;
		}
		$this->prefix = $prefix;
		return $this;
	}
	
	/**
	 * Reads the INI from the given path.
	 * @param  string       $file Path to the INI file to read in.
	 * @return IniGenerator       This instance to enable method chaining.
	 */
	public function read( $file ) {
		if( !file_exists($file) ) {
			logMsg('Failed to read ini file "' . $file . '": not found', 9, 5);
		}
		else {
			ob_start();
			include($file);
			$contents = ob_get_contents();
			ob_end_clean();
			
			$this->data = parse_ini_string($contents, true);
		}
		return $this;
	}
	
	/**
	 * Writes the contents of this INI to file.
	 * @param  string       $file Path to the INI file to save at.
	 * @return IniGenerator       This instance to enable method chaining.
	 */
	public function write( $file ) {
		if( @file_put_contents($file, $this->__toString()) === false ) {
			throw new Exception("Failed to write INI to disk");
		}
		return $this;
	}
	
	/**
	 * Sets the value of a key (within the category).
	 * @param string $key      INI key
	 * @param string $category INI category. Optional.
	 * @param mixed  $value    Numeric or stringifyable value to set.
	 * @return IniGenerator    This instance to enable method chaining.
	 */
	public function set( $key, $category, $value = '' ) {
		if( func_num_args() < 3 ) {
			$value    = $category;
			$this->data[$key] = $value;
		}
		else {
			$this->data[$category][$key] = $value;
		}
		return $this;
	}
	
	/**
	 * Gets the currently set value of the key (within the category).
	 * @param  string $key      INI key
	 * @param  string $category INI category
	 * @return mixed            Value as a string or number
	 */
	public function get( $key, $category = '' ) {
		if( empty($category) ) {
			return $this->data[$key];
		}
		
		if( !isset($this->data[$category]) ) {
			return null;
		}
		
		return $this->data[$category][$key];
	}
	
	/**
	 * A convenience function like {@link #get()}, but you must provide
	 * a default value to return in case the key or category does not exist.
	 * 
	 * Note: this method is separate from {@link #get()} because it's
	 * otherwise hard to determine what you are asking for when passing 
	 * two values to it - you could either request a value from a category
	 * or pass its default value. Thus this method has been born as a
	 * convenience method.
	 * @param  string $key      Key to retrieve
	 * @param  string $category Optional. Category of the key
	 * @param  mixed  $default  Default value to return if the key or category does not exist
	 * @return mixed            Default or read value
	 */
	public function def( $key, $category, $default = false ) {
		if( func_num_args() < 3 ) {
			$default  = $category;
			$category = '';
			
			if( !isset($this->data[$key]) or empty($this->data[$key]) ) {
				return $default;
			}
			
			return $this->data[$key];
		}
		
		if( !isset($this->data[$category]) or !isset($this->data[$category][$key]) ) {
			return $default;
		}
		
		$value = $this->data[$category][$key];
		
		return empty($value) ? $default : $value;
	}
	
	
	/**
	 * Returns the contents of the generated ini.
	 * @return string
	 */
	public function __toString( ) {
		$result = '';
		
		foreach( $this->data as $key => $value ) {
			// Skip categories in the first run through as they would break the format at this point.
			if( is_array($value) ) continue;
			
			if( is_numeric($value) ) {
				$result .= "$key=$value\n";
			}
			else {
				$result .= "$key=\"$value\"\n";
			}
		}
		$result .= "\n";
		
		foreach( $this->data as $category => $data ) {
			// Skip uncategorized keys as they have been treated above.
			if( !is_array($data) ) continue;
			
			$result .= "[$category]\n";
			foreach( $data as $key => $value ) {
				if( is_numeric($value) ) {
					$result .= "$key=$value\n";
				}
				else {
					$result .= "$key=\"$value\"\n";
				}
			}
			$result .= "\n";
		}
		
		return $result;
	}
	
}
