<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

$result = $this->controller->getResult();
$installations = $result['installations'];

$lang = i18n::load('diamondmvc-backend');

$this->addStylesheet('view-installations.css');
$this->addScript('./installations');

$flipflop = true;
?>
<div class="view" id="view-system">
	<h2 class="page-header"><?= $lang->get('TITLE', 'ControllerSystem.Installations') ?></h2>
	
	<div class="buttons">
		<div class="pull-right">
			<a href="<?= DIAMONDMVC_URL ?>/system/install" class="btn btn-primary"><?= $lang->get('INSTALL') ?></a>
		</div>
		<div class="clearfix"></div>
	</div>
	
	<table class="table view-installations">
		<thead>
			<tr>
				<th><?= $lang->get('NAME') ?></th>
				<th><?= $lang->get('VERSION') ?></th>
				<th><?= $lang->get('UPDATE', 'ControllerSystem.Installations') ?></th>
			</tr>
			<tr>
				<th colspan="3"><?= $lang->get('DESCRIPTION') ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach( $installations as $meta => $inst ) : { ?>
				<tr class="part1 <?= $flipflop ? 'odd' : 'even' ?>">
					<td class="col-name">
						<a href="<?= DIAMONDMVC_URL ?>/system/installation?id=<?= $meta ?>"><?= $inst->getName() ?></a>
					</td>
					<td class="col-version"><?= $inst->getVersion() ?></td>
					<td class="col-has-update">
						<?php if( InstallationManager::hasUpdate($inst) ) : { ?>
							<a href="<?= DIAMONDMVC_URL ?>/system/update?id=<?= $meta ?>">
								<i class="fa fa-arrow-circle-o-up">&nbsp;</i>
							</a>
						<?php } endif ?>
					</td>
				</tr>
				<tr class="part2 <?= $flipflop ? 'odd' : 'even' ?>">
					<td class="col-desc" colspan="3"><?= $inst->getDescription() ?></td>
				</tr>
				<?php $flipflop = !$flipflop ?>
			<?php } endforeach ?>
		</tbody>
	</table>
</div>
