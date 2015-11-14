<?php
defined('DIAMONDMVC') or die;
$ini = (new ini())->read(dirname(__FILE__) . DS . 'config.ini.php')->set('SET', 'set');
?>
<div id="test-config" class="unittest">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>[Category][Property]</th>
				<th>Original value</th>
				<th>Parsed value</th>
				<th>Expected value</th>
				<th>Passed</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>[HELLO]</td>
				<td>WORLD</td>
				<td><?= $ini->get('HELLO') ?></td>
				<td>WORLD</td>
				<td>
					<?php if( $ini->get('HELLO') === 'WORLD' ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td>[FOO][FOO]</td>
				<td>FOO</td>
				<td><?= $ini->get('FOO', 'FOO') ?></td>
				<td>FOO</td>
				<td>
					<?php if( $ini->get('FOO', 'FOO') === 'FOO' ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td>[BAR][FOO]</td>
				<td>BAR</td>
				<td><?= $ini->get('FOO', 'BAR') ?></td>
				<td>BAR</td>
				<td>
					<?php if( $ini->get('FOO', 'BAR') === 'BAR' ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td>[DEFAULT]</td>
				<td></td>
				<td><?= $ini->def('DEFAULT', 'default') ?></td>
				<td>default</td>
				<td>
					<?php if( $ini->def('DEFAULT', 'default') === 'default' ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
			<tr>
				<td>[SET]</td>
				<td></td>
				<td><?= $ini->get('SET') ?></td>
				<td>set</td>
				<td>
					<?php if( $ini->get('SET') === 'set' ) : { ?>
						<i class="fa fa-check" style="color:green"></i>
					<?php } else : { ?>
						<i class="fa fa-times" style="color:red"></i>
					<?php } endif ?>
				</td>
			</tr>
		</tbody>
	</table>
</div>
