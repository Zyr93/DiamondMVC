<?php
defined('DIAMONDMVC') or die;
?>
<!DOCTYPE html>
<html>
<head>
	<title>DiamondMVC Installation - Setup</title>
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
			<small>Setup</small>
		</h2>
	</div>
	<div id="view-firstinstall" class="view">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#a-note-ahead" data-toggle="tab" id="tablbl-a-note-ahead">Home</a></li>
			<li><a href="#general-settings" data-toggle="tab" id="tablbl-general-settings">General</a></li>
			<li><a href="#database-settings" data-toggle="tab" id="tablbl-database-settings">Database</a></li>
		</ul>
		
		<form action="<?= DIAMONDMVC_URL ?>/firstinstallation/" method="post" class="form-horizontal">
			<input type="hidden" name="step" value="2">
			
			<div class="tab-content">
				<div id="a-note-ahead" class="tab-pane fade in active">
					<h3 class="page-header">A note ahead</h3>
					
					<p>
						Before we get started, a note ahead:
					</p>
					<p>
						<strong>
							During the installation, be sure to hover over the input fields as they may provide
							tooltips with additional information on how a particular configuration setting is used.
						</strong>
					</p>
					<p>
						As a matter of fact you don't actually have to run this installation. The Diamond should
						work fine without, however you'd need to set up the database by yourself. In fact, this
						installation does just about that: prepare your database by creating tables in the
						database of your choice. The installation process is merely meant to save you some time.
					</p>
					<p>
						The required database tables store the users, user groups, permissions and plugin meta
						data of the Diamond. That's it. Nothing more, nothing less. Frankly, I even recommend
						after the installation you go back into phpMyAdmin or whatever it is you use to manage
						your database and fine-tune the user table to your liking. The user table only stores
						the most vital information: unique user ID, user name, email (for login), password,
						optionally also whether the user has been deleted - the record is still maintained and
						will prevent the user from creating a new account! This last aspect is just a standard
						in the business world where no data is ever really deleted, but rather archived.
					</p>
					<p>
						That said, regardless of your choice, good luck with your work!
					</p>
					
					<a href="#general-settings" class="btn btn-primary" id="btn_a-note-ahead_continue">Continue</a>
				</div>
				<div id="general-settings" class="tab-pane fade">
					<h3 class="page-header">Admin user settings</h3>
					
					<p>
						In order to log in to the backend of the Diamond for the first time, you'll need to
						specify the credentials of your first admin user.
					</p>
					
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-adminname">Pseudonym:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-adminname" class="form-control" name="adminname" value="Admin">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-adminemail">Email:</label>
						</div>
						<div class="col-xs-10">
							<input type="email" id="input-adminemail" name="adminemail" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-adminpassword">Password:</label>
						</div>
						<div class="col-xs-10">
							<input type="password" id="input-adminpassword" name="adminpassword" class="form-control">
						</div>
					</div>
					
					<h3 class="page-header">General settings</h3>
					
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-website-title">Website title:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-website-title" name="website_title" class="form-control" value="DiamondMVC">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-default-controller-title">Default controller title:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-default-controller-title" name="default_controller_title" data-toggle="tooltip" title="The controller title can be retrieved by the template or view for displaying. This is useful for changing the tab title on a per-page basis. However, I recommend to leave this empty." class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-login-redirect">Login redirect:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-login-redirect" name="login_redirect" value="/user" data-toggle="tooltip" title="The local URL to redirect the user to after successfully logging in." class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-session-timeout">Session timeout:</label>
						</div>
						<div class="col-xs-10">
							<input type="number" id="input-session-timeout" min="0" max="525600" step="1" value="4320" name="session_timeout" data-toggle="tooltip" title="Timeout is in minutes. 4320 = 3 days" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-cache-lifetime">Cache lifetime:</label>
						</div>
						<div class="col-xs-10">
							<input type="number" id="input-cache-lifetime" name="cache_lifetime" min="0" max="525600" step="60" value="86400" data-toggle="tooltip" title="Lifetime is in seconds. 86400 = 1 day" class="form-control">
						</div>
					</div>
					
					<h3 class="page-header">Debugging</h3>
					
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-debug-mode">Debug mode</label>
						</div>
						<div class="col-xs-10">
							<input type="checkbox" id="input-debug-mode" name="debug_mode">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-verbose-logging">Verbose logging</label>
						</div>
						<div class="col-xs-10">
							<input type="checkbox" id="input-verbose-logging" name="verbose_logging" checked>
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-log-severity">Log severity:</label>
						</div>
						<div class="col-xs-10">
							<input type="number" id="input-log-severity" min="-1" step="1" value="5" class="form-control" name="log_severity" data-toggle="tooltip" title="The Diamond will dump anything to the log file with a logging severity greater than or equal to this number.">
						</div>
					</div>
					
					<a href="#database-settings" class="btn btn-primary" id="btn_general-settings_next">Next</a>
				</div>
				<div id="database-settings" class="tab-pane fade">
					<h3 class="page-header">Database settings</h3>
					
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-db-host">Host:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-db-host" name="db_host" class="form-control" value="127.0.0.1">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-db-port">Port:</label>
						</div>
						<div class="col-xs-10">
							<input type="number" id="input-db-port" min="0" max="65535" step="1" value="3306" name="db_port" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-db-user">User:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-db-user" name="db_user" class="form-control" value="diamondmvc" data-toggle="tooltip" title="For the sake of security, I recommend you create a new MySQL user just for DiamondMVC - or whatever other database system you're using.">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-db-pass">Pass:</label>
						</div>
						<div class="col-xs-10">
							<input type="password" id="input-db-pass" name="db_pass" class="form-control">
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-db-database">Database:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-db-database" class="form-control" name="db_database" value="diamondmvc" data-toggle="tooltip" title="Choose the default database of the system. Note: you are not bound to this database in your code.">
						</div>
					</div>
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-db-table-prefix">Table prefix:</label>
						</div>
						<div class="col-xs-10">
							<input type="text" id="input-db-table-prefix" class="form-control" name="db_prefix" data-toggle="tooltip" title="Forces all tables accessed using DiamondMVC's query building interface to be prefixed with (exactly) this string.">
						</div>
					</div>
					
					<div class="form-group">
						<div class="col-xs-2">
							<label for="input-enforce-column-deleted">Enforce "deleted" column</label>
						</div>
						<div class="col-xs-10">
							<input type="checkbox" id="input-enforce-column-deleted" name="enforce_column_deleted" checked>
						</div>
					</div>
					
					<a href="#general-settings" class="btn btn-default" id="btn_database-settings_previous">Previous</a>
					<input type="submit" class="btn btn-primary" id="btn_database-settings_continue" value="Proceed to step 2" />
				</div>
			</div>
		</form>
	</div>
</body>
</html>