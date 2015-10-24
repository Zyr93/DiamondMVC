<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * @param View $this
 */
defined('DIAMONDMVC') or die();

$tplTitle  = $this->title();
$ctrlTitle = $this->controller->getTitle();
if( !empty($ctrlTitle) ) {
	$title = "$ctrlTitle - $tplTitle";
}
else {
	$title = $tplTitle;
}

$nosidebar = !$this->countModules('sidebar');
$this->addStylesheet('error.css');
?>
<!DOCTYPE html>
<html>
<head>
	<title><?= $title ?></title>
	<?php echo $this->getDefaultHead() ?>
	<!--[if gte IE 9]>
		<style type="text/css">
		    .gradient {
		    	filter: none;
		    }
		</style>
	<![endif]-->
</head>
<body>
	<div id="master-wrapper"<?php if( $nosidebar ) echo ' class="nosidebar"' ?>>
		<div id="super-wrapper">
			<div id="background-faker"></div>
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
			<div id="body-wrapper">
				<div id="body-inner-wrapper">
					<?php if( !$nosidebar ) : { ?>
						<div id="sidebar-wrapper">
							<div id="sidebar">
								<?php foreach( $this->getSidebar() as $module ) : ?>
									<div class="sidebar-module">
										<?= $module ?>
									</div>
								<?php endforeach ?>
							</div>
						</div>
					<?php } endif ?>
					<div id="content-wrapper">
						<div id="content">
							<?= $this->getBody() ?>
						</div>
						<?php if( count($this->controller->getMessages()) ) : { ?>
							<div id="messages">
								<?php echo $this->controller->getMessagesHTML() ?>
							</div>
						<?php } endif ?>
					</div>
				</div>
			</div>
			<div id="footer-wrapper">
				<div id="footer-inner-wrapper">
					<div id="footer">
						<div id="copyright">Copyright &copy; 2015 Wings of Dragons, Germany</div>
						<div id="links">
							<a href="<?= DIAMONDMVC_URL ?>/about">About DiamondMVC</a> |
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