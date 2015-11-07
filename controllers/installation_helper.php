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

class ControllerInstallation_Helper extends Controller {
	
	protected $ctrlFileBrowser = null;
	
	
	public function __construct( $db = null ) {
		parent::__construct('installation_helper', $db);
		$this->ctrlFileBrowser = (new ControllerFileBrowser($this->db))->jail(DIAMONDMVC_ROOT . '/uploads');
	}
	
	protected function action_browse( ) {
		$result = $this->ctrlFileBrowser->action('browse');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_size( ) {
		$result = $this->ctrlFileBrowser->action('size');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_rename( ) {
		$result = $this->ctrlFileBrowser->action('rename');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_delete( ) {
		$result = $this->ctrlFileBrowser->action('delete');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_upload( ) {
		$result = $this->ctrlFileBrowser->action('upload');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_mkdir( ) {
		$result = $this->ctrlFileBrowser->action('mkdir');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_copy( ) {
		$result = $this->ctrlFileBrowser->action('copy');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
	protected function action_move( ) {
		$result = $this->ctrlFileBrowser->action('move');
		$this->result = $this->ctrlFileBrowser->result;
		return $result;
	}
	
}
