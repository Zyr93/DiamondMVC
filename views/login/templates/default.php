<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * @param View $this
 */
defined('DIAMONDMVC') or die();

$lang  = i18n::load('diamondmvc');
$error = $this->getParam('error');
?>
<div id="view-login" class="container-fluid">
	<?php if( !empty($error) ) : ?>
		<div class="alert alert-danger alert-dismissable" role="alert">
			<button class="close" type="button" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<div class="title"><?= $lang->get('ERROR') ?></div>
			<div class="message"><?= $error ?></div>
		</div>
	<?php else : ?>
		<p><?= $lang->get('ERROR_MESSAGE', 'ControllerLogin') ?></p>
	<?php endif ?>
	<form action="<?php echo DIAMONDMVC_URL ?>/login" method="post">
		<input type="hidden" name="action" value="login">
		<div class="row">
			<div class="col-xs-3 col-md-2"><label for="username"><?= $lang->get('EMAIL') ?>:</label></div> <div class="col-xs-9"><input type="text" name="username" id="username"></div>
		</div>
		<div class="row">
			<div class="col-xs-3 col-md-2"><label for="password"><?= $lang->get('PASSWORD') ?>:</label></div> <div class="col-xs-9"><input type="password" name="password"></div><br>
		</div>
		<div style="margin-left:20px;"><input type="submit" name="login" class="btn btn-primary" value="<?= $lang->get('LOGIN') ?>"></div>
	</form>
</div>
