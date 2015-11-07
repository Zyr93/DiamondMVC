<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

$result = $this->controller->getResult();

$lang = i18n::load('diamondmvc-backend');

$this->addStylesheet('installation.css');

if( $result['success'] ) {
	$meta   = $result['meta'];

	$name        = $meta->getName();        if( empty($name) )        $name        = $lang->get('UNNAMED_EXTENSION', 'ControllerSystem.Installation');
	$description = $meta->getDescription(); if( empty($description) ) $description = $lang->get('NO_DESC_AVAILABLE', 'ControllerSystem.Installation');
	$author      = $meta->getAuthor();      if( empty($author) )      $author      = strToLower($lang->get('UNKNOWN'));
	$distUrl     = $meta->getDistUrl();     if( empty($distUrl) )     $distUrl     = strToLower($lang->get('STR_NONE'));
	$updateUrl   = $meta->getUpdateUrl();   if( empty($updateUrl) )   $updateUrl   = strToLower($lang->get('STR_NONE'));
}
?>
<div class="view view-installation" id="view-system">
	<?php if( !$result['success'] ) : { ?>
		<p><?= $lang->get('ERROR_NO_META', 'ControllerSystem.Installation') ?></p>
	<?php } else : { ?>
		<div class="buttons">
			<a href="<?= DIAMONDMVC_URL ?>/system/uninstall?id=<?= $result['id'] ?>" class="btn btn-danger pull-right"><?= $lang->get('UNINSTALL') ?></a>
			<a href="<?= DIAMONDMVC_URL ?>/system/installations" class="btn btn-primary pull-right"><?= $lang->get('BACK') ?></a>
		</div>
		<h3 class="page-header installation-name">
			<?= $lang->get('TITLE_PRETEXT', 'ControllerSystem.Installation') ?> <?= $name ?>
			<small>
				<span class="installation-version"><?= $meta->getVersion() ?></span><br>
				<span class="installation-copyright"><?= $meta->getCopyright() ?></span>
			</small>
		</h3>
		<div class="clearfix"></div>
		
		<?php if( InstallationManager::hasUpdate($meta) ) : { ?>
			<?= generateNotificationHTML(
				$lang->get('UPDATE_NOTIFICATION_TITLE', 'ControllerSystem.Installation'),
				str_replace('%update-link%', '<a href="' . DIAMONDMVC_URL . '/system/update?id=' . $result['id'] . '">here</a>', $lang->get('UPDATE_NOTIFICATION_BODY', 'ControllerSystem.Installation')),
				'success'
			) ?>
		<?php } endif ?>
		
		<pre class="installation-description"><?= $description ?></pre>
		
		<table class="table" id="view-meta">
			<tr>
				<td><?= $lang->get('AUTHOR') ?></td>
				<td class="installation-author"><?= $author ?></td>
			</tr>
			<tr>
				<td><?= $lang->get('DIST_URL', 'ControllerSystem.Installation') ?></td>
				<td class="installation-dist-url"><?= $distUrl ?></td>
			</tr>
			<tr>
				<td><?= $lang->get('UPDATE_URL', 'ControllerSystem.Installation') ?></td>
				<td class="installation-update-url"><?= $updateUrl ?></td>
			</tr>
		</table>
		
		<div class="container-fluid">
			<?php $files = $meta->getFiles() ?>
			<div class="row">
				<div class="col-xs-12 col-sm-6 col-md-4">
					<ul>
						<?php for( $i = 0; $i < count($files) / 3; ++$i ) : { ?>
							<li><?= $files[$i] ?></li>
						<?php } endfor ?>
					</ul>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-4">
					<ul>
						<?php for( $i = floor(count($files) / 3 + 1); $i < count($files) * 2 / 3; ++$i ) : { ?>
							<li><?= $files[$i] ?></li>
						<?php } endfor ?>
					</ul>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-4">
					<ul>
						<?php for( $i = floor(count($files) * 2 / 3 + 1); $i < count($files); ++$i ) : { ?>
							<li><?= $files[$i] ?></li>
						<?php } endfor ?>
					</ul>
				</div>
			</div>
		</div>
	<?php } endif ?>
</div>
