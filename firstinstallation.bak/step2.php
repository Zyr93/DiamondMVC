<?php
defined('DIAMONDMVC') or die;
?>
<!DOCTYPE html>
<html>
<head>
	<title>DiamondMVC Installation - Report</title>
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
			<small>Report</small>
		</h2>
	</div>
	<div id="view-firstinstall" class="view">
		<?php if( $success ) : { ?>
			<?= generateNotificationHTML('Success!', 'The configuration file has been set up and the database prepared. You can now start exploring the system!', 'success') ?>
			
			<p>
				There remains one last thing for you to do. Click this button to proceed with the final step: deleting the installation directory to finalize your
				installation. You will be immediately forwarded to the frontpage of your new website. That's when you know everything went according to plan and you
				are good to go to explore your new toy.
			</p>
			
			<a href="<?= DIAMONDMVC_URL ?>/firstinstallation/?step=3" class="btn btn-primary">Finalize installation</a>
		<?php } else : { ?>
			<?php foreach( $errors as $error ) : { ?>
				<?= generateNotificationHTML('Error:', $error, 'error') ?>
			<?php } endforeach ?>
		<?php } endif ?>
	</div>
</body>
</html>
