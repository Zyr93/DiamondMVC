<?php
function left( $str, $len ) {
	return substr($str, 0, $len);
}
function right( $str, $len ) {
	return substr($str, strlen($str) - $len);
}

function startsWith( $str, $check ) {
	return left($str, strlen($check)) == $check;
}
function endsWith( $str, $check ) {
	return right($str, strlen($check)) == $check;
}

/**
 * Converts a camel case string to a dash separated string.
 * @param  string $str Camel cased string.
 * @return string      Dash separated string.
 */
function camelCaseToDashSplit( $str ) {
	return strToLower(preg_replace('/[A-Z]/', '-$0', $str));
}
/**
 * Converts a dash separated string into a camel case string.
 * @param  string $str Dash separated string.
 * @return string      Camel cased string.
 */
function toCamelCase( $str ) {
	while( ($index = strpos($str, '-')) !== false ) {
		$str = substr($str, 0, $index) . strToUpper(substr($str, $index + 1, 1)) . substr($str, $index + 2);
	}
	return $str;
}

/**
 * Generates a random name using the given characters and length.
 * Supports a simple character range identifier "-" which represents
 * every character between its immediate surrounding characters,
 * including non-readable characters. "A-z" is different from "A-Za-z"!
 * @param  string  Allowed characters to use in the generated name.
 * @param  integer Length of the name, defaults to 20.
 * @return string  Generated name.
 */
function generateRandomName( $chars, $length = 20 ) {
	// Set up the set of characters to use.
	$realchars = '';
	for( $i = 0; $i < strlen($chars); ++$i ) {
		if( $chars[$i] === '-' and $i < strlen($chars) - 1 ) {
			if( $i === 0 || $i + 1 === strlen($chars) ) {
				throw new MalformedCharacterRangeException($chars, $i);
			}
			
			$start = ord($chars[$i-1]);
			$end   = ord($chars[$i+1]);
			for( $j = $start + 1; $j <= $end; ++$j ) {
				$realchars .= chr($j);
			}
		}
		else {
			$realchars .= $chars[$i];
		}
	}
	
	
	$result = '';
	$max    = strlen($realchars) - 1;
	
	for( $i = 0; $i < $length; ++$i ) {
		$char = $realchars[mt_rand(0, $max)];
		$result .= $char;
	}
	
	return $result;
}

function minmax( $num, $min, $max ) {
	return min(max($num, $max), $min);
}
function clip( $num, $min, $max ) {
	return minmax($num, $max, $min);
}

/**
 * Jails the $path to $jail. The result will either be a path to an existing file or directory or the $jail itself in case
 * $path would lie outside of the $jail.
 * @param  string $jail Forced root of the $path to jail. Absolute path is recommended. Jail need not exist.
 * @param  string $path Relative or absolute path to jail. Path need not exist.
 * @return string       The jailed path or the jail itself if the file or directory lies outside the jail or does not exist.
 */
function jailpath( $jail, $path ) {
	$jail = sanitizePath($jail);
	$path = isPathAbsolute($path) ? sanitizePath($path) : sanitizePath("$jail/$path");
	return startsWith($path, $jail) ? $path : $jail;
}

/**
 * Sanitizes the given path. Uses realpath() where possible. Directory separators are uniformed. Multiple subsequential
 * separators are removed. "This" and "parent" special directories are resolved.
 * @param  string $path
 * @return string Sanitized path
 */
function sanitizePath( $path ) {
	$tmp  = realpath($path);
	if( $tmp ) {
		return $tmp;
	}
	
	// If given path not absolute, prepend working directory
	if( !isPathAbsolute($path) ) {
		$path = getcwd() . DS . $path;
	}
	
	$path = preg_replace('/[\\\\\/]+/', '/', $path);
	$path = preg_replace('/\/+/', DS, $path);
	
	// Resolve "parent" special directory
	$parts = explode(DS, $path);
	while( ($index = array_search('..', $parts)) !== false ) {
		if( $index === 0 or $index === 1 ) {
			throw new Exception('Failed to resolve parent directory in path ' . $path);
		}
		array_splice($parts, $index - 1, 2);
	}
	$path = implode(DS, $parts);
	
	// Resolve "this" special directory
	$path = str_replace(DS . '.', '', $path);
	
	return $path;
}

/**
 * Checks if the given path is absolute.
 * @param  string  $path
 * @return boolean
 */
function isPathAbsolute( $path ) {
	return preg_match('/^[A-Z]:[\\\\\/]|^\/{1,2}/', $path);
}

/**
 * Makes a relative URL absolute. URLs which are already absolute aren't touched.
 * @param  string $url  URL to make absolute.
 * @param  string $home Optional home directory for relative URLs. Defaults to website root.
 * @return string       Absolute URL
 */
function makeUrlAbsolute( $url, $home = '' ) {
	// Apply uniform
	$path = preg_replace('#//+#', '/', "/$home/$url");
	
	// If $home itself is absolute, $path as result
	if( preg_match('/^(\w+:)?\/\//', $home) ) {
		// The preg_replace above broke a few things: :// was turned into :/ and the URL was prefixed with an unnecessary / to uniform things.
		$path = substr(str_replace(':/', '://', $path), 1);
	}
	// Otherwise prefix the system's root URL
	else {
		$path = DIAMONDMVC_URL . $path;
	}
	
	// Absolute path within website home
	if( startsWith($url, '/') and !startsWith($url, '//') ) {
		return DIAMONDMVC_URL . $url;
	}
	// Paths relative to any home
	else if( !preg_match('/^(\w+:)?\/\//', $url) ) {
		return $path;
	}
	// Just any kind of network resource (//my/url)
	return $url;
}

/**
 * Leitet den Klienten weiter an eine andere URL. Es kann ein HTTP-Code angegeben werden.
 * @param  string  $url  URL, an die der Klient weitergeleitet wird.
 * @param  integer $code Zu übergenbener HTTP-Code.
 */
function redirect( $url, $code = 307 ) {
	header('location: ' . $url, true, $code);
}

/**
 * Checks if a user is logged in with the given access level. If no user is logged in, the user
 * is automatically forwarded to the login page. If a user is logged in but does not have the
 * required access permissions, the user is automatically forwarded to the error page with the
 * HTTP status code 403 Forbidden.
 * In the aforementioned two scenarios this function returns false, otherwise true.
 * @param  integer $userlevel Required user level for accessing this area.
 * @return boolean            True if the conditions above are met, otherwise false.
 */
function requireLogin( $userlevel = 1 ) {
	$user = DiamondMVC::instance()->getCurrentUser();
	if( !$user->isLoggedIn() ) {
		redirect(DIAMONDMVC_URL . '/login?returnto=' . urlencode($_SERVER['REQUEST_URI']));
		return false;
	}
	if( !$user->hasLevel($userlevel) ) {
		redirect(DIAMONDMVC_URL . '/error?code=403');
		return false;
	}
	return true;
}


/**
 * Schreibt eine Benachrichtigung mit Dringlichkeit in die Log-Datei falls die Dringlichkeit
 * hoch genug ist.
 * @param string  $msg              Zu loggende Benachrichtigung.
 * @param integer $level            Dringlichkeit der Nachricht.
 * @param integer $stackTraceLength Anzahl der anzuzeigenden Stack-Trace-ELemente. Standardmäßig 5. Setze auf 0 um keinen Stack-Trace anzuzeigen.
 */
function logMsg( $msg, $level = 1, $stackTraceLength = 5 ) {
	$user = DiamondMVC::instance()->getCurrentUser();
	
	$requiredLevel = intval(Config::main()->get('LOG_SEVERITY'));
	if( $level >= $requiredLevel ) {
		$bt = debug_backtrace();
		$ip = $_SERVER['REMOTE_ADDR'];
		
		if( DiamondMVC::instance()->isLoggedIn() ) {
			$file = date('Y-m-d-') . $user->getName();
		}
		else {
			if( $ip === '127.0.0.1' or $ip === '::1' ) {
				$ip = 'localhost';
			}
			$file = date('Y-m-d-') . $ip;
		}
		
		$file = DIAMONDMVC_ROOT . '/logs/' . $file . '.log.txt';
		
		if( !is_dir(DIAMONDMVC_ROOT . '/logs') )
			mkdir(DIAMONDMVC_ROOT . '/logs');
		$res = fopen($file, 'a+');
		if( !is_resource($res) ) {
			trigger_error('Log-Datei ' . $file . ' konnte nicht geöffnet werden.', E_USER_WARNING);
		}
		else {
			fwrite($res, date('H:i:s ') . str_replace(DIAMONDMVC_ROOT . DS, '', $bt[0]['file']) . ' - ' . $bt[1]['function'] . '(' . implode(', ', array_map('mapArgs', $bt[1]['args'])) . ')(:' . $bt[0]['line'] . ') ' . $msg . PHP_EOL);
			
			if( Config::main()->get('VERBOSE_LOGGING') and $stackTraceLength > 0 ) {
				fwrite($res, "\tStacktrace:" . PHP_EOL);
				
				for( $i = 1; $i < $stackTraceLength and $i < count($bt); ++$i ) {
					@fwrite($res, "\t\t($i) " . str_replace(DIAMONDMVC_ROOT . DS, '', $bt[$i]['file']) . " - {$bt[$i]['function']}(" . implode(', ', array_map('mapArgs', $bt[$i]['args'])) . ")(:{$bt[$i]['line']})" . PHP_EOL);
				}
				
				if( 5 < count($bt) ) {
					fwrite($res, "\t\t... and " . (count($bt) - 5) . " more" . PHP_EOL);
				}
			}
			
			fclose($res);
		}
	}
}

function mapArgs( $arg ) {
	if( is_array($arg) ) {
		return '[Array]';
	}
	if( is_object($arg) ) {
		return '{Object}';
	}
	return $arg;
}

/**
 * Schreibt einen MySQL-Query in die entsprechende Log-Datei.
 * Queries werden nur im Debug-Mode geloggt, um überflüssige Dateisystemzugriffe zu vermeiden.
 * @param string $query  Zu loggender MySQL-Query.
 * @param string $bind   Typendefinition der Marker
 * @param array  $params Zu bindende Parameter
 */
function logQuery( $query, $bind = '', $params = null ) {
	if( !Config::main()->isDebugMode() )
		return;
	
	$user = DiamondMVC::instance()->getCurrentUser();
	
	if( !is_array($params) ) {
		$params = array();
	}
	
	if( $user->isLoggedIn() ) {
		$file = date('Y-m-d-') . $user->getName();
	}
	else {
		$ip = $_SERVER['REMOTE_ADDR'];
		if( $ip === '127.0.0.1' or $ip === '::1' ) {
			$ip = 'localhost';
		}
		$file = date('Y-m-d-') . $ip;
	}
	
	$file = DIAMONDMVC_ROOT . '/logs/' . $file . '.qlg.txt';
	
	if( !is_dir(DIAMONDMVC_ROOT . '/logs') )
		mkdir(DIAMONDMVC_ROOT . '/logs');
	$res = fopen($file, 'a');
	if( !is_resource($res) ) {
		trigger_error('Log-Datei ' . $file . ' konnte nicht geöffnet werden.', E_USER_WARNING);
	}
	else {
		fwrite($res, date('[H:i:s]') . $query . PHP_EOL);
		if( !empty($bind) ) {
			fwrite($res, "\tBind ($bind): " . implode(', ', array_map('mapArgs', $params)) . PHP_EOL);
		}
		fclose($res);
	}
}

function generateNotificationHTML( $title, $message, $level = 'info' ) {
	$level = strToLower($level);
	
	switch( $level ) {
		case 'success':
		case 'done':
			$level = 'success';
			break;
			
		case 'info': default:
		case 'notice':
		case 'note':
			$level = 'info';
			break;
			
		case 'warning':
		case 'warn':
		case 'caution':
			$level = 'warning';
			break;
			
		case 'danger':
		case 'error':
		case 'fatal':
		case 'critical':
			$level = 'danger';
			break;
	}
	
	return
		'<div class="alert alert-' . $level . '" role="alert">' .
			'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>' .
			'<strong>' . $title . '</strong> ' . $message .
		'</div>';
}


/**
 * Schneidet den Text nach $wordcount Wörtern ab.
 * @param  string  $text      Zu schneidender Text.
 * @param  integer $wordcount Anzahl der Wörter, die im resultierenden Text enthalten bleiben sollen.
 * @return string             Geschnittener Text.
 */
function cut_text_by_words( $text, $wordcount = 100 ) {
	$words = preg_split('/\s+/', $text, $wordcount + 1);
	
	if( count($words) < $wordcount ) {
		return implode(' ', $words);
	}
	
	array_pop($words);
	return implode(' ', $words) . ' ...';
}

/**
 * Schneidet den Text nach ungefähr $charactercount Buchstaben ab, sodass ein Wort
 * nicht zerschnitten wird.
 * @param  string  $text           Zu schneidender Text.
 * @param  integer $charactercount Anzahl der Zeichen, die im resultierenden Text enthalten bleiben sollen.
 * @return string                  Geschnittener Text.
 */
function cut_text_by_characters( $text, $charactercount = 100 ) {
	if( preg_match('/[\s-]/', $text, $matches, PREG_OFFSET_CAPTURE, $charactercount) ) {
		return substr($text, 0, $matches[0][1]) . ' ...';
	}
	return $text;
}


/**
 * Filtert ein Array ähnlich wie array_filter. Aus irgendeinem Grund scheint array_filter
 * allerdings nicht wie in der Dokumentation beschrieben 3 Parameter zu akzeptieren, daher
 * diese Funktion.
 * @param  array   $array  Zu filterndes Array.
 * @param  string  $filter Filternde Funktion. Wird als Parameter an call_user_func übergeben, daher kann auch ein Array mit Objekt oder Klasse und Name übergeben werden.
 * @param  integer $mode   Ob Schlüssel, Wert oder Schlüssel und Wert an den Filter übergeben werden sollen. 0 für nur Wert, 1 für nur Schlüssel, 2 für beides.
 * @return array           Gefiltertes Array.
 */
function filter_array( $array, $filter, $mode = 0 ) {
	$result = array();
	foreach( $array as $key => $value ) {
		if( !$mode ) {
			$keep = call_user_func($filter, $value);
		}
		else if( $mode == 1 ) {
			$keep = call_user_func($filter, $key);
		}
		else {
			$keep = call_user_func($filter, $key, $value);
		}
		if( $keep ) {
			$result[$key] = $value;
		}
	}
	return $result;
}


/**
 * Konvertiert einen Markdown-Text in gültiges HTML.
 * @param  string $text Zu konvertierender Text.
 * @return string       Konvertierter Text.
 */
function parseMarkdown( $text ) {
	require_once('assets/Parsedown.php');
	$text = unescape($text);
	return (new Parsedown())->parse($text);
}

function unescape( $text ) {
	$text = preg_replace('/\\\\{2,}/', '\\', $text);
	$text = preg_replace('/\\\\n/', "\n", $text);
	$text = preg_replace('/\\\\r/', '', $text);
	$text = preg_replace('/\\\\t/', "\t", $text);
	$text = preg_replace('/\\\\(.)/', "$1", $text);
	return $text;
}

function strip_html( $text ) {
	return preg_replace('/<.*?>/', ' ', $text);
}

/**
 * Saves the given associative array in the named ini file. Supports categorized
 * ini files by adding nested arrays. Obviously only supports two-dimensional
 * arrays due to the nature of ini categories.
 * @param  string|resource $file      Path to the file to write to or a file resource.
 * @param  array           $data      Associative array providing the data to write to file.
 * @param  boolean         $protected Whether the file is to be publicly read protected by forcing the checking the existence of the DIAMONDMVC constant with PHP. Accordingly, ensure your server treats the file as a PHP file.
 */
function saveIni( $file, $data, $protected = false ) {
	$res = is_resource($file) ? $file : fopen($file, 'w');
	
	if( !$res ) {
		logMsg('Failed to open the Ini file ' . $file, 5);
		return false;
	}
	
	$categories = array();
	
	// First extract all nested arrays.
	foreach( $data as $key => $value ) {
		if( is_array($value) ) {
			$categories[$key] = $value;
			unset($data[$key]);
		}
	}
	
	// If public read access is restricted, write the PHP header.
	fwrite($res, "<?php defined('DIAMONDMVC') or die() ?>\n");
	
	// Write non-categorized data.
	foreach( $data as $key => $value ) {
		fwrite($res, "$key=" . saveIni_packValue($value) . "\n");
	}
	
	// Write the categories and category data.
	foreach( $categories as $category => $items ) {
		fwrite($res, "\n[$category]\n");
		foreach( $items as $key => $value ) {
			fwrite($res, "$key=" . saveIni_packValue($value) . "\n");
		}
	}
	
	// Final blank line
	fwrite($res, "\n");
	
	if( !is_resource($file) ) {
		fclose($res);
	}
	
	return true;
}

function saveIni_packValue( $value ) {
	if( is_object($value) ) {
		return '__OBJECT__' . urlencode(serialize($value));
	}
	return urlencode($value);
}

function readIni( $file ) {
	if( !file_exists($file) ) {
		logMsg('Ini file not found: ' . $file, 5);
		return false;
	}
	
	ob_start();
	include($file);
	$contents = ob_get_contents();
	ob_end_clean();
	
	$ini = parse_ini_string($contents, true);
	
	if( !$ini ) {
		logMsg('Failed to read ini file ' . $file, 5);
		return false;
	}
	
	$result = array();
	
	foreach( $ini as $key => $value ) {
		if( !is_array($value) ) {
			$result[$key] = readIni_unpackValue($value);
		}
		else {
			$result[$key] = array();
			foreach( $value as $key2 => $value2 ) {
				$result[$key][$key2] = readIni_unpackValue($value2);
			}
		}
	}
	
	return $result;
}

function readIni_unpackValue( $value ) {
	if( left($value, 10) === '__OBJECT__' ) {
		return unserialize(urldecode(substr($value, 10)));
	}
	return urldecode($value);
}


function parseCurlResponse( $response ) {
	$lines   = explode("\r\n", $response);
	$headers = array();
	$content = '';
	
	// Until the first blank line, every line contains a header.
	for( $i = 0; $i < count($lines); ++$i ) {
		$line = $lines[$i];
		
		if( empty($line) ) {
			++$i;
			break;
		}
		$index = strpos($line, ':');
		$headers[substr($line, 0, $index)] = substr($line, $index + 2);
	}
	
	$content = array_splice($lines, $i);
	return array(
		'headers' => $headers,
		'content' => implode("\r\n", $content),
	);
}


/**
 * Simple test whether the passed string is a URL.
 * @param  string $url
 * @return boolean
 */
function is_url( $url ) {
	return preg_match('/^(([a-z]+:)?\/\/)?(((\w|[-])+\.)*((\w|[-])+)|localhost)(:\d+)?\/?/', $url);
}


/**
 * Creates the given path if it doesn't already exist.
 * @param  string  $path to create
 * @return boolean       True if the path was successfully created. False if at least one directory within the path could not be created. Also returns true if the path did not need to be created.
 */
function mkdirs( $path ) {
	if( startsWith($path, $_SERVER['DOCUMENT_ROOT']) ) {
		$path = substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
	}
	if( startsWith($path, DIAMONDMVC_ROOT) ) {
		$path = substr($path, strlen(DIAMONDMVC_ROOT));
	}
	if( startsWith($path, '/') ) {
		$path = substr($path, 1);
	}
	
	// Make directory separators platform-specifically uniform.
	$path = preg_replace('/[\/\\\\]/', DS, $path);
	
	// Get directories in path.
	$parts = explode(DS, $path);
	$path  = '';
	
	foreach( $parts as $part ) {
		$path .= $part . DS;
		if( is_dir($path) ) {
			continue;
		}
		if( is_file($path) ) {
			return false;
		}
		if( !mkdir($path) ) {
			return false;
		}
	}
	
	return true;
}

/**
 * Recurrsively removes everything in the given path, i.e. all files and subdirectories within the
 * given directory.
 * @param  string  $path
 * @return boolean True if the directory was successfully deleted, otherwise false. In case of an error, see the logs.
 */
function rmdirs( $path ) {
	// Make sure the file even is a file. :I
	if( !is_dir($path) ) {
		logMsg('rmdirs: attempted to recurrsively remove directories in path ' . $path . ', but its not a directory!', 5, 5);
		return false;
	}
	
	// Make directory separators platform-specifically uniform.
	$files = glob("$path/*");
	
	foreach( $files as $file ) {
		if( is_dir($file) ) {
			rmdirs($file);
		}
		else {
			unlink($file);
		}
	}
	
	if( !is_dir_empty($path) ) {
		logMsg('rmdirs: failed to delete everything in the directory', 5, 5);
		return false;
	}
	
	return rmdir($path);
}

/**
 * Checks if the given directory is empty.
 * @param string $dir
 * @return boolean
 */
function is_dir_empty( $dir ) {
	return count(glob("$dir/*")) === 0;
}

/**
 * Recursive glob file pattern search utility function
 * @see https://secure.php.net/manual/de/function.glob.php
 * @return array Matched files and directories
 */
function rglob( $glob, $flags = 0 ) {
	$glob   = preg_replace('/[\\\\\/]/', DS, $glob);
	$result = glob($glob, $flags);
	
	foreach( $result as $item ) {
		if( is_dir($item) ) {
			$subresult = rglob($item . DS . '*', $flags);
			$result    = array_merge($result, $subresult);
		}
	}
	
	return $result;
}

/**
 * Real copy of files, directories (recurrsively) and symbolic links in one handy function.
 * @param  string  $source Path to the source to copy
 * @param  string  $dest   Path to the destination to copy to
 * @return boolean         Whether copying was successful. When copying a directory, returns true if and only if copying of all files within the directory was successful.
 */
function rcopy( $source, $dest ) {
	if( is_link($source) ) {
		return symlink(readlink($source), $dest);
	}
	
	if( is_file($source) ) {
		return copy($source, $dest);
	}
	
	if( !is_dir($dest) && !mkdir($dest) ) {
		return false;
	}
	
	$dir = opendir($source);
	if( !$dir ) {
		logMsg('rcopy: failed to open directory ' . $source, 5, 5);
		return false;
	}
	
	$result = true;
	while( $file = readdir($dir) ) {
		if( $file === '.' or $file === '..' ) continue;
		
		$tmp = rcopy("$source/$file", "$dest/$file");
		if( !$tmp ) {
			logMsg("rcopy: failed to copy file \"$source/$file\" to \"$dest/$file\"", 5, 5);
		}
		$result = $result && $tmp;
	}
	return $result;
}
