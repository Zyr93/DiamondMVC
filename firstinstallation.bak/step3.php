<?php
defined('DIAMONDMVC') or die;
?>
<!DOCTYPE html>
<html>
<head>
	<title>DiamondMVC Installation - Finalization</title>
	<link rel="stylesheet" href="<?= DIAMONDMVC_URL ?>/assets/bootstrap/css/bootstrap.min.css">
	<link rel="stylesheet" href="<?= DIAMONDMVC_URL ?>/firstinstallation/style.css">
	<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
	<script src="<?= DIAMONDMVC_URL ?>/firstinstallation/script.js"></script>
	<script src="<?= DIAMONDMVC_URL ?>/assets/bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	<div id="header">
		<h2>
			Installation
			<small>Finalization</small>
		</h2>
	</div>
	<div id="view-firstinstall" class="view">
		<p>
			The fact that you're seeing this means the Diamond failed to delete the <code>/firstinstallation</code>
			directory in its root. In order to proceed you must manually delete this directory. Then you may
			continue to the frontpage of your website either by directly entering it into the URL bar of your
			browser or by simply clicking the following button.
		</p>
		
		<a href="<?= DIAMONDMVC_URL ?>" class="btn btn-primary">Proceed to frontpage</a>
	</div>
</body>
</html>
