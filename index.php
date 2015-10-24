<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * The main engine of the DiamondMVC which instructs the controllers and renders the views.
 */
define('DIAMONDMVC', '0.2');
define('DIAMONDMVC_ROOT', dirname(__FILE__));
if ($_SERVER['SERVER_PROTOCOL'] == 'https' || $_SERVER['SERVER_PORT'] == '443')
{
    $chelpurl = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
}
else
{
    $chelpurl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
}
$chelpurl = str_replace('\\', '/', $chelpurl);
if (substr($chelpurl, -1) == '/')
{
    $chelpurl = substr($chelpurl, 0, strlen($chelpurl) - 1);
}

define('DIAMONDMVC_URL', $chelpurl, true);
unset($chelpurl);
// define('DIAMONDMVC_URL',  str_replace('\\', '/', str_replace(dirname(DIAMONDMVC_ROOT), '', DIAMONDMVC_ROOT)));

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
	DiamondMVC::instance()->install();
}
else {
	DiamondMVC::instance()->run();
}
