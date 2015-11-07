<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class IniGenerator {
	
	/**
	 * Holds the data of the INI file.
	 * @var array
	 */
	protected $data = array();
	
	
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
			$category = '';
		}
		
		$this->data[$category][$key] = $value;
		return $this;
	}
	
	/**
	 * Gets the currently set value of the key (within the category).
	 * @param  string $key      INI key
	 * @param  string $category INI category
	 * @return mixed            Value as a string or number
	 */
	public function get( $key, $category = '' ) {
		return $this[$category][$key];
	}
	
	
	public function __toString( ) {
		$result = '';
		
		foreach( $data[''] as $key => $value ) {
			if( is_numeric($value) ) {
				$result .= "$key=$value\n";
			}
			else {
				$result .= "$key=\"$value\"\n";
			}
		}
		$result .= "\n";
		
		foreach( $data as $category => $data ) {
			// Skip the empty category which is a special category storing non-categorized data.
			if( empty($category) ) continue;
			
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
