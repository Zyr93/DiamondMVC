<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * TODO: Localization
 */
defined('DIAMONDMVC') or die();

class Version {
	
	public $major    = 1;
	public $minor    = 0;
	public $revision = 0;
	public $build    = 0;
	/**
	 * Indicates that this version is a release candidate. I use the RC mainly to skip publishing the build number
	 * while the project is not large enough to use a sophisticated automated build system.
	 * @var integer
	 */
	public $rc       = 0;
	/**
	 * Flavor of the version. When parsed this is a single letter. It does not change the
	 * weight of the version.
	 * @var string
	 */
	public $variant  = '';
	
	
	public function major( $major = 0 ) {
		if( func_num_args() === 0 ) {
			return $this->major;
		}
		$this->major = intval($major);
		return $this;
	}
	
	public function minor( $minor = 0 ) {
		if( func_num_args() === 0 ) {
			return $this->minor;
		}
		$this->minor = intval($minor);
		return $this;
	}
	
	public function revision( $revision = 0 ) {
		if( func_num_args() === 0 ) {
			return $this->revision;
		}
		$this->revision = intval($revision);
		return $this;
	}
	
	public function build( $build = 0 ) {
		if( func_num_args() === 0 ) {
			return $this->build;
		}
		$this->build = intval($build);
		return $this;
	}
	
	public function variant( $variant = '' ) {
		if( func_num_args() === 0 ) {
			return $this->variant;
		}
		$this->variant = $variant;
		return $this;
	}
	
	public function rc( $rc = 0 ) {
		if( func_num_args() === 0 ) {
			return $this->rc;
		}
		$this->rc = $rc;
		return $this;
	}
	
	
	/**
	 * Checks whether this version is less than the specified other version.
	 * @param  Version $other
	 * @return boolean
	 */
	public function lessThan( $other ) {
		return $this->compareTo($other) === -1;
	}
	
	/**
	 * Checks whether this version is greater than the other.
	 * @param  Version $other
	 * @return boolean
	 */
	public function greaterThan( $other ) {
		return $this->compareTo($other) === 1;
	}
	
	/**
	 * Compares this version to another.
	 * @param  Version $other Version to compare to.
	 * @return integer        1 if this version is greater than, -1 if it is less than, or 0 if it is exactly equal to the other version.
	 */
	public function compareTo( $other ) {
		if( !($other instanceof Version) ) {
			throw new InvalidArgumentException('Cannot compare to non-Version');
		}
		
		if( $this->major < $other->major ) {
			return -1;
		}
		if( $this->major > $other->major ) {
			return 1;
		}
		
		if( $this->minor < $other->minor ) {
			return -1;
		}
		if( $this->minor > $other->minor ) {
			return 1;
		}
		
		if( $this->revision < $other->revision ) {
			return -1;
		}
		if( $this->revision > $other->revision ) {
			return 1;
		}
		
		if( $this->build < $other->build ) {
			return -1;
		}
		if( $this->build > $other->build ) {
			return 1;
		}
		
		if( $this->rc < $other->rc ) {
			return -1;
		}
		if( $this->rc > $other->rc ) {
			return 1;
		}
		
		return 0;
	}
	
	
	static public function parse( $version ) {
		$result = new Version();
		
		if( preg_match('/^(\d+)(\.(\d+)(\.(\d+)([.-](\d+))?)?)?([a-z])?([ -]?rc(\d+))?$/', $version, $matches) ) {
			$result->major($matches[1]);
			if( isset($matches[3]) ) {
				$result->minor($matches[3]);
				if( isset($matches[5]) ) {
					$result->revision($matches[5]);
					if( isset($matches[7]) ) {
						$result->build($matches[7]);
					}
				}
			}
			if( isset($matches[8]) ) {
				$result->variant($matches[8]);
			}
			if( isset($matches[10]) ) {
				$result->rc($matches[10]);
			}
		}
		
		return $result;
	}
	
	
	public function __toString( ) {
		$result = "{$this->major}.{$this->minor}.{$this->revision}";
		if( $this->build ) {
			$result .= ".{$this->build}";
		}
		if( !empty($this->variant) ) {
			$result .= $this->variant;
		}
		if( $this->rc ) {
			$result .= " rc{$this->rc}";
		}
		return $result;
	}
	
}
