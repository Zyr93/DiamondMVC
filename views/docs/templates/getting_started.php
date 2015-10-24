<?php
$this->addStylesheet('docs.css');
?>
<div class="view view-getting-started" id="view-docs">
	<h2 class="page-header" id="getting-started-with-diamondmvc">Getting started with <span class="blue">DiamondMVC</span></h2>
	
	<div class="toc pull-right">
		<h4>Table of contents</h4>
		<ol>
			<li><a href="#getting-started-with-diamondmvc">Getting started with DiamondMVC</a></li>
			<li><a href="#introduction-to-controllers-and-views">Introduction to controllers and views</a></li>
			<li><a href="#introduction-to-models">Introduction to models</a></li>
			<li><a href="#introduction-to-plugins">Introduction to plugins</a></li>
			<li><a href="#introduction-to-custom-libraries">Introduction to custom libraries</a></li>
			<li><a href="#introduction-to-internationalization">Introduction to internationalization</a></li>
			<li><a href="#introduction-to-permissions">Introduction to permissions</a></li>
			<li><a href="#introduction-to-extensions">Introduction to extensions</a></li>
			<li><a href="#conclusion">Conclusion</a></li>
		</ol>
	</div>
	
	
	<p>
		As you would deduce from its name, <span class="blue">DiamondMVC</span> is a MVC system. Actually
		it's more than that. It's somewhere inbetween MVC and CMS. It doesn't feature a fancy text editor.
		Instead it provides a number of tools to assist you in development. In other words, it requires
		you to know the environment you're working in - much like a Linux operating system.
	</p>
	
	<p>
		As is the nature of a MVC system, DiamondMVC provides three specific core classes:
	</p>
	
	<ul>
		<ol>(<strong>M</strong>) the model,</ol>
		<ol>(<strong>V</strong>) the view,</ol>
		<ol>(<strong>C</strong>) and the controller.</ol>
	</ul>
	
	<p>
		Consider the controller your heart piece. The controller is directly run from the DiamondMVC's core
		run method. It's objective is to calculate all the requested data and store them locally in its
		<i>result</i> property.<br>
		To calculate its result it may require access to a database. Generally the model is responsible for
		retrieving any kind of data and is directly associated with a controller. However, it seems more
		reasonable to use a model where communicating with an external data management software, such as
		MySQL. In other words, DiamondMVC does not prescribe utilizing a model to access the file system,
		for example.<br>
		Ultimately the data can be displayed in a view. Views are both directly associated with a controller
		and a controller's action. A view can additionally be typed. The default type of view is HTML which
		generates HTML output. Another type of view could be JSON which extracts the interesting information
		from the controller's result and formats it for transmission to the client.
	</p>
	
	
	<h3 class="page-header" id="introduction-to-controllers-and-views">Introduction to controllers and views <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		The above explains how to create a new web page in your website:
	</p>
	
	<ol>
		<li>Create a controller</li>
		<li>Add an action</li>
		<li>Create the view</li>
	</ol>
	
	<p>
		That's it. The DiamondMVC automatically routes the first subdirectory relative to the root URL of
		the system to the controller, the second subdirectory to the controller's action and the third
		to a specific view, although usually the default view for the action is taken. That means step one
		may even be optional depending on whether you wish to create a sub page for a controller. An example
		is the documentation itself. The default action (<code>action_main()</code>) is the index linking to
		the most important quick guides to the Diamond, while this page is an action called
		<code>action_getting_started()</code> in code.
	</p>
	
	<p>
		Relevant files for this page are:
	</p>
	
	<ul>
		<li><code>/controllers/docs.php</code></li>
		<li><code>/views/docs/templates/getting_started.php</code></li>
	</ul>
	
	<p>
		The above shows us two general rules:
	</p>
	
	<ol>
		<li>
			In code, hyphens are replaced with underscores (<code>getting-started</code> as found in the
			URL is changed to <code>getting_started</code>), and
		</li>
		<li>
			View templates are stored in <code>/views/&lt;controller_name&gt;/templates</code>
		</li>
	</ol>
	
	<p>
		Alternatively you could attempt to access this action via a JSON view. To do so, simply append
		<code>?type=json</code> to the end of your URL. However, this documentation has not been built
		for AJAX support, thus the result will be <samp>null</samp>, because the default result of any
		controller is <var>null</var> itself. However, how about you treat this guide as a tutorial and
		create such a JSON view yourself? To do so, simply create the file
		<code>/views/docs/templates/getting_started.json.php</code> and generate JSON formatted output.
	</p>
	
	<p>
		Technically any kind of view type is supported. For example, if you're more of an XML fan, you
		could also create <code>/views/docs/templates/getting_started.xml.php</code> or even something
		completely genuine, such as <code>/views/docs/templates/getting_started.special.php</code>. The
		type is not predefined.
	</p>
	
	<p>
		<a href="<?= DIAMONDMVC_URL ?>/docs/views">Check this out</a> if you want to learn more about
		views, or <a href="<?= DIAMONDMVC_URL ?>/docs/controllers">refer to this guide</a> to learn
		more about controllers.
	</p>
	
	
	<h3 class="page-header" id="introduction-to-models">Introduction to models <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		When building your web service, you're most likely going to find yourself in the need of accessing
		the database. I've developed an elaborate and secure framework around <i>MySQLi</i> to allow
		quick and safe database interaction without having to worry about XSS injections - that is
		as long as you use every methods but the <code>Database::query()</code> method directly. While
		the latter has been made secure as far as possible (using the <code>?</code> markers of MySQLi),
		it does not benefit from the additional assembly methods the DBO provides and thus may end up
		vulnerable if not handled with care.<br>
		Quick disclaimer: I do not guarantee security - I'm confident it is safe, but I do not guarantee.
		Don't sue me! After all, you are getting an open source project! :(
	</p>
	
	<p>
		You create a new model just like creating a new controller: by creating a new file in the
		<code>/models</code> directory and extending the generic <code>Model</code> class, additionally
		implementing the <code>Model::read($from = null)</code> method.
	</p>
	
	<p>
		<a href="<?= DIAMONDMVC_URL ?>/docs/models">See this guide</a> to learn more about models.
	</p>
	
	
	<h3 class="page-header" id="introduction-to-plugins">Introduction to plugins <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		Sometimes you need to intervene in particular algorithms. For example, you want to know what
		controller has been requested and what action in particular was invoked. The solution to this
		problem is a plugin - or in other words an event listener automatically instantiated and
		registered by the <span class="blue">Diamond</span>.
	</p>
	
	<p>
		Place a new directory in the <code>/plugins</code> directory named after your plugin and inside
		that directory create the plugin's PHP file. For example:
		<code>/plugins/&lt;plugin_name&gt;/&lt;plugin_name&gt;.php</code><br>
		Obviously we could've chosen to omit the subdirectory as we did with controllers and models.
		But this approach has a much more important application: you can modularize your plugin even
		further! You can ship snippets along with your plugin core or other additional required
		libraries.
	</p>
	
	<p>
		Now technically a plugin does not need to handle any event. In fact, I was thinking of introducing
		the caching class as a plugin - until I found it much more applicable to just create it as a
		regular core class. This plugin wouldn't have listened to any event, instead would've simply
		loaded its library into the system and exposed it.
	</p>
	
	<p>
		Refer to <a href="<?= DIAMONDMVC_URL ?>/docs/plugins">the plugins guide</a> to learn more about
		plugins.
	</p>
	
	
	<h3 class="page-header" id="introduction-to-custom-libraries">Introduction to custom libraries <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		Unlike the above mentioned method of shipping a plugin you can create a custom library in the
		<code>/classes</code> directory. This has one major difference over libraries embedded via
		plugins: these are not loaded at the startup of the run method of the <span class="blue">Diamond</span>.
		Loading one of these libraries requires a call to <code>DiamondMVC::loadLibrary(&lt;string&gt;)</code>,
		which attempts to include the given library core file.
	</p>
	
	<p>
		The library core files are stored in <code>/classes/libs</code>. An exaple is the events framework
		itself. Its library core can be found under <code>/classes/libs/events.php</code> while its individual
		classes are located at <code>/classes/libs/events</code>. The library core itself simply accesses the
		<code>AutoloadRegistry::registerAutoloader(&lt;string&gt; <var>$fnName</var>)</code> method which
		registers the named function as an additional autoloader. The <code>AutoloadRegistry</code> microlibrary
		ensures registering multiple autoloaders are supported by providing a fallback for older PHP platforms.
		I thus recommend the usage of this class.
	</p>
	
	<p>
		Note that it is not mandatory to save your library's associated files in a director named the same as
		your library core. In the instance of the events library, this just happens to be the case. The various
		events are merely loaded using an autoloader which searches for unloaded events in the aforementioned
		directory. In fact, it's not unlikely you won't need to access the autoloader at all. The events library
		however is designed to allow you to create new event classes without having to register them with the
		library core - the moment they can be found in the directory is the same moment they are known to the
		system.
	</p>
	
	<p>
		While there is no individual guide to custom libraries in the <span class="blue">Diamond</span>, feel
		free to browse the source code of the events library and see its simplicity for yourself. Cheers!
	</p>
	
	
	<h3 class="page-header" id="introduction-to-internationalization">Introduction to internationalization <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		Commonly abbreviated with <i>i18n</i> - the i is the first letter and n is the last letter in the word
		<i>internationalization</i> while the 18 stands for the 18 characters between the two - and also known
		as localization means making your software ready for multilingual use. Usually this means using constants
		for text replacers, but the <span class="blue">Diamond</span> uses categorized INI files - even better,
		it doesn't just read the contents of these INI files but includes them as PHP scripts meaning you can
		slightly customize the output before sending out the appropriate translations.
	</p>
	
	<p>
		To support your preferred coding style, the <span class="blue">Diamond</span> searches for six particular
		files in this order:
	</p>
	
	<ol>
		<li><code>/lang/&lt;base_name&gt;.&lt;lang&gt;_&lt;country&gt;.ini.php</code></li>
		<li><code>/lang/&lt;base_name&gt;.&lt;lang&gt;_&lt;country&gt;.ini</code></li>
		<li><code>/lang/&lt;base_name&gt;.&lt;lang&gt;.ini.php</code></li>
		<li><code>/lang/&lt;base_name&gt;.&lt;lang&gt;.ini</code></li>
		<li><code>/lang/&lt;base_name&gt;.en.ini.php</code></li>
		<li><code>/lang/&lt;base_name&gt;.en.ini</code></li>
	</ol>
	
	<p>
		Quite obviously the system prefers concrete matches in language AND specific country in which the language
		is spoken the most - this can be used to distinguish between dialects, for instance as spoken in
		Belgian and French French. One example in this particular language would be &quot;soixant-dix&quot; and
		&quot;septant&quot;, the former being used in France and the latter in Belgium while both stand for &quot;seventy&quot;.<br>
		After that the system attempts to find a generic French translation not associated with any country. Perhaps
		you'd try to prevent redundancy by merging both files together in a PHP file generating the INI output
		which alternates between these two depending on <code>i18n::getCurrentCountry()</code>?<br>
		Last but not least if the system fails to find the preferred language it will attempt to find any of the two
		alternatives before it resorts to the English translation.
	</p>
	
	<p>
		Note that the <span class="blue">Diamond</span> currently does not automatically detect the client's preferred
		language and preferred alternatives. Instead you'd be required to write a plugin which detects this for you and
		sets the <var>$_SESSION['lang']</var>, <var>$_SESSION['lang2']</var>, and <var>$_SESSION['lang3']</var> session
		variables respectively. I do have plans of introducing such a feature myself one day - it would also support
		determining the user's preference based on his or her direct choice, for example as a registered user. It would
		probably also be quite nice to use a third party library to determine the user's possible language by IP, but
		frankly that is unreliable as the user might be surfing through a proxy or VPN.
	</p>
	
	<p>
		If you want to learn more about the internationalization process,
		<a href="<?= DIAMONDMVC_URL ?>/docs/i18n">review this guide</a>.
	</p>
	
	
	<h3 class="page-header" id="introduction-to-permissions">Introduction to permissions <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		Initially I considered assigning every user a &quot;userlevel&quot;. The controllers could then match this level
		against a minimum required level before casting its magic - to enforce permissions.
	</p>
	
	<p>
		However, one day I played around a little with TeamSpeak and took pleasure from its permissions system.
		Consequentially I built a permissions system inspired by TeamSpeak's permissions system. No user levels,
		instead a user can have any permission. Instead of requiring a minimum level per user, you can now require
		a minimum level per permission. This allows for much greater flexibility and grants you absolute control
		over your various user groups. This would even allow to restrict portions of the website to paying customers
		only without granting them particularly more powers than others. Additionally this keeps the possibly
		various installed extensions separately as each can request its own permissions.
	</p>
	
	<p>
		As briefly mentioned, you can also group users together. Instead of granting certain permissions to individual
		users, which would be tedious to manage, you can instead assign them to a group and grant said group the
		same permissions instead. This is called <i>permission inheritance</i>. A user may inherit from multiple groups.
		Only the highest granted or inherited permission level is retrieved.
	</p>
	
	<p>
		Permissions are much more complex and are not as easily managed as other parts of the system. I'd strongly
		suggest you learn more about them <a href="<?= DIAMONDMVC_URL ?>/docs/permissions">in this guide</a>.
	</p>
	
	
	<h3 class="page-header" id="introduction-to-extensions">Introduction to extensions <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<p>
		Extensions are all sorts of files installed through an internal manager: the <code>InstallationManager</code>.
		You will unlikely ever directly use it. For the sake of simplicity I've written a graphical user interface
		to manage all your installations. You can find it <a href="<?= DIAMONDMVC_URL ?>/system/installations">here</a>.
		This should suffice for this guide in terms of the actual management procedures.
	</p>
	
	<p>
		Technically you do not need to register your own controllers, models, modules, views, snippets, assets or anything,
		really, as an extension. This is what annoyed me the most about Joomla, to be honest. Developing any kind of
		extension for that CMS would be a nuisance at times. For example, adding new module positions to a template would
		require you to uninstall and reinstall your WIP.<br>
	</p>
	
	<p>
		The <span class="blue">Diamond</span> does not predefine extension types. An extension can be anything from a single
		image saved in <code>/assets/media/images/foo.png</code> to an actual CMS featuring multiple controllers, plugins,
		models and modules. It treats them all the same. In fact, it even treats itself the same! Well, almost...<br>
		The only difference between the <span class="blue">Diamond</span> and an extension - both together called an installation -
		is that the <span class="blue">Diamond</span> stores its installation meta data in a specific file common among
		all copies of the system: <code>/registry/diamondmvc.json</code>. Due to this fact the update process is also slightly
		adjusted: usually the old installation meta data is deleted as the new data is saved under a different, random name.
		Obviously we don't want that when updating the <span class="blue">Diamond</span> itself.
	</p>
	
	<p>
		I've just mentioned that weird installation meta data. What's that, I hear you ask? Well, maybe I don't. Because
		you're smart enough to guess what that is. It's a JSON encoded file storing, well, meta data on an installation,
		namely its version, the used installation protocol version, its author and his or her copyright, the distribution
		URL - for sharing :P - and update URL - for semi-automatic updating - and, possibly the most important of all,
		an automatically generated list of files extracted during the installation of an extension to be able to remove
		them again upon uninstallation.
	</p>
	
	<p>
		There are, however, certain expectations from an extension:
	</p>
	
	<ul>
		<li>The extension must be packed in a ZIP archive!</li>
		<li>Said archive may contain a meta.json file - in fact, I recommend it does as this file will be stored (and manipulated) in the registry!</li>
		<li>Before and after an installation certain PHP files within the archive may be included, as well as upon error during the installation.</li>
	</ul>
	
	<p>
		That last bullet is quite interesting, frankly. They can be used to enforce prerequisites of your extension (and
		abort installation by returning false from the script), set up permissions and tables in the database or roll back
		changes done to the system other than merely extracting the archive's files to the file system, such as drop added
		tables. Those three said scripts must be named the following:
	</p>
	
	<ul>
		<li>onbeforeinstall.php</li>
		<li>onafterinstall.php</li>
		<li>oninstallerror.php</li>
	</ul>
	
	<p>
		While this is actually all you need to know to get started with your own extension, you might want to
		<a href="<?= DIAMONDMVC_URL ?>/docs/extensions">read this guide</a> to learn more about the internal affairs
		of the InstallationManager.
	</p>
	
	
	<h3 class="page-header" id="conclusion">Conclusion <a href="#" data-toggle="tooltip" data-placement="bottom" title="Back to top"><i class="fa fa-arrow-circle-up"></i></a></h3>
	
	<h4>That's it!</h4>
	
	<p>
		I really have to say, bravo! I know I'm not the best at writing guides as I tend to type way too much information
		than actually necessary, so if you've read all of this: bravo! Truely bravo! You've took quite some time to read
		something a single individual has put hours into just to give you a hand at understanding the code he has written
		over the course of the past few months. And you took it!
	</p>
	
	<p>
		I hereby deem you a novice of the DiamondMVC! Conglaturations! May this platform be of much use to you! And thanks for your interest! :D
	</p>
</div>
 