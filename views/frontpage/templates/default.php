<?php
/**
 * @package DiamondMVC official website & demo package
 * @author  Zyr <zyrius@live.com>
 * @license Public Domain
 */
defined('DIAMONDMVC') or die;

// Instead of using an internationalization file, it seems much more applicable to write the translations of an article
// directly into its source file. The i18n class still provides the primary and two alternative languages - if specified.
// NOTE: currently the Diamond does not feature browser preference detection. However, it may be a feature of the future.
$lang     = i18n::getCurrentLanguage();
$altLang1 = i18n::getFirstAlternativeLanguage();
$altLang2 = i18n::getSecondAlternativeLanguage();
?>
<div class="view" id="view-frontpage">
	<div class="fp-note" id="fp-note-welcome">
		<h1>
			Welcome to<br>
			the <span class="blue">Diamond</span>!<br>
			<small>The Linux of Web Server Platforms</small>
		</h1>
	</div>
	
	<div class="fp-note" id="fp-note-short-desc">
		<p>
			The <span class="blue">Diamond</span> is a
			<a href="https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller" class="external" target="_blank">model-view-controller</a> <i class="fa fa-external-link"></i>,
			not a 
			<a href="https://en.wikipedia.org/wiki/Content_management_system" class="external" target="_blank">content management system</a> <i class="fa fa-external-link"></i>.
		</p>
		<p>
			This means the <span class="blue">Diamond</span> does not feature a fancy graphical user interface or any kind of
			<a href="https://en.wikipedia.org/wiki/WYSIWYG" class="external" target="_blank">WYSIWYG</a> <i class="fa fa-external-link"></i>
			editor. Instead we provide you with a variety of standardized tools to speed up your development process.
		</p>
		<p>
			Consider CMS platforms such as
			<a href="https://wordpress.org/" class="external" target="_blank">Wordpress</a> <i class="fa fa-external-link"></i>,
			<a href="https://www.joomla.org/" class="external" target="_blank">Joomla!</a> <i class="fa fa-external-link"></i>,
			<a href="https://typo3.org/" class="external" target="_blank">Typo3</a> <i class="fa fa-external-link"></i>
			to be your Windows of web server platforms while <span class="blue">DiamondMVC</span> is your Linux! Our target user
			is not the unexperienced end user who simply wishes to quickly set up a website, but the seasoned web developer in need
			of a powerful framework to kickstart a new web service.
		</p>
	</div>
	
	<div class="fp-note" id="fp-note-features-short" aria-hidden="true">
		<p>
			The <span class="blue">Diamond</span> offers a number of useful features:
		</p>
		
		<ul>
			<li>Create a new page and consequentially a new action in 3 simple steps!</li>
			<li>Exploit a powerful and secure database interface abstraction class</li>
			<li>Easily manage any kind of extension!</li>
			<li>Directly interfere with the procedures of the system by handling events through plugins</li>
			<li>
				Check out <a href="<?= DIAMONDMVC_URL ?>/docs">the documentation</a>
				to learn more about the <span class="blue">Diamond</span>
			</li>
		</ul>
	</div>
	
	<div class="fp-note" id="fp-note-features-long">
		<p>
			The <span class="blue">Diamond</span> offers a number of useful features:
		</p>
		
		<ul>
			<li>Create a new page and consequentially a new action in 3 simple steps!</li>
			<li>Exploit a powerful and secure database interface abstraction class</li>
			<li>Build a next gen website with an in-development full AJAX framework</li>
			<li>
				Install any kind of extension! The <span class="blue">Diamond</span> trusts you as the author of extensions and
				does not predefine extension types. Anything can be installed anywhere. The <span class="blue">Diamond</span>
				automatically saves which files it extracted during the installation process to allow you to easily remove
				installed extensions again.
			</li>
			<li>
				Stock assets include
				<a href="http://getbootstrap.com/" class="external" target="_blank">Twitter Bootstrap</a> <i class="fa fa-external-link"></i>,
				<a href="https://fortawesome.github.io/Font-Awesome/" class="external" target="_blank">FontAwesome</a> <i class="fa fa-external-link"></i>,
				<a href="https://jquery.com/" class="external" target="_blank">jQuery</a> <i class="fa fa-external-link"></i>, and
				<a href="http://requirejs.org/" class="external" target="_blank">RequireJS</a> <i class="fa fa-external-link"></i>
			</li>
			<li>Manage the web server's files with the built in file browser! Frankly, it's currently only used to upload and select extensions for installation.</li>
			<li>Directly influence aspects of the program flow by handling events using plugins</li>
			<li>Quickly switch between default templates through the configuration file (and in future through the configuration menu!)</li>
			<li>The <span class="blue">Diamond</span> provides a Snippet class standardizing parametralization and customization as well as data passing for clean access</li>
			<li>
				The <span class="blue">Diamond's</span> output buffer magic allows us to link stylesheets and JavaScript modules
				from everywhere within the system and still have them nicely organized in the &lt;head&gt; tag
			</li>
			<li>An elaborate logging system allows you to capture all sorts of data for easier debugging, including time of message and stack trace</li>
			<li>
				Check out <a href="<?= DIAMONDMVC_URL ?>/docs">the documentation</a>
				to learn more about the <span class="blue">Diamond</span>
			</li>
		</ul>
	</div>
</div>
