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

class ControllerSystem extends Controller {
	
	public function __construct( $db = null ) {
		parent::__construct('system', $db);
	}
	
	protected function action_main( ) {
		if( !Permissions::has('sys_access') ) {
			return $this->redirectForbidden();
		}
	}
	
	protected function action_users( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_users_view') ) {
			return $this->redirectForbidden();
		}
	}
	
	protected function action_permissions( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_perms_view') ) {
			return $this->redirectForbidden();
		}
	}
	
	protected function action_installations( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_installations_view') ) {
			return $this->redirectForbidden();
		}
		
		$lang = i18n::load('diamondmvc-backend');
		$this->title = $lang->get('TITLE', 'ControllerSystem.Installations');
		
		$insts = array();
		
		$dir = DIAMONDMVC_ROOT . DS . 'registry';
		foreach( glob("$dir/*.json") as $orig ) {
			$meta = str_replace('.json', '', $orig);
			$meta = substr($meta, strlen($dir) + 1);
			
			$json = json_decode(file_get_contents($orig), true);
			
			$inst = Installation::getInstallation($json);
			$insts[$meta] = $inst;
		}
		
		$insts = $this->sortInstallations($insts);
		
		$this->result = array('success' => true, 'installations' => $insts);
	}
	
	protected function sortInstallations( $installations ) {
		// Collect the data used for sorting, i.e. installation name and meta as key
		$tmp = array();
		foreach( $installations as $meta => $inst ) {
			$tmp[$meta] = $inst->getName();
		}
		
		// natsort will do the dirty work for us, then we'll simply use the key to build the correct result
		natsort($tmp);
		
		$result = array();
		foreach( $tmp as $meta => $name ) {
			$result[$meta] = $installations[$meta];
		}
		return $result;
	}
	
	protected function action_installation( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_installations_view') ) {
			return $this->redirectForbidden();
		}
		
		$lang = i18n::load('diamondmvc-backend');
		$this->title = $lang->get('TITLE', 'ControllerSystem.Installation');
		
		if( !isset($_REQUEST['id']) ) {
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_MISSING_ARGUMENTS'));
			return;
		}
		
		$path = jailpath(DIAMONDMVC_ROOT . DS . 'registry', $_REQUEST['id'] . '.json');
		
		if( !file_exists($path) ) {
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_NO_META', 'ControllerSystem.Installation'));
			return;
		}
		
		$json = json_decode(file_get_contents($path), true);
		$meta = Installation::getInstallation($json);
		
		$this->result = array('success' => true, 'meta' => $meta, 'id' => $_REQUEST['id']);
	}
	
	protected function action_install( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_installation_install') ) {
			return $this->redirectForbidden();
		}
		
		$lang = i18n::load('diamondmvc-backend');
		$this->title  = $lang->get('TITLE', 'ControllerSystem.Install');
		
		$ctrl = new ControllerFileBrowser($this->db);
		$ctrl->jail(DIAMONDMVC_ROOT . '/uploads')->actionUrl(DIAMONDMVC_URL . '/installation-helper')->files($ctrl->getFiles('/'));
		$snippet = $ctrl->getSnippet('/')->read();
		
		$result = array();
		$result['filebrowser'] = $snippet;
		$this->result = $result;
	}
	
	protected function action_realinstall( ) {
		if( !Permissions::has('sys_installation_install') ) {
			return $this->redirectForbidden();
		}
		
		$lang = i18n::load('diamondmvc-backend');
		
		if( !isset($_REQUEST['base']) or !isset($_REQUEST['ids']) ) {
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_MISSING_ARGUMENTS'));
			return;
		}
		
		$base = $_REQUEST['base'];
		$ids  = $_REQUEST['ids'];
		
		$result  = array();
		$success = true;
		
		$jail = realpath(DIAMONDMVC_ROOT . '/uploads');
		$ctrl = (new ControllerFileBrowser($this->db))->jail($jail);
		foreach( $ids as $id ) {
			$path = $ctrl->buildPath($base, $id);
			if( $path === $jail ) {
				logMsg('DiamondMVC Installation: invalid file "' . $jail . DS . $base . DS . $file . '", skipping', 9, false);
				$success     = false;
				$result[$id] = false;
			}
			else {
				try {
					InstallationManager::install($path);
					$result[$id] = true;
				}
				catch( Exception $ex ) {
					logMsg('DiamondMVC Installation: installation failed with exception "' . $ex->getMessage() . '"', 9, false);
					$success     = false;
					$result[$id] = false;
				}
			}
		}
		
		$this->result = array('success' => $success, 'details' => $result);
	}
	
	protected function action_uninstall( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_installation_uninstall') ) {
			return $this->redirectForbidden();
		}
		
		$lang = i18n::load('diamondmvc-backend');
		
		if( !isset($_REQUEST['id']) or empty($_REQUEST['id']) ) {
			$this->addMessage('Whoops!', $lang->get('ERROR_MISSING_ARGUMENTS'), 'error');
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_MISSING_ARGUMENTS'));
			return;
		}
		
		$path = jailpath(DIAMONDMVC_ROOT . DS . 'registry', $_REQUEST['id'] . '.json');
		if( !is_file($path) ) {
			$this->addMessage('Whoops!', $lang->get('ERROR_NO_META', 'ControllerSystem.Installation', 'error'));
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_NO_META', 'ControllerSystem.Installation'));
			return;
		}
		
		try {
			InstallationManager::uninstall($path);
			$this->result = array('success' => true);
		}
		catch( Exception $ex ) {
			$this->result = array('success' => false, 'msg' => $ex->getMessage());
			$this->addMessage('Whoops!', 'An exception occurred: ' . $ex->getMessage(), 'error');
			logMsg('DiamondMVC: failed to uninstall installation ' . $_REQUEST['id'] . ' with exception: ' . $ex->getMessage(), 9, false);
		}
	}
	
	protected function action_update( ) {
		if( !Permissions::has('access') or !Permissions::has('sys_installation_install') ) {
			return $this->redirectForbidden();
		}
		
		$lang = i18n::load('diamondmvc-backend');
		
		if( !isset($_REQUEST['id']) or empty($_REQUEST['id']) ) {
			$this->addMessage('Whoops!', $lang->get('ERROR_MISSING_ARGUMENTS'), 'error');
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_MISSING_ARGUMENTS'));
			return;
		}
		
		$path = jailpath(DIAMONDMVC_ROOT . DS . 'registry', $_REQUEST['id'] . '.json');
		if( !is_file($path) ) {
			$this->addMessage('Whoops!', $lang->get('ERROR_NO_META', 'ControllerSystem.Installation', 'error'));
			$this->result = array('success' => false, 'msg' => $lang->get('ERROR_NO_META', 'ControllerSystem.Installation'));
			return;
		}
		
		try {
			$id = InstallationManager::update($path);
			$this->result = array('success' => true, 'id' => $id);
		}
		catch( Exception $ex ) {
			$this->result = array('success' => false, 'msg' => $ex->getMessage());
			$this->addMessage('Whoops!', 'An exception occurred: ' . $ex->getMessage(), 'error');
			logMsg('DiamondMVC: failed to update installation ' . $_REQUEST['id'] . ' with exception (' . $ex->getCode() . '): ' . $ex->getMessage(), 9, false);
		}
	}
	
	protected function action_plugins( ) {
		if( !Permissions::has('sys_access') or !Permissions::has('sys_plugins_view') ) {
			return $this->redirectForbidden();
		}
	}
	
	
	protected function redirectForbidden( ) {
		$lang = i18n::load('diamondmvc-backend');
		
		$_SESSION['error'] = array(
			'title' => $lang->get('ERROR_RESTRICTED_ACCESS'),
			'msg'   => $lang->get('ERROR_INSUFFICIENT_PERMISSIONS'),
			'level' => 'error',
		);
		redirect(DIAMONDMVC_URL . '/error');
	}
	
}
