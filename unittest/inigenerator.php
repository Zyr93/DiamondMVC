<?php
$gen = new IniGenerator();
$gen->set('Hello', 'World!')->set('foo', 'bar', 'baz');

$inistring = $gen . '';

$ini = parse_ini_string($inistring, true);
?>
<div id="test-inigenerator" class="unittest">
	<div class="container-fluid">
		<div class="row test">
			<div class="col-xs-3">INI key</div>
			<div class="col-xs-3">Original value</div>
			<div class="col-xs-3">Parsed value</div>
			<div class="col-xs-3">Passed</div>
		</div>
		<div class="row test">
			<div class="col-xs-3">Hello</div>
			<div class="col-xs-3"><?= $gen->get('Hello') ?></div>
			<div class="col-xs-3"><?= $ini['Hello'] ?></div>
			<div class="col-xs-3">
				<?php if( $gen->get('Hello') === $ini['Hello'] ) : { ?>
					<i class="fa fa-check" style="color:green"></i>
				<?php } else : { ?>
					<i class="fa fa-times" style="color:red"></i>
				<?php } endif ?>
			</div>
		</div>
		<div class="row test">
			<div class="col-xs-3">foo</div>
			<div class="col-xs-3"><?= $gen->get('foo', 'bar') ?></div>
			<div class="col-xs-3"><?= $ini['bar']['foo'] ?></div>
			<div class="col-xs-3">
				<?php if( $gen->get('foo', 'bar') === $ini['bar']['foo'] ) : { ?>
					<i class="fa fa-check" style="color:green"></i>
				<?php } else : { ?>
					<i class="fa fa-times" style="color:red"></i>
				<?php } endif ?>
			</div>
		</div>
	</div>
</div>
