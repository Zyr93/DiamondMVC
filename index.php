<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * The main engine of the DiamondMVC which instructs the controllers and renders the views.
 */
define('DIAMONDMVC', '1.1.3');
define('DIAMONDMVC_ROOT', dirname(__FILE__));

$https = $_SERVER['SERVER_PROTOCOL'] === 'https' or $_SERVER['SERVER_PORT'] == 443;
$tmp   = ($https ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
$tmp   = str_replace('\\', '/', $tmp);
if( substr($tmp, -1) === '/' ) {
    $tmp = substr($tmp, 0, strlen($tmp) - 1);
}

define('DIAMONDMVC_URL', $tmp, true);
unset($tmp);

defined('DS') or define('DS', DIRECTORY_SEPARATOR);

require_once(DIAMONDMVC_ROOT . '/config.php');
require_once(DIAMONDMVC_ROOT . '/lib/fns.php');
require_once(DIAMONDMVC_ROOT . '/lib/autoload.php');


// So far not needed... thus only an unnecessary safety risk.
// if( isset($_REQUEST['sid']) ) {
// 	session_id($_REQUEST['sid']);
// }
session_start();

if( !DiamondMVC::instance()->isInstalled() ) {
	redirect(DIAMONDMVC_URL . '/firstinstallation');
}
else {
	DiamondMVC::instance()->run();
}
