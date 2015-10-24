<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Extension installation mask. Provides a modal to choose an extension ZIP archive for upload.
 */

$lang = i18n::load('diamondmvc-backend');

// $this->addStylesheet('install.css');
$this->addScript('dropzone');
$this->addScript('./install');

$data    = $this->controller->getResult();
$snippet = $data['filebrowser'];

foreach( $snippet->getStylesheets() as $sheet ) {
	$this->addStylesheet($sheet);
}
foreach( $snippet->getScripts() as $script ) {
	$this->addScript($script);
}
?>
<div class="view view-install" id="view-system">
	<a href="<?= DIAMONDMVC_URL ?>/system/installations" class="btn btn-primary pull-right"><?= $lang->get('RETURN_TO_OVERVIEW', 'ControllerSystem.Install') ?></a>
	<h3 class="page-header"><?= $lang->get('TITLE', 'ControllerSystem.Install') ?></h3>
	<div class="clearfix"></div>
	
	<p><?= $lang->get('PRETEXT', 'ControllerSystem.Install') ?></p>
	
	<noscript>
		<p><?= $lang->get('NOSCRIPT', 'ControllerSystem.Install') ?></p>
	</noscript>
	
	<?= $snippet ?>
	
	<div style="text-align:center">
		<button type="button" class="btn btn-primary" id="btn-launch-installation"><?= $lang->get('START_INSTALLATION', 'ControllerSystem.Install') ?></button>
	</div>
</div>
