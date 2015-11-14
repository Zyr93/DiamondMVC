<?php
define('DIAMONDMVC', '1.1.3');
define('DIAMONDMVC_ROOT', dirname(dirname(__FILE__)));

$https = $_SERVER['SERVER_PROTOCOL'] === 'https' or $_SERVER['SERVER_PORT'] == 443;
$tmp   = ($https ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']));
$tmp   = str_replace('\\', '/', $tmp);
if( substr($tmp, -1) === '/' ) {
	$tmp = substr($tmp, 0, strlen($tmp) - 1);
}
define('DIAMONDMVC_URL', $tmp);
unset($tmp);

define('DS', DIRECTORY_SEPARATOR);

require_once(DIAMONDMVC_ROOT . '/lib/fns.php');
require_once(DIAMONDMVC_ROOT . '/config.php');

require_once(DIAMONDMVC_ROOT . '/lib/class_database.php');

$step = 1;
if( isset($_REQUEST['step']) ) {
	$step = intval($_REQUEST['step']);
}
step($step);


function step( $step ) {
	switch( $step ) {
		case 1:
			step1();
			break;
		
		case 2:
			step2();
			break;
			
		case 3:
			step3();
			break;
		
		default:
			noStep();
	}
}

/**
 * Shows the tabs to configure the installation.
 */
function step1( ) {
	include_once(dirname(__FILE__) . DS . 'step1.php');
}

/**
 * Prepares up the database. Afterwards shows success or failure message.
 */
function step2( ) {
	$success = false;
	
	if( !isset($_REQUEST['adminemail']) OR !isset($_REQUEST['adminpassword']) ) {
		$this->addMessage('Required field not set', 'Administrator email or administrator password not passed', 'error');
	}
	else {
		$success = true;
		$errors  = array();
		
		$adminemail    = $_REQUEST['adminemail'];
		$adminpassword = $_REQUEST['adminpassword'];
		
		// Generate other variables
		$variables = array(
			'adminname'                => 'Admin',
			'website_title'            => 'DiamondMVC',
			'default_controller_title' => '',
			'login_redirect'           => '/user',
			'session_timeout'          => 4320,
			'cache_lifetime'           => 86400,
			'debug_mode'               => 0,
			'verbose_logging'          => 0, // Unchecked checkboxes aren't included in the query string
			'log_severity'             => 5,
			'db_host'                  => '127.0.0.1',
			'db_port'                  => 3306,
			'db_user'                  => 'root',
			'db_pass'                  => '',
			'db_database'              => 'diamondmvc',
			'db_prefix'                => '',
			'enforce_column_deleted'   => 0, // Same as with verbose_logging
		);
		
		foreach( $variables as $variable => $default ) {
			$$variable = (isset($_REQUEST[$variable]) and !empty($_REQUEST[$variable])) ? $_REQUEST[$variable] : $default;
			
			// If the default is a numeric value, we're expecting a numeric value from the client as well.
			// For our convenience we're going to conver them here already.
			if( is_numeric($default) ) {
				if( is_int($default) ) {
					$$variable = intval($$variable);
				}
				else if( is_float($default) ) {
					$$variable = floatval($$variable);
				}
				else {
					throw new Exception('Unknown number format');
				}
			}
		}
		
		$pathConfig = DIAMONDMVC_ROOT . DS . 'ini.php';
		
		// Use the generated variables to configure the system
		$ini = (new ini())->read($pathConfig);
		$ini
			->set('DEBUG_MODE',                 $debug_mode)
			->set('VERBOSE_LOGGING',            $verbose_logging)
			->set('SESSION_TIMEOUT',            $session_timeout)
			->set('DEFAULT_CACHE_LIFETIME',     $cache_lifetime)
			->set('LOG_SEVERITY',               $log_severity)
			->set('WEBSITE_TITLE',              $website_title)
			->set('DEFAULT_CONTROLLER_TITLE',   $default_controller_title)
			->set('DEFAULT_LOGIN_REDIRECT',     $login_redirect)
			->set('DBO_ENFORCE_COL_DELETED',    $enforce_column_deleted)
			->set('HOST',   'DATABASE.DEFAULT', $db_host)
			->set('PORT',   'DATABASE.DEFAULT', $db_port)
			->set('USER',   'DATABASE.DEFAULT', $db_user)
			->set('PASS',   'DATABASE.DEFAULT', $db_pass)
			->set('DB',     'DATABASE.DEFAULT', $db_database)
			->set('PREFIX', 'DATABASE.DEFAULT', $db_prefix);
		
		try {
			$ini->write($pathConfig);
		}
		catch( Exception $ex ) {
			$errors[] = 'Failed to write config to disk';
		}
		
		$contents = @file_get_contents($pathConfig);
		if( @file_put_contents($pathConfig, "<?php defined('DIAMONDMVC') or die; ?>\n" . $contents) === false ) {
			$errors[] = 'Failed to prepend config with view guard - you must manually prepend it with <code>&lt;?php defined(\'DIAMONDMVC\') or die; ?&gt;</code>';
		}
		
		$config = Config::main()->reload();
		
		// Create the tables
		$db  = $config->getDBO();
		$sql = file_get_contents(DIAMONDMVC_ROOT . '/firstinstallation/create_tables.sql');
		$commands = explode(';', $sql);
		
		$db->pushState();
		
		foreach( $commands as $command ) {
			$command = trim($command);
			$db->query($command);
			if( !empty($db->error) ) {
				$errors[] = 'Failed to execute MySQL query: ' . $command . '. Terminated with error: ' . $db->error;
				$success  = false;
			}
		}
		
		$db->query("INSERT INTO `USERS` (`USERNAME`, `EMAIL`, `PASSWORD`, `DELETED`) VALUES ('$adminname', '$adminemail', '" . hash('sha256', $adminpassword) . "', 0)");
		if( !empty($db->error) ) {
			$errors[] = 'Failed to create admin user, please create the user manually in the database management software of your choosing. Please note that the password must be a SHA256 hash.';
		}
		
		$db->popState();
		
		$success = true;
		
		include(dirname(__FILE__) . '/step2.php');
	}
}

function step3( ) {
	if( !@rmdirs(DIAMONDMVC_ROOT . DS . 'firstinstallation') ) {
		include(dirname(__FILE__) . '/step3.php');
	}
	else {
		redirect(DIAMONDMVC_URL);
	}
}

/**
 * Shows an error message saying that, for some reason, no step was selected.
 */
function noStep( ) {
	include(dirname(__FILE__) . DS . 'nostep.php');
}
