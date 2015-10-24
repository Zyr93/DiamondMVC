<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * An event triggered upon file upload shortly before the ControllerUpload would handle
 * the file. It can be used to abort the file upload by preventing the default action
 * ({@link Event#preventDefault()})
 */
defined('DIAMONDMVC') or die();

class UploadEvent extends Event {
	
	/**
	 * Property in the $_FILES superglobal.
	 * @var string
	 */
	protected $prop = '';
	
	/**
	 * Original name of the uploaded file as saved on the client maschine.
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * MIME type of the uploaded file if the browser provided this. Can be used for simple
	 * safety precautions, but I recommend checking the magic number of the actual file.
	 * @var string
	 */
	protected $mime = '';
	
	/**
	 * Size of the uploaded file in bytes.
	 * @var integer
	 */
	protected $size = 0;
	
	/**
	 * Name of the temporary file as which the uploaded file was temporarily saved.
	 * @var string
	 */
	protected $tmp_name = '';
	
	/**
	 * Error code of the uploaded file.
	 * @var integer
	 */
	protected $error = 0;
	
	
	/**
	 * Constructs the event.
	 * @param string $prop $_FILES superglobal property
	 * @param array  $data Uploaded file information as found in the $_FILES superglobal.
	 */
	public function __construct( $prop, $data ) {
		parent::__construct('upload');
		$this->prop     = $prop;
		$this->name     = $data['name'];
		$this->mime     = $data['type'];
		$this->size     = $data['size'];
		$this->tmp_name = $data['tmp_name'];
		$this->error    = $data['error'];
	}
	
	
	/**
	 * Gets the property name of the uploaded file within the $_FILES superglobal.
	 * @return string
	 */
	public function getProp( ) {
		return $this->prop;
	}
	
	/**
	 * @see #getProp()
	 */
	public function getKey( ) {
		return $this->prop;
	}
	
	/**
	 * Gets the original name of the uploaded file as saved on the client maschine.
	 * @return string
	 */
	public function getName( ) {
		return $this->name;
	}
	
	/**
	 * Sets the name to save the associated file as. NOTE: overwrites the "original" file name!
	 * @param string $name
	 * @return UploadEvent This instance to enable method chaining.
	 */
	public function setName( $name ) {
		$this->name = $name;
		return $this;
	}
	
	/**
	 * Get the MIME type of the uploaded file if provided by the server.
	 * CAVEAT: this is not checked by the PHP server and thus can be any information
	 * the browser feeds you! For security reasons I thus recommend checking the magic
	 * number of the file.
	 * @return string
	 */
	public function getMimeType( ) {
		return $this->mime;
	}
	
	/**
	 * Gets the size of the uploaed file in bytes.
	 * @return integer
	 */
	public function getSize( ) {
		return $this->size;
	}
	
	/**
	 * Get the name of the temporary file as which the uploaded file was saved on the
	 * server.
	 * @return string
	 */
	public function getTmpName( ) {
		return $this->tmp_name;
	}
	
	/**
	 * Gets the error code of the uploaded file in case of an error during upload.
	 * @return integer
	 */
	public function getError( ) {
		return $this->error;
	}
	
	/**
	 * Checks whether an error occurred during upload.
	 * @return boolean
	 */
	public function hasError( ) {
		return !empty($this->error);
	}
	
}
