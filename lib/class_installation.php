<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Represents an installation's JSON meta data.
 */
defined('DIAMONDMVC') or die();

abstract class Installation {
	
	/**
	 * Gets the protocol version requested by this installation.
	 * @return Version
	 */
	abstract public function getProtocolVersion( );
	
	/**
	 * Gets the installation version.
	 * @return Version
	 */
	abstract public function getVersion( );
	
	/**
	 * Gets the name of this installation.
	 * @return string
	 */
	abstract public function getName( );
	
	/**
	 * Gets the description of this installation.
	 * @return string
	 */
	abstract public function getDescription( );
	
	/**
	 * Gets the installation author.
	 * @return string
	 */
	abstract public function getAuthor( );
	
	/**
	 * Gets the author's copyright.
	 * @return string
	 */
	abstract public function getCopyright( );
	
	/**
	 * Gets the URL from which a copy of this installaton can be obtained.
	 * @return string
	 */
	abstract public function getDistUrl( );
	
	/**
	 * Gets the URL which can be used to check for updates for this installation.
	 * @return string
	 */
	abstract public function getUpdateUrl( );
	
	/**
	 * Sets the files associated with this installation.
	 * @param array $files
	 */
	abstract public function setFiles( $files );
	
	/**
	 * Gets the files associated with this installation.
	 * @return [type] [description]
	 */
	abstract public function getFiles( );
	
	/**
	 * Saves the installation JSON meta data to the given file.
	 * @param string $file
	 * @return Installation This instance to enable method chaining.
	 */
	abstract public function save( $file );
	
	
	/**
	 * Gets an Installation instance from the JSON contents of the installation meta data file.
	 * As currently only a single installation meta data protocol is available, this factory does
	 * not do much yet.
	 * @param  array        $json Contents of the installation meta data file
	 * @return Installation       Uniform PHP representation of the installation meta data
	 */
	static public function getInstallation( $json ) {
		return new InstallationV1($json);
	}
	
}

class InstallationV1 extends Installation {
	
	/**
	 * Original JSON information passed to our constructor.
	 * @var array
	 */
	protected $original = array();
	
	/**
	 * Installation version
	 * @var Version
	 */
	protected $version = null;
	
	/**
	 * Name of the installation
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * Installation description
	 * @var string
	 */
	protected $desc = '';
	
	/**
	 * Installation author
	 * @var string
	 */
	protected $author = '';
	
	/**
	 * Distribution URL of the installation
	 * @var string
	 */
	protected $distUrl = '';
	
	/**
	 * Update URL of the installation
	 * @var string
	 */
	protected $updateUrl = '';
	
	/**
	 * Associated files
	 * @var array
	 */
	protected $files = array();
	
	
	public function __construct( $json ) {
		$this->original  = $json;
		$this->version   = isset($json['version'])                                    ? Version::parse($json['version']) : Version::parse('1.0');
		$this->name      = isset($json['name'])                                       ? $json['name']        : '';
		$this->desc      = isset($json['description'])                                ? $json['description'] : '';
		$this->author    = isset($json['author'])                                     ? $json['author']      : '';
		$this->copyright = isset($json['copyright'])                                  ? $json['copyright']   : '';
		$this->distUrl   = (isset($json['distUrl']) and is_url($json['distUrl']))     ? $json['distUrl']     : '';
		$this->updateUrl = (isset($json['updateUrl']) and is_url($json['updateUrl'])) ? $json['updateUrl']   : '';
		$this->files     = (isset($json['files']) and is_array($json['files']))       ? $json['files']       : array();
	}
	
	
	public function getProtocolVersion( ) {
		return Version::parse('1.0');
	}
	
	public function getVersion( ) {
		return $this->version;
	}
	
	public function getName( ) {
		return $this->name;
	}
	
	public function getDescription( ) {
		return $this->desc;
	}
	
	public function getAuthor( ) {
		return $this->author;
	}
	
	public function getCopyright( ) {
		return $this->copyright;
	}
	
	public function getDistUrl( ) {
		return $this->distUrl;
	}
	
	public function getUpdateUrl( ) {
		return $this->updateUrl;
	}
	
	public function setFiles( $files ) {
		if( is_array($files) ) {
			$this->original['files'] = $this->files = $files;
		}
		return $this;
	}
	
	public function getFiles( ) {
		return $this->files;
	}
	
	public function save( $file ) {
		if( !file_put_contents($file, json_encode($this->original)) ) {
			throw new InstallationException("Failed to save to $file");
		}
		return $this;
	}
	
}
