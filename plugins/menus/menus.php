<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Adds the main menu to the websites. Could be made much more elaborate, but for the
 * sake of this demo we do not require any more complex menu establishment.
 */
defined('DIAMONDMVC') or die();

class PluginMenus extends Plugin {
	
	public function __construct( $db = null ) {
		parent::__construct('menus', $db);
		DiamondMVC::instance()->on('controller::action', $this);
	}
	
	
	public function handle( $evt, $ctrl = null ) {
		// Special menu for the backend.
		if( $ctrl->getName() === 'system' ) {
			$ctrl->appendModule('navigation', $this->generateBackendMenu($ctrl));
		}
		// Special menu for the documentation
		else if( $ctrl->getName() === 'docs' ) {
			$ctrl->appendModule('navigation', $this->generateDocsMenu($ctrl));
		}
		// Generic menu for all other pages.
		else {
			$ctrl->appendModule('navigation', $this->generateMainMenu($ctrl));
		}
	}
	
	protected function generateMainMenu( $ctrl ) {
		$lang = i18n::load('diamondmvc');
		
		$result = new ModuleNavbar($ctrl);
		$result->addLinkLeft ($lang->get('HOME'),          DIAMONDMVC_URL)
			   ->addLinkLeft ($lang->get('DOCUMENTATION'), DIAMONDMVC_URL . '/docs')
			   ->addLinkRight($lang->get('ABOUT'),         DIAMONDMVC_URL . '/about');
		return $result;
	}
	
	protected function generateBackendMenu( $ctrl ) {
		$lang = i18n::load('diamondmvc-backend');
		
		$result = new ModuleNavbar($ctrl);
		$result->addLinkLeft ($lang->get('HOME'),          DIAMONDMVC_URL . '/system')
			   ->addLinkLeft ($lang->get('USERS'),         DIAMONDMVC_URL . '/system/users')
			   ->addLinkLeft ($lang->get('PERMISSIONS'),   DIAMONDMVC_URL . '/system/permissions')
			   ->addMenuLeft ($lang->get('INSTALLATIONS'))
			    	->addLink($lang->get('OVERVIEW'),      DIAMONDMVC_URL . '/system/installations')
			    	->addLink($lang->get('INSTALL'),       DIAMONDMVC_URL . '/system/install')
			    	->back()
			   ->addLinkLeft ($lang->get('PLUGINS'),       DIAMONDMVC_URL . '/system/plugins');
		return $result;
	}
	
	protected function generateDocsMenu( $ctrl ) {
		$lang = i18n::load('diamondmvc');
		
		$result = new ModuleNavbar($ctrl);
		$result->addLinkLeft($lang->get('HOME'), DIAMONDMVC_URL . '/docs')
			   ->addMenuLeft($lang->get('GUIDES'))
			   		->addLink($lang->get('GETTING_STARTED'),      DIAMONDMVC_URL . '/docs/getting-started')
			   		->addLink($lang->get('CONTROLLERS'),          DIAMONDMVC_URL . '/docs/controllers')
			   		->addLink($lang->get('VIEWS'),                DIAMONDMVC_URL . '/docs/views')
			   		->addLink($lang->get('MODELS'),               DIAMONDMVC_URL . '/docs/models')
			   		->addLink($lang->get('INTERNATIONALIZATION'), DIAMONDMVC_URL . '/docs/i18n')
			   		->addLink($lang->get('PERMISSIONS'),          DIAMONDMVC_URL . '/docs/permissions')
			   		->addLink($lang->get('EXTENSIONS'),           DIAMONDMVC_URL . '/docs/extensions')
			   		->back();
		return $result;
	}
	
}
