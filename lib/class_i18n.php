<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * The i18n class allows easy multilingual support for the website. It checks the session for
 * the user's chosen language: $_SESSION['lang']. Set this session variable respectively to
 * allow the i18n class to determine the correct language files.
 * 
 * Two alternative languages may be specified by setting $_SESSION['lang2'] and/or $_SESSION['lang3']
 * respectively.
 * 
 * If none of the three preferred languages can be found, resorts to en_US.
 * 
 * The language files are INI files. The instance can then be used similarly to the Config class:
 * by using {@link #get()} you can access a particular key and retrieve its translation.
 * 
 * The language file may also be a PHP file generating INI formatted output. The file is then run
 * in the context of the current i18n instance, granting access to the methods of this class,
 * namely {@link #getCurrentLanguage()} and {@link #getCurrentCountry()}. This allows for slight
 * customization of otherwise identical languages, e.g. British English and American English,
 * while avoiding duplication.
 */
defined('DIAMONDMVC') or die();

class i18n {
	
	/**
	 * Chosen language. By default English.
	 * @var string
	 */
	static protected $lang     = '';
	
	/**
	 * Country in which the given language is spoken to allow for culturally more accurate
	 * translations. By default the USA.
	 * @var string
	 */
	static protected $country  = '';
	
	/**
	 * First preferred alternative language if the primary preferred language is not available.
	 * @var string
	 */
	static protected $lang2    = '';
	
	/**
	 * Associated country for the first preferred alternative language.
	 * @var string
	 */
	static protected $country2 = '';
	
	/**
	 * Least preferred alternative language if the first alternative is not available.
	 * @var string
	 */
	static protected $lang3    = '';
	
	/**
	 * Associated country for the least preferred alternative language.
	 * @var string
	 */
	static protected $country3 = '';
	
	/**
	 * Base file name of the associated language files. When reading the language data it
	 * is used to search for appropriate language files, e.g. mvc.en_US.ini.php.
	 * @var string
	 */
	protected $base = '';
	
	/**
	 * Pseudo-absolute path to the home directory of the language files, relative to system root.
	 * @var string
	 */
	protected $path = '/lang';
	
	/**
	 * Stores the contents of the INI file.
	 * @var array
	 */
	protected $ini = array();
	
	
	/**
	 * Constructs a new internationalization instance from the given file base name.
	 * The chosen language is stored in the session or in the user data. If a language file
	 * is not found, defaults to English (en).
	 * @param string $base name of the internationalization files
	 * @param string $path to the home directory of the language files. Pseudo-absolute path relative to the system root.
	 */
	protected function __construct( $base, $path = '/lang' ) {
		$this->base = $base;
		$this->path = $path;
	}
	
	/**
	 * Determines the most appropriate language file to read in based on the language preferences
	 * of the current user as determined in the $_SESSION['lang'], $_SESSION['lang2'] and
	 * $_SESSION['lang3'] variables.
	 * @return i18n This instance to enable method chaining.
	 */
	public function read( ) {
		$file = $this->findFile();
		
		// We should've already thrown an exception by here, should it even be empty.
		assert(!empty($file));
		
		// Instead of reading the file contents directly, we'll include the ini/php file and
		// intercept its output using the output buffer. This allows executing PHP code and
		// securing possibly sensible data (which on second thought sounds really stupid).
		ob_start();
		include($file);
		$contents = ob_get_contents();
		ob_end_clean();
		
		$this->ini = parse_ini_string($contents, true);
		return $this;
	}
	
	/**
	 * Attempts to find four particular files in this order:
	 *  - <base>.<lang>_<country>.ini.php
	 *  - <base>.<lang>_<country>.ini
	 *  - <base>.<lang>.ini.php
	 *  - <base>.<lang>.ini
	 * The first found file is returned.
	 * @param  string $lang    Language abbreviation, e.g. en or de
	 * @param  string $country Country abbreviation, e.g. US or GB
	 * @return string          The first matching file, or an empty string if none found.
	 */
	protected function findFile( $lang = '', $country = '' ) {
		// If no specific language has been passed, attempt to find one of the three preferred languages. Otherwise attempt to find en_US.
		// If none of these four languages could be found, throw an error.
		if( empty($lang) ) {
			if( !empty(self::$lang) ) {
				$file = $this->findFile(self::$lang, self::$country);
				if( !empty($file) ) {
					return $file;
				}
			}
			
			if( !empty(self::$lang2) ) {
				$file = $this->findFile(self::$lang2, self::$country2);
				if( !empty($file) ) {
					return $file;
				}
			}
			
			if( !empty(self::$lang3) ) {
				$file = $this->findFile(self::$lang3, self::$country3);
				if( !empty($file) ) {
					return $file;
				}
			}
			
			$file = $this->findFile('en', 'US');
			if( empty($file) ) {
				throw new LanguageNotFoundException($this->base, $this->path, self::$lang, self::$country, self::$lang2, self::$country2, self::$lang3, self::$country3);
			}
			return $file;
		}
		
		if( !empty($country) ) {
			// Otherwise we've been given a particular file which we'll attempt to find.
			$file = $this->checkAndGetFile("{$this->base}.{$lang}_{$country}.ini.php");
			if( !empty($file) ) {
				return $file;
			}
			
			$file = $this->checkAndGetFile("{$this->base}.{$lang}_{$country}.ini");
			if( !empty($file) ) {
				return $file;
			}
		}
		
		$file = $this->checkAndGetFile("{$this->base}.{$lang}.ini.php");
		if( !empty($file) ) {
			return $file;
		}
		
		$file = $this->checkAndGetFile("{$this->base}.{$lang}.ini");
		if( !empty($file) ) {
			return $file;
		}
		
		return '';
	}
	
	/**
	 * Checks if the given file exists in the language files' home directory. If not, returns an
	 * empty string.
	 * @param  string $file to check for existence.
	 * @return string       The file if it exists, otherwise an empty string.
	 */
	protected function checkAndGetFile( $file ) {
		$real = DIAMONDMVC_ROOT . str_replace('//', '/', "/{$this->path}/$file");
		if( file_exists($real) ) {
			return realpath($real);
		}
		return '';
	}
	
	/**
	 * Gets a translation by key in the named category. If no category is passed, the key is treated as a top level
	 * entry.
	 * @param  string $key      INI key to retrieve.
	 * @param  string $category Optional. INI category to find the key in.
	 * @return string           Requested translation
	 */
	public function get( $key, $category = '' ) {
		// If we're given a category, search for the category first, then for the key.
		if( !empty($category) ) {
			// Category exists?
			if( !isset($this->ini[$category]) ) {
				logMsg("Could not find INI category $category in language file {$this->path}/{$this->base}", 1, 5);
				return '';
			}
			
			// Key exists in category?
			if( !isset($this->ini[$category][$key]) ) {
				logMsg("Could not find $key in INI category $category in language file {$this->path}/{$this->base}", 1, 5);
				return '';
			}
			
			// Wooh!
			return $this->ini[$category][$key];
		}
		
		// No category given, attempt to find the key on top level
		if( !isset($this->ini[$key]) ) {
			logMsg("Could not find $key (no category) in language file {$this->path}/{$this->base}", 1, 5);
			return '';
		}
		
		// Found
		return $this->ini[$key];
	}
	
	
	/**
	 * Gets the currently chosen language (e.g. "en" in "en_GB" for English). Need not necessarily
	 * be a valid language string.
	 * @return string
	 */
	static public function getCurrentLanguage( ) {
		return self::$lang;
	}
	
	/**
	 * Gets the first alternatively preferred language of the current user. Need not necessarily be
	 * a valid language string.
	 * @return string
	 */
	static public function getFirstAlternativeLanguage( ) {
		return self::$lang2;
	}
	
	/**
	 * Gets the second alternatively preferred language of the current user. Need not necessarily be
	 * a valid language string.
	 * @return string
	 */
	static public function getSecondAlternativeLanguage( ) {
		return self::$lang3;
	}
	
	/**
	 * Gets the specific country as indicated in the later part of the language identifier
	 * (e.g. "GB" in "en_GB" for Great Britain). Need not be a valid country string.
	 * @return string
	 */
	static public function getCurrentCountry( ) {
		return self::$country;
	}
	
	/**
	 * Gets the first alternatively chosen specific country as indicated in the later part
	 * of the language identifier (e.g. "GB" in "en_GB" for Great Britain). Need not be a
	 * valid country string.
	 * @return string
	 */
	static public function getFirstAlternativeCountry( ) {
		return self::$country2;
	}
	
	/**
	 * Gets the first alternatively chosen specific country as indicated in the later part
	 * of the language identifier (e.g. "GB" in "en_GB" for Great Britain). Need not be a
	 * valid country string.
	 * @return string
	 */
	static public function getSecondAlternativeCountry( ) {
		return self::$country3;
	}
	
	/**
	 * Gets the language string as stored in memory.
	 * @return string
	 */
	static public function getLanguageString( ) {
		$result = self::$lang;
		if( !empty(self::$country) ) {
			$result .= '_' . self::$country;
		}
		return $result;
	}
	
	
	/**
	 * Initiate the i18n classes.
	 * 
	 * Reads in and parses chosen language information set in $_SESSION['lang'].
	 * 
	 * @param boolean $useCompressedLanguageFile Whether to use a single compressed language file to avoid searching for truckloads of files and boost performance.
	 */
	static public function init( ) {
		if( isset($_SESSION['lang']) ) {
			$tmp = self::parseLang($_SESSION['lang']);
			self::$lang     = $tmp[0];
			self::$country  = $tmp[1];
		}
		else {
			self::$lang     = '';
			self::$country  = '';
		}
		
		if( isset($_SESSION['lang2']) ) {
			$tmp = self::parseLang($_SESSION['lang2']);
			self::$lang2    = $tmp[0];
			self::$country2 = $tmp[1];
		}
		else {
			self::$lang2    = '';
			self::$country2 = '';
		}
		
		if( isset($_SESSION['lang3']) ) {
			$tmp = self::parseLang($_SESSION['lang3']);
			self::$lang3    = $tmp[0];
			self::$country3 = $tmp[1];
		}
		else {
			self::$lang3    = '';
			self::$country3 = '';
		}
	}
	
	/**
	 * Parses a language string for language and country abbreviations.
	 * @param  string $lang language string
	 * @return array        First item is the language abbrev., second item is the country abbreviation.
	 */
	static protected function parseLang( $lang ) {
		$result = array();
		if( preg_match('/([A-z]{2})(_([A-z]{2}))?/', $lang, $matches) ) {
			$result[0] = strToLower($matches[1]);
			$result[1] = isset($matches[3]) ? strToUpper($matches[3]) : '';
		}
		return $result;
	}
	
	
	/**
	 * Factory method to read the language files.
	 * 
	 * While this method currently does not serve any real purpose other than proxying the constructor,
	 * it will come handy in the future when compressing several commonly used together language files
	 * to save some performance.
	 * Up to four checks can be done for each requested language file. Compressing language files commonly
	 * loaded in the same session could drastically save time when running many extensions, each using
	 * its own language file.
	 * 
	 * This factory method then allows to browse the contents of a registry file read during initiation
	 * to redirect such language files to a single, common, compressed language file manually compiled in
	 * the backend.
	 * 
	 * But for now it simply constructs a new instance.
	 * @param  string $base Base name of the language files
	 * @param  string $path Path to the language files' home directory, relative to the website's root (with prefix '/')
	 * @return i18n         Instance holding the language information.
	 */
	static public function load( $base, $path = '/lang' ) {
		return (new i18n($base, $path))->read();
	}
	
}
