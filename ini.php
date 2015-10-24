<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();
?>

DEBUG_MODE=1
VERBOSE_LOGGING=1 ; If set (1), a short stacktrace of by default 5 calls will be logged along.

DATABASE=DEFAULT
SESSION_TIMEOUT=15 ; in minutes
DEFAULT_CACHE_LIFETIME=90000 ; 1 day in milliseconds

LOG_SEVERITY=1 ; Log messages with a priority higher than this will be logged.

WEBSITE_TITLE='DiamondMVC Demo Site' ; Title of the website, as usually displayed in the <title> element and/or as website header.

DEFAULT_CONTROLLER=ControllerFrontpage ; Default controller to route to if the requested controller does not exist
DEFAULT_CONTROLLER_TITLE='DiamondMVC' ; Either empty this or change it to a more appropriate title.
DEFAULT_LOGIN_REDIRECT='/user' ; Web URL relative to system root to redirect the user to after login

; Database column enforcement rules

DBO_ENFORCE_COL_DELETED=true
DBO_ENFORCE_COL_HIDDEN=true

[DATABASE.DEFAULT]
HOST=127.0.0.1
PORT=3306
USER=root
PASS=
DB=diamondmvc
PREFIX=
