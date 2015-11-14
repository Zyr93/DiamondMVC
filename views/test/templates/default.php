<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die;

$tests = $this->controller->getResult();

$lang = i18n::load('diamondmvc-backend');
?>
<div id="view-unittest-overview" class="view">
	<p><?= $lang->get('PARAGRAPH1', 'ControllerTest.Overview') ?></p>
	
	<p><?= $lang->get('PARAGRAPH2', 'ControllerTest.Overview') ?></p>
	
	<table class="table table-striped">
		<thead>
			<th><?= $lang->get('UNITTEST') ?></th>
		</thead>
		<tbody>
			<?php foreach( $tests as $test ) : { ?>
				<tr>
					<td><a href="<?= DIAMONDMVC_URL ?>/test/unit?<?= $test ?>" style="display:block"><?= $test ?></a></td>
				</tr>
			<?php } endforeach ?>
		</tbody>
	</table>
</div>
