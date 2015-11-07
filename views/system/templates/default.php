<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Backend landing page. This page is to feature a dashboard with the most important
 * overview functions. Ideally I'd like the system to feature methods to allow extensions
 * to add more widgets to the dashboard.
 */
defined('DIAMONDMVC') or die();

$nav = new ModuleNavigation($this->controller, 'long');
$nav->addLink('Users',         DIAMONDMVC_URL . '/system/users')
	->addLink('Permissions',   DIAMONDMVC_URL . '/system/permissions')
	->addLink('Installations', DIAMONDMVC_URL . '/system/installations')
	->addLink('Plugins',       DIAMONDMVC_URL . '/system/plugins');
?>
<div class="view" id="view-system">
	<div class="dashboard">
		<!-- TODO: dashboard showing various interesting things like available updates, registered users, etc. -->
	</div>
	<?= $nav ?>
</div>
