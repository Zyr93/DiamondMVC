<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * Extension installation mask. Provides a modal to choose an extension ZIP archive for upload.
 */

$lang = i18n::load('diamondmvc-backend');

$result = $this->controller->getResult();
?>
<div class="view view-install" id="view-system">
	<h3 class="page-header"><?= $lang->get('TITLE', 'ControllerSystem.Uninstall') ?></h3>
	
	<?php if( $result['success'] ) : { ?>
		<p><?= str_replace('%return-link%', '<a href="' . DIAMONDMVC_URL . '/system/installations" class="btn btn-primary">here</a>', $lang->get('SUCCESS', 'ControllerSystem.Uninstall')) ?></p>
	<?php } else : { ?>
		<p><?= $lang->get('FAILURE', 'ControllerSystem.Uninstall') ?></p>
	<?php } endif ?>
</div>

