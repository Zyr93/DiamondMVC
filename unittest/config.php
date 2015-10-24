<?php
$config = new Config(dirname(__FILE__) . DS . 'config.ini.php');
?>
<div id="test-config" class="unittest">
	<div class="container-fluid">
		<div class="row">
			<div class="col-xs-3">[Category][Property]</div>
			<div class="col-xs-3">Parsed value</div>
			<div class="col-xs-3">Expected value</div>
			<div class="col-xs-3">Passed</div>
		</div>
		<div class="row">
			<div class="col-xs-3">[HELLO]</div>
			<div class="col-xs-3"><?= $config->get('HELLO') ?></div>
			<div class="col-xs-3">WORLD</div>
			<div class="col-xs-3">
				<?php if( $config->get('HELLO') === 'WORLD' ) : { ?>
					<i class="fa fa-check">&nbsp;</i>
				<?php } else : { ?>
					<i class="fa fa-times">&nbsp;</i>
				<?php } endif ?>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-3">[FOO][FOO]</div>
			<div class="col-xs-3"><?= $config->get('FOO', 'FOO') ?></div>
			<div class="col-xs-3">FOO</div>
			<div class="col-xs-3">
				<?php if( $config->get('FOO', 'FOO') === 'FOO' ) : { ?>
					<i class="fa fa-check">&nbsp;</i>
				<?php } else : { ?>
					<i class="fa fa-times">&nbsp;</i>
				<?php } endif ?>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-3">[BAR][FOO]</div>
			<div class="col-xs-3"><?= $config->get('FOO', 'BAR') ?></div>
			<div class="col-xs-3">BAR</div>
			<div class="col-xs-3">
				<?php if( $config->get('FOO', 'BAR') === 'BAR' ) : { ?>
					<i class="fa fa-check">&nbsp;</i>
				<?php } else : { ?>
					<i class="fa fa-times">&nbsp;</i>
				<?php } endif ?>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-3">[BAZ][FOO]</div>
			<div class="col-xs-3"><?= $config->get('FOO', 'BAZ') ?></div>
			<div class="col-xs-3">BAZ</div>
			<div class="col-xs-3">
				<?php if( $config->get('FOO', 'BAZ') === 'BAZ' ) : { ?>
					<i class="fa fa-check">&nbsp;</i>
				<?php } else : { ?>
					<i class="fa fa-times">&nbsp;</i>
				<?php } endif ?>
			</div>
		</div>
	</div>
</div>
