<?php
/**
 * @package DiamondMVC official website & demo package
 * @author  Zyr <zyrius@live.com>
 * @license Public Domain
 */
defined('DIAMONDMVC') or die;

$tplTitle  = $this->title();
$ctrlTitle = $this->controller->getTitle();
if( !empty($ctrlTitle) ) {
	$title = "$ctrlTitle - $tplTitle";
}
else {
	$title = $tplTitle;
}

$this->addStylesheet('/templates/default/css/default.css');
$this->addStylesheet('frontpage.css');
$this->addScript('../templates/default/js/client');
?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $title ?></title>
	<?= $this->getDefaultHead() ?>
	<!--[if gte IE 9]>
		<style type="text/css">
		    .gradient {
		    	filter: none;
		    }
		</style>
	<![endif]-->
	<link rel="icon" href="<?= DIAMONDMVC_URL ?>/assets/images/favicon.gif" type="image/x-icon">
</head>
<body>
	<div id="master-wrapper" class="frontpage-tpl">
		<div id="super-wrapper">
			<div id="header-wrapper">
				<div id="header-inner-wrapper">
					<div id="header">
						<h2>
							<a href="<?= DIAMONDMVC_URL ?>"><?= $this->title() ?></a>
							<small>The Linux of Web Server Platforms</small>
						</h2>
					</div>
				</div>
			</div>
			<div id="navigation-wrapper">
				<?= $this->getModule('navigation', 0) ?>
			</div>
			<div id="body-wrapper">
				<div id="body-inner-wrapper">
					<div id="content-wrapper">
						<div id="messages">
							<?php echo $this->controller->getMessagesHTML() ?>
						</div>
						<div id="content">
							<?= $this->getBody() ?>
						</div>
					</div>
				</div>
			</div>
			<div id="footer-wrapper">
				<div id="footer-inner-wrapper">
					<div id="footer">
						<div id="copyright">Copyright &copy; 2015 Wings of Dragons, Germany</div>
						<div id="links">
							<a href="http://www.wings-of-dragons.com/about">About Wings of Dragons</a> |
							<a href="<?= DIAMONDMVC_URL ?>/legal#disclaimer">Disclaimer</a> |
							<a href="<?= DIAMONDMVC_URL ?>/legal#privacy-policy">Privacy Policy</a> |
							<a href="<?= DIAMONDMVC_URL ?>/legal#license">License</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
</html>
