<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * System administration controller. Manages all sorts of installations, including plugin priority.
 */
defined('DIAMONDMVC') or die();

class ControllerFileBrowser extends Controller {
	
	/**
	 * Jail of the files returned by {@link #getFiles()}. Files must lie within
	 * the given directory.
	 * @var string
	 */
	protected $jail = DIAMONDMVC_ROOT;
	
	/**
	 * URL to which to send requests from within the client side file browser.
	 * @var string
	 */
	protected $actionUrl = DIAMONDMVC_URL . '/filebrowser';
	
	/**
	 * Files to list in the browser.
	 * @var array
	 */
	protected $files = array();
	
	/**
	 * Whether to list dot files in the browser. Defaults to false.
	 * @var boolean
	 */
	protected $dotFiles = false;
	
	/**
	 * Whether we're displaying the copy and paste buttons. The paste button is shared with the cut button.
	 * @var boolean
	 */
	protected $controlCopy = true;
	
	/**
	 * Whether we're displaying the cut and paste buttons. The paste button is shared with the copy button.
	 * @var boolean
	 */
	protected $controlMove = true;
	
	/**
	 * Whether we're displaying the delete button.
	 * @var boolean
	 */
	protected $controlDelete = true;
	
	/**
	 * Whether we're displaying the rename button.
	 * @var boolean
	 */
	protected $controlRename = true;
	
	/**
	 * Whether we're displaying the refresh button.
	 * @var boolean
	 */
	protected $controlRefresh = true;
	
	/**
	 * Whether we're displaying the upload button.
	 * @var boolean
	 */
	protected $controlUpload = true;
	
	/**
	 * Whether we're displaying the "New dir" button
	 * @var boolean
	 */
	protected $controlMkdir = true;
	
	/**
	 * Custom control buttons HTML
	 * @var string
	 */
	protected $controlCustom = '';
	
	
	public function __construct( $db = null ) {
		parent::__construct('filebrowser', $db);
	}
	
	protected function action_browse( $base = '', $id = '', $actionUrl = '' ) {
		if( !Permissions::has('sys_fs_browse') ) {
			$this->redirectForbidden();
			return false;
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['base']) or !isset($_REQUEST['id']) ) {
				$this->result = array('success' => false, 'msg' => 'Missing arguments');
				return false;
			}
			$base = $_REQUEST['base'];
			$id   = $_REQUEST['id'];
			$actionUrl = isset($_REQUEST['actionUrl']) ? $_REQUEST['actionUrl'] : '';
		}
		
		// If the requested path is not a directory, obviously we can't browse it.
		$path = $this->buildPath($base, $id);
		$this->files($this->getFiles($path))->actionUrl($actionUrl);
		if( !is_dir($path) ) {
			$this->result = array('success' => 'false', 'msg' => 'Not a directory');
			return false;
		}
		
		// Get the view/snippet ...
		$snippet = $this->getSnippet($base, $id);
		
		$result = array('success' => true);
		$result['html'] = $snippet->read()->getContents();
		$this->result = $result;
	}
	
	protected function action_size( $base = '', $id = '' ) {
		if( !Permissions::has('sys_fs_browse') ) {
			$this->redirectForbidden();
			return false;
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['base']) or !isset($_REQUEST['id']) ) {
				$this->result = 0;
				return false;
			}
			$base = $_REQUEST['base'];
			$id   = $_REQUEST['id'];
		}
		
		$this->result = $this->getSize($this->buildPath($base, $id));
	}
	
	
	/**
	 * Rename one or multiple files. In case multiple files are given, they all receive the same name suffixed
	 * with an incrementing index.
	 * @param  string $base Filebrowser base ID
	 * @param  string $id   File or folder locally unique ID
	 * @param  string $name New name for the files and folders
	 */
	public function action_rename( $base = '', $id = '', $name = '' ) {
		if( !Permissions::has('sys_fs_rename') ) {
			$this->redirectForbidden();
			return false;
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['base']) or !isset($_REQUEST['ids']) or !isset($_REQUEST['name']) ) {
				$this->result = array('success' => false, 'msg' => 'Missing arguments');
				return false;
			}
			else {
				$base = $_REQUEST['base'];
				$ids  = $_REQUEST['ids'];
				$name = $_REQUEST['name'];
			}
		}
		
		$result  = array();
		$success = true;
		$index   = 0;
		$name    = $this->buildPath($base, $name);
		
		foreach( $ids as $id ) {
			// Build their paths based on file browser base and item ID
			$pathSrc = $this->buildPath($base, $id);
			
			// Determine the target name for the current file
			$pathTgt = $this->getIndexedFileName($name, $index);
			$index   = 0;
			while( file_exists($pathTgt) ) {
				$index++;
				$pathTgt = $this->getIndexedFileName($name, $index);
			}
			
			if( file_exists($pathSrc) ) {
				$tmp         = rename($pathSrc, $pathTgt);
				$result[$id] = $tmp;
				$success     = $success && $tmp;
			}
		}
		
		$this->result = array('success' => $success, 'details' => $result);
		return $success;
	}
	
	protected function action_copy( $baseSrc = '', $baseTgt = '', $baseIds = array() ) {
		if( !Permissions::has('sys_fs_alter') ) {
			$this->redirectForbidden();
			return false;
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['baseSrc']) or !isset($_REQUEST['baseTgt']) or !isset($_REQUEST['ids']) ) {
				$this->result = array('success' => false, 'msg' => 'Missing arguments');
				return false;
			}
			
			$baseSrc = $_REQUEST['baseSrc'];
			$baseTgt = $_REQUEST['baseTgt'];
			$ids     = $_REQUEST['ids'];
		}
		
		$result  = array();
		$success = true;
		
		foreach( $ids as $id ) {
			$pathSrc = $this->buildPath($baseSrc, $id);
			$pathTgt = $this->buildPath($baseTgt, $id);
			
			if( !file_exists($pathSrc) ) {
				$success     = false;
				$result[$id] = false;
			}
			else if( file_exists($pathTgt) ) {
				$success     = false;
				$result[$id] = false;
			}
			else {
				$tmp = rcopy($pathSrc, $pathTgt);
				$result[$id] = $tmp;
				$success     = $success && $tmp;
			}
		}
		
		$this->result = array('success' => $success, 'details' => $result);
		return $success;
	}
	
	protected function action_move( $baseSrc = '', $baseTgt = '', $ids = array() ) {
		if( !Permissions::has('sys_fs_alter') ) {
			$this->redirectForbidden();
			return false;
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['baseSrc']) or !isset($_REQUEST['baseTgt']) or !isset($_REQUEST['ids']) ) {
				$this->result = array('success' => false, 'msg' => 'Missing arguments');
				return false;
			}
			
			$baseSrc = $_REQUEST['baseSrc'];
			$baseTgt = $_REQUEST['baseTgt'];
			$ids     = $_REQUEST['ids'];
		}
		
		$result  = array();
		$success = true;
		
		foreach( $ids as $id ) {
			$pathSrc = $this->buildPath($baseSrc, $id);
			$pathTgt = $this->buildPath($baseTgt, $id);
			
			if( !file_exists($pathSrc) ) {
				$success     = false;
				$result[$id] = false;
			}
			else if( file_exists($pathTgt) ) {
				$success     = false;
				$result[$id] = false;
			}
			else {
				$tmp = rename($pathSrc, $pathTgt);
				$result[$id] = $tmp;
				$success     = $success && $tmp;
			}
		}
		
		$this->result = array('success' => $success, 'details' => $result);
		return $success;
	}
	
	protected function action_delete( $base = '', $ids = array() ) {
		if( !Permissions::has('sys_fs_delete') ) {
			$this->redirectForbidden();
			return false;
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['base']) or !isset($_REQUEST['ids']) ) {
				$this->result = array('success' => false, 'msg' => 'Missing arguments');
				return false;
			}
			
			$base = $_REQUEST['base'];
			$ids  = $_REQUEST['ids'];
		}
		
		$result  = array();
		$success = true;
		
		foreach( $ids as $id ) {
			$path = $this->buildPath($base, $id);
			if( is_dir($path) ) {
				$tmp = rmdirs($path);
			}
			else if( is_file($path) ) {
				$tmp = unlink($path);
			}
			else {
				$tmp = false;
			}
			$result [$id] = $tmp;
			$success      = $success && $tmp;
		}
		
		$this->result = array('success' => $success, 'details' => $result);
		return $success;
	}
	
	protected function action_upload( $base = '' ) {
		$path = $this->buildPath($base);
		
		$ctrl = new ControllerUpload($this->db);
		$ctrl->addFilter('filebrowser-upload')->path('filebrowser-upload', $path, true)->action('main');
		
		$this->result = $ctrl->result;
	}
	
	protected function action_mkdir( $base = '', $id = '' ) {
		if( !Permissions::has('sys_fs_create') ) {
			return $this->redirectForbidden();
		}
		
		if( !func_num_args() ) {
			if( !isset($_REQUEST['base']) or !isset($_REQUEST['id']) ) {
				$this->result = array('success' => false, 'msg' => 'Missing arguments');
				return false;
			}
			else {
				$base = $_REQUEST['base'];
				$id   = $_REQUEST['id'];
			}
		}
		
		$path = $this->buildPath($base, $id);
		if( file_exists($path) ) {
			$this->result = array('success' => false, 'msg' => 'A file with this name already exists!');
			return false;
		}
		
		if( !mkdir($path) ) {
			$this->result = array('success' => false, 'msg' => 'I could not create your directory!');
			return false;
		}
		
		$this->result = array('success' => true);
		return true;
	}
	
	protected function getIndexedFileName( $file, $index ) {
		if( $index === 0 ) {
			return $file;
		}
		
		// Extract the file name from the path
		if( strrpos($file, '/') !== false ) {
			$find = strrpos($file, '/');
		}
		else if( strrpos($file, '\\') !== false ) {
			$find = strrpos($file, '\\');
		}
		
		if( isset($find) ) {
			$dirname = substr($file, 0, $find);
			$name    = substr($file, $find + 1);
		}
		else {
			$dirname = '';
			$name    = $file;
		}
		
		// Separate the "file extensions" from the name to insert the index
		$parts = explode('.', $name);
		$first = array_shift($parts);
		array_unshift($parts, $index);
		array_unshift($parts, $first);
		$name = implode('.', $parts);
		
		return $dirname . DS . $name;
	}
	
	/**
	 * Build the path to the selected item. Standardized method considering . and .. special directories.
	 * @param  string $base
	 * @param  string $id
	 * @return string
	 */
	public function buildBase( $base, $id = '' ) {
		if( empty($id) ) {
			return $base;
		}
		
		// Special treatment for browsing from the root directory.
		if( $base === '/' ) {
			if( $id === '..' or $id === '.' ) {
				return $base;
			}
			return "/$id";
		}
		
		if( $id === '..' ) {
			$parts = explode('/', $base);
			// Can't go any further up than root.
			if( count($parts) > 1 ) {
				array_pop($parts);
			}
			return (count($parts) === 1 and empty($parts[0])) ? '/' : implode('/', $parts);
		}
		if( $id === '.' ) {
			return $base;
		}
		return "{$base}/{$id}";
	}
	
	/**
	 * Builds the path from the base and item ID.
	 * @param  string $base ID of the filebrowser
	 * @param  string $id   Locally unique ID of the item, optional
	 * @return string       Built path to the chosen file or directory
	 */
	public function buildPath( $base, $id = '' ) {
		$path = !empty($id) ? $base . DS . $id : $base;
		$path = preg_replace('/\\\\+/', '/', $path);
		$path = preg_replace('/\/{2,}/', '/', $path);
		
		if( startsWith($path, '/') ) {
			$path = substr($path, 1);
		}
		if( !empty($this->jail) ) {
			$path = jailpath($this->jail, $path);
		}
		
		return $path;
	}
	
	/**
	 * Gets the default view of this controller as snippet while providing it with all the collected data
	 * without interfering with the {@link #result}.
	 * @param  string $path to the files to browse in the filebrowser
	 * @return Snippet The default view of this controller as Snippet.
	 */
	public function getSnippet( $base, $id = '' ) {
		$rebase  = $this->buildBase($base, $id);
		$snippet = new Snippet($this->getTemplatePath('default'));
		
		$path = $rebase;
		if( !empty($this->jail) ) {
			$path = jailpath($this->jail, $path);
		}
		
		// ... which retrieves its data from our result
		$data = array();
		$data['base']      = $rebase;
		$data['files']     = $this->files;
		$data['up_dir']    = $data['base'] !== '/';
		$data['actionUrl'] = $this->actionUrl;
		$data['controls']  = array(
			'copy'    => $this->controlCopy,
			'move'    => $this->controlMove,
			'delete'  => $this->controlDelete,
			'rename'  => $this->controlRename,
			'refresh' => $this->controlRefresh,
			'upload'  => $this->controlUpload,
			'mkdir'   => $this->controlMkdir,
			'custom'  => $this->controlCustom,
		);
		$snippet->setData($data);
		
		return $snippet;
	}
	
	
	/**
	 * Gets the size of a file or directory. If the size of a directory is to be determined, recurrsively
	 * gets the size of all files within the directory, including its subdirectories.
	 * @return integer Size of the target file or directory in bytes.
	 */
	public function getSize( $path ) {
		if( !empty($this->jail) ) {
			$path = jailpath($this->jail, $path);
			if( $path === $this->jail ) {
				return 0;
			}
		}
		
		if( !file_exists($path) ) {
			return 0;
		}
		
		if( is_dir($path) ) {
			$result = 0;
			
			foreach( array_merge(glob("$path/*"), glob("$path/.*")) as $file ) {
				if( endsWith($file, '.') or endsWith($file, '..') ) continue;
				$result += $this->getSize($file);
			}
		}
		else {
			$result = filesize($path);
		}
		
		return $result;
	}
	
	
	/**
	 * Jails files returned by {@link #getFiles()} to the given directory or returns the current jail
	 * if no arguments are passed. By default files are jailed to DiamondMVC's root.
	 * 
	 * You can pass an empty string to remove the jail, but this is not recommended as it could possibly,
	 * depending on server settings, list files outside of the htdocs root.
	 * @param  string $path
	 * @return ControllerFileBrowser
	 */
	public function jail( $path = '' ) {
		if( func_num_args() === 0 ) {
			return $this->jail;
		}
		$this->jail = sanitizePath($path);
		return $this;
	}
	
	/**
	 * Gets or sets the action URL of the associated file browser snippet. Actions within the file browser
	 * will be sent there.
	 * @param  string $url Action URL to set
	 * @return string|ControllerFileBrowser This instance to enable method chaining if used as a setter, otherwise the current URL if used as a getter.
	 */
	public function actionUrl( $url = '' ) {
		if( !func_num_args() ) {
			return $this->actionUrl;
		}
		$this->actionUrl = $url;
		return $this;
	}
	
	/**
	 * Gets or sets the files listed in the file browser.
	 * @return array|ControllerFileBrowser This instance to enable method chaining if used as a setter, otherwise the current files if used as a getter.
	 */
	public function files( $files = array() ) {
		if( !func_num_args() ) {
			return $this->files;
		}
		$this->files = $files;
		return $this;
	}
	
	/**
	 * Gets or sets whether to retrieve dot files (hidden files) and directories through {@link #getFiles()}.
	 * @param  boolean? $set Optional. Omit to use as getter. Whether to list dot files.
	 * @return boolean|ControllerFileBrowser If used as a setter, this instance to enable method chaining. If used as a getter, whether we're currently listing dot files in the browser.
	 */
	public function dotFiles( $set = false ) {
		if( func_num_args() === 0 ) {
			return $this->dotFiles;
		}
		$this->dotFiles = !!$set;
		return $this;
	}
	
	/**
	 * Gets or sets whether we're allowing the user to copy files.
	 * @param  boolean $set Optional. Omit to use as getter. Whether to allow file copying.
	 * @return boolean|ControllerFileBrowser This instance to enable method chaining if used as a setter, otherwise the current value if used as a getter.
	 */
	public function controlCopy( $set = true ) {
		if( func_num_args() === 0 ) {
			return $this->controlCopy;
		}
		$this->controlCopy = !!$set;
		return $this;
	}
	
	/**
	 * Gets or sets whether we're allowing the user to move files.
	 * @param  boolean $set Optional. Omit to use as getter. Whether to allow file moving.
	 * @return boolean|ControllerFileBrowser This instance to enable method chaining if used as a setter, otherwise the current value if used as a getter.
	 */
	public function controlCut( $set = true ) {
		if( func_num_args() === 0 ) {
			return $this->controlMove;
		}
		$this->controlMove = !!$set;
		return $this;
	}
	
	/**
	 * Gets or sets whether we're displaying the delete button.
	 * @param  boolean $set Optional. Omit to get the current value.
	 * @return ControllerFileBrowser|boolean This instance if used as a setter, the current value if used as a getter.
	 */
	public function controlDelete( $set = true ) {
		if( !func_num_args() ) {
			return $this->controlDelete;
		}
		$this->controlDelete = $set;
		return $this;
	}
	
	/**
	 * Gets or sets whether we're displaying the rename button.
	 * @param  boolean $set Optional. Omit to get the current value.
	 * @return ControllerFileBrowser|boolean This instance if used as a setter, the current value if used as a getter.
	 */
	public function controlRename( $set = true ) {
		if( !func_num_args() ) {
			return $this->controlRename;
		}
		$this->controlRename = $set;
		return $this;
	}
	
	/**
	 * Gets or sets whether we're displaying the refresh button.
	 * @param  boolean $set Optional. Omit to get the current value.
	 * @return ControllerFileBrowser|boolean This instance if used as a setter, the current value if used as a getter.
	 */
	public function controlRefresh( $set = true ) {
		if( !func_num_args() ) {
			return $this->controlRefresh;
		}
		$this->controlRefresh = $set;
		return $this;
	}
	
	/**
	 * Gets or sets whether we're displaying the upload button.
	 * @param  boolean $set Optional. Omit to get the current value.
	 * @return ControllerFileBrowser|boolean This instance if used as a setter, the current value if used as a getter.
	 */
	public function controlUpload( $set = true ) {
		if( !func_num_args() ) {
			return $this->controlUpload;
		}
		$this->controlUpload = $set;
		return $this;
	}
	
	/**
	 * Sets additional control buttons HTML
	 * @param  string $html 
	 * @return ControlFileBrowser This instance to enable method chaining.
	 */
	public function setControl( $html ) {
		$this->controlCustom = $html;
		return $this;
	}
	
	
	/**
	 * Gets information on the files in the given directory, such as name, size, etc.
	 * Sizes of directories are skipped. They can be requested via AJAX calling
	 * {@link #action_size()}.
	 * @param  string $dir Path to the directory whose files to get. May be either absolute or relative to the jail.
	 * @return array       Array of files in the directory providing additional information.
	 */
	public function getFiles( $dir ) {
		$files = array();
		
		// First jail the directory who's files to get. If it lies outside the jail, all its contained files will in particular.
		// This also allows us to use paths relative to the jail.
		if( !empty($this->jail) ) {
			$dir = jailpath($this->jail, $dir);
		}
		
		// Get a list of all files in the directory
		$paths = glob("$dir/*");
		
		// Including dot files (such as .htaccess) if set
		if( $this->dotFiles ) {
			$paths = array_merge(glob("$dir/.*"));
		}
		
		// Retrieve the file info
		foreach( $paths as $path ) {
			if( endsWith($path, '.') or endsWith($path, '..') ) continue;
			
			$path = preg_replace('/[\\\\\/]+/', DS, $path);
			
			$pathparts = explode(DS, $path);
			$file = array();
			$name = $pathparts[count($pathparts) - 1];
			$file['id']     = $name;
			$file['name']   = $name;
			$file['is_dir'] = is_dir($path);
			if( is_dir($path) ) {
				$file['size'] = '';
			}
			else {
				$file['size'] = filesize($path);
			}
			$file['perms'] = $this->getPermsString($path);
			
			$files[] = $file;
		}
		
		// Sort the files. First directories, then regular files. Both alphabetically.
		$tmp1 = $tmp2 = array();
		foreach( $files as $file ) {
			if( $file['is_dir'] ) {
				$tmp1[] = $file;
			}
			else {
				$tmp2[] = $file;
			}
		}
		// The natsort works with our standardized 2-dimensional arrays as well, but converts them into strings. We'll simply repress the generated notice and it should work.
		@natsort(array_reverse($tmp1)); @natsort(array_reverse($tmp2));
		$files = array_merge($tmp1, $tmp2);
		
		return $files;
	}
	
	public function getPermsString( $file ) {
		if( !file_exists($file) ) {
			return '0000';
		}
		return substr(sprintf('%o', fileperms($file)), -4);
	}
	
	
	protected function redirectForbidden( ) {
		$_SESSION['error'] = array(
			'title' => 'Insufficient permissions',
			'msg'   => 'You lack permission to browse/modify the server\'s file system.',
			'level' => 'error',
		);
		redirect(DIAMONDMVC_URL . '/error', 403);
	}
	
}
