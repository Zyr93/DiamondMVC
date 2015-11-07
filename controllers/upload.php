<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * General purpose server-side upload script. It is intended for use with other controllers,
 * thus does not feature a GUI for uploading. Note: DiamondMVC already features Dropzone as an
 * asset!
 */
defined('DIAMONDMVC') or die();

class ControllerUpload extends Controller {
	
	/**
	 * Property names to filter for. Only these will be saved. Ensures only the desired files will be
	 * treated and no additional, unintended, unwanted files.
	 * @var array
	 */
	protected $filters = array();
	
	/**
	 * Stores individual destination paths for each item of the $_FILES superglobal.
	 * @var array
	 */
	protected $paths = array();
	
	
	public function __construct( $db = null ) {
		parent::__construct('upload', $db);
	}
	
	/**
	 * Parses the $_FILES superglobal for uploaded files. An event is triggered for each file. Handlers
	 * can then decide whether to keep the uploaded file. The action result is filled with the properties
	 * of the $_FILES superglobal storing the corresponding result - whether the respective file was
	 * removed or has been accepted.
	 */
	protected function action_main( $skipPermsCheck = false ) {
		if( !$skipPermsCheck and !Permissions::has('sys_upload') ) {
			return $this->redirectForbidden();
		}
		
		$lang    = i18n::load('diamondmvc');
		$result  = array();
		$success = true;
		
		if( !empty($_FILES) ) {
			foreach( $_FILES as $prop => $file ) {
				// Skip this file if not desired.
				if( !empty($this->filters) and !in_array($prop, $this->filters) ) {
					continue;
				}
				
				// Attempt to save the file.
				if( !$this->handleUpload($prop, $file) ) {
					$this->addMessage(str_replace('%name%', $file['name'], $lang->get('ERROR_TITLE', 'ControllerUpload')), $lang->get('ERROR_MESSAGE', 'ControllerUpload'), 'error');
					$result[$prop] = false;
					$success       = false;
				}
				else {
					$result[$prop] = true;
				}
			}
		}
		
		$this->result = array('success' => $success, 'details' => $result);
	}
	
	/**
	 * Gets or sets the property names of the $_FILES superglobal to treat. If none set, i.e. an empty
	 * array is passed (default), all files will be treated.
	 * @param  array $filters
	 * @return ControllerUpload|array This instance if used as a setter, otherwise the current array of filtered names if used as a getter.
	 */
	public function filter( $filters = array() ) {
		if( !func_num_args() ) {
			return $this->filters;
		}
		$this->filters = $filters;
		return $this;
	}
	
	/**
	 * Adds another property name of the $_FILES superglobal to filter for, i.e. it and existing filters
	 * will be saved, others won't.
	 * @param string $filter
	 * @return ControllerUpload This instance to enable method chaining.
	 */
	public function addFilter( $filter ) {
		$this->filters[] = $filter;
		return $this;
	}
	
	/**
	 * Sets the path for a particular property name of the $_FILES superglobal. This allows you to store various
	 * uploaded files at different places instead of the default place ("/tmp").
	 * @param  string  $key        Property name of the $_FILES superglobal to set the path for. The associated file will be saved there.
	 * @param  string  $path       Where to save the associated file.
	 * @param  boolean $appendName Optional. Whether to append the original file name to the path. If set to false, the path itself will be considered to be the absolute destination. Defaults to true.
	 * @return ControllerUpload This instance to enable method chaining.
	 */
	public function path( $key, $path, $appendName = true ) {
		$this->paths[$key] = "$path;" . ($appendName ? 1 : 0);
		return $this;
	}
	
	/**
	 * Triggers the {@link UploadEvent} which allows handlers to decide whether to keep or dump the
	 * uploaded file. If kept, the file is moved to the /uploads directory using the name returned
	 * by {@link UploadEvent#getName()}.
	 * @param  string  $prop Name of the $_FILES superglobal property the uploaded file is associated with
	 * @param  array   $data Entry in the $_FILES superglobal array
	 * @return boolean       Whether the uploaded file was kept
	 */
	protected function handleUpload( $prop, $data ) {
		// Upload accepted by all handlers?
		$evt = new UploadEvent($prop, $data);
		DiamondMVC::instance()->trigger($evt);
		if( $evt->isDefaultPrevented() ) {
			return false;
		}
		
		// Error during the upload process itself?
		if( $evt->hasError() ) {
			return false;
		}
		
		$name = $evt->getName();
		$dest = DIAMONDMVC_ROOT . '/uploads/' . $name;
		if( isset($this->paths[$prop]) ) {
			$tmp    = $this->paths[$prop];
			$index  = strpos($tmp, ';');
			$path   = substr($tmp, 0, $index);
			$append = intval(substr($tmp, $index + 1));
			
			$dest  = $path;
			if( $append ) {
				$dest .= "/$name";
			}
		}
		
		logMsg('ControllerUpload: uploading to ' . $dest, 9, false);
		
		if( !move_uploaded_file($data['tmp_name'], $dest) ) {
			$this->addMessage('Sorry!', 'Upload failed on server side!', 'error');
			logMsg("Failed to move uploaded file {$data['tmp_name']} to " . DIAMONDMVC_ROOT . "/uploads/$name", 9, 5);
			return false;
		}
		return true;
	}
	
	
	protected function redirectForbidden( ) {
		$_SESSION['error'] = array(
			'title' => 'Insufficient permissions',
			'msg'   => 'You lack permission to upload files to the server.',
			'level' => 'error',
		);
		redirect(DIAMONDMVC_URL . '/error');
	}
	
}
