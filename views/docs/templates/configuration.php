<?php
$this->addStylesheet('docs.css');
?>
<div id="view-docs" class="view view-configuration">
	<h2 class="page-header">Configuration</h2>
	
	<div class="toc pull-right">
		<h4>Table of contents</h4>
		<ol>
			<li><a href="#session_timeout">SESSION_TIMEOUT</a></li>
			<li><a href="#default_cache_lifetime">DEFAULT_CACHE_LIFETIME</a></li>
			<li><a href="#website_title">WEBSITE_TITLE</a></li>
			<li>
				<a href="#controller_related">Controller related</a>
				<ol>
					<li><a href="#default_controller">DEFAULT_CONTROLLER</a></li>
					<li><a href="#default_controller_title">DEFAULT_CONTROLLER_TITLE</a></li>
					<li><a href="#default_login_redirect">DEFAULT_LOGIN_REDIRECT</a></li>
				</ol>
			</li>
			<li>
				Logging
				<ol>
					<li><a href="#debug_mode">DEBUG_MODE</a></li>
					<li><a href="#verbose_logging">VERBOSE_LOGGING</a></li>
					<li><a href="#log_severity">LOG_SEVERITY</a></li>
				</ol>
			</li>
			<li>
				Database
				<ol>
					<li><a href="#database">DATABASE</a></li>
					<li><a href="#dbo_enforce_col_deleted">DBO_ENFORCE_COL_DELETED</a></li>
					<li>
						<a href="#database_connection">Database Connection Entry</a>
						<ol>
							<li><a href="#database_host">HOST</a></li>
							<li><a href="#database_port">PORT</a></li>
							<li><a href="#database_user">USER</a></li>
							<li><a href="#database_pass">PASS</a></li>
							<li><a href="#database_db">DB</a></li>
							<li><a href="#database_prefix">PREFIX</a></li>
						</ol>
					</li>
				</ol>
			</li>
		</ol>
	</div>
	
	
	<h3 class="page-header" id="session_timeout">SESSION_TIMEOUT</h3>
	
	<p>
		Defines the period of inactivity in minutes after which a user will be logged out of the
		system. Currently the database does not feature a column to determine whether to stay
		logged in beyond this period.<br>
		By default 15 minutes.
	</p>
	
	
	<h3 class="page-header" id="default_cache_lifetime">DEFAULT_CACHE_LIFETIME</h3>
	
	<p>
		The lifetime of a cached item in seconds if not otherwise specified. By default approximately
		a day.
	</p>
	
	
	<h3 class="page-header" id="website_title">WEBSITE_TITLE</h3>
	
	<p>
		The name of your website, usually found in both the header as well as in the &lt;title&gt;
		tag of your template.
	</p>
	
	<p>
		Use this together with the individual title of your current controller to generate unique
		titles for each page of your website. I recommend setting the controller's title per action.
		If not otherwise set, the <code><a href="#default_controller_title">DEFAULT_CONTROLLER_TITLE</a></code>
		will be used.
	</p>
	
	
	<h3 class="page-header" id="controller_related">Controller related</h3>
	
	<h4 class="page-header" id="default_controller">DEFAULT_CONTROLLER</h4>
	
	<p>
		Defines the controller routed to if no other controller was specified or the requested
		controller does not exist. By default <code>ControllerFrontpage</code>.
	</p>
	
	
	<h4 class="page-header" id="default_controller_title">DEFAULT_CONTROLLER_TITLE</h4>
	
	<p>
		Specifies the default title of any controller if not otherwise defined. The controller
		title can be accessed together with the <code><a href="#website_title">WEBSITE_TITLE</a></code>
		by the template or view to construct a rather unique page title. Thus I generally recommend
		to leave this string blank and specify a title in each action of your controllers.
	</p>
	
	
	<h4 class="page-header" id="default_login_redirect">DEFAULT_LOGIN_REDIRECT</h4>
	
	<p>
		Specifies the URL to redirect the user to after logging in if not otherwise specified.
		By default redirects the user to his or her personal user page. However, the user page
		has not been implemented yet, thus it simply leads to the default controller.
	</p>
	
	
	<h3 class="page-header" id="logging">Logging</h3>
	
	<p>
		To assist you with development the Diamond provides a number of verbose logging
		utilities. All logs are dumped to <code>/logs</code>. Depending on whether a user is
		logged in at the time of logging, the log file will either contain the remote IP of
		the client causing the log files or the username. In both cases the file also
		contains a date and the file extension <code>.log.txt</code>.
	</p>
	
	<p>
		Another type of log file is used to dump performed MySQL queries. These files are named
		in the same manner, however receiving the <code>.qlg.txt</code> file extension.
	</p>
	
	
	<h4 class="page-header" id="debug_mode">DEBUG_MODE</h4>
	
	<p>
		Boolean (0/1). If set to true this configuration setting indicates the website is
		in debug mode, effectively dumping successfully performed queries and enabling the
		testing controller. The regular log files are not affected by this setting whatsoever.
	</p>
	
	
	<h4 class="page-header" id="verbose_logging">VERBOSE_LOGGING</h4>
	
	<p>
		Boolean (0/1). If set to true this configuration setting allows the <code>logMsg()</code>
		function to dump a stack trace of variable length (set by the third parameter of
		the function). This however can still be manually disabled by passing <code>false</code>
		as third parameter.
	</p>
	
	
	<h4 class="page-header" id="log_severity">LOG_SEVERITY</h4>
	
	<p>
		When not in debug mode this configuration setting causes the <code>logMsg()</code>
		function to only dump messages to file with a severity (second parameter) higher
		than or equal to this number. To log every message logged simply pass 0 as second
		parameter to said function. By default the log severity is set to 5. The highest
		severity used by the Diamond is 9 which usually corresponds to fatal errors.
	</p>
	
	
	<h3 class="page-header" id="database_connection">Database Connection</h3>
	
	<p>
		Due to the fact that the Diamond stores the database credentials in INI categories
		it is possible to prepare multiple connection settings. This is particularly useful
		when working on different databases during development and deployment as it allows
		to change a single string instead of 6 different values.
	</p>
	
	
	<h4 class="page-header" id="database_host">HOST</h4>
	
	<p>
		Database server host, usually <code>127.0.0.1</code> or another IP address.
	</p>
	
	
	<h4 class="page-header" id="database_port">PORT</h4>
	
	<p>
		Database server port, usually <code>3306</code>, the default MySQL database server port.
	</p>
	
	
	<h4 class="page-header" id="database_user">USER</h4>
	
	<p>
		The user with limited access rights to the database to prevent damage to the
		database outside of the website relevant tables. By default <code>diamondmvc</code>.
	</p>
	
	
	<h4 class="page-header" id="database_pass">PASS</h4>
	
	<p>
		The user's password.
	</p>
	
	
	<h4 class="page-header" id="database_db">DB</h4>
	
	<p>
		Default database to use for the queries. By default <code>diamondmvc</code>.
	</p>
	
	
	<h4 class="page-header" id="database_prefix">PREFIX</h4>
	
	<p>
		Database table prefix prepended to any table in queries built using the Diamond's
		query building methods.
	</p>
</div>
