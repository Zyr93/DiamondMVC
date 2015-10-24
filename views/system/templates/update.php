<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

$result = $this->controller->getResult();

$lang    = i18n::load('diamondmvc-backend');
?>
<div class="view view-update" id="view-system">
	<h2 class="page-header"><?= $lang->get('TITLE', 'ControllerSystem.Update') ?></h2>
	
	<?php if( $result['success'] ) : { ?>
		<p><?= str_replace('%return-link%',
			'<a href="' . DIAMONDMVC_URL . '/system/installation?id=' . $result['id'] . '">here</a>',
			$lang->get('SUCCESS', 'ControllerSystem.Update')
		) ?></p>
	<?php } else: { ?>
		<div class="alert alert-danger" role="alert">
			<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
			<span class="sr-only">Error: </span>
			<?= $result['msg'] ?>
		</div>
	<?php } endif ?>
</div>
