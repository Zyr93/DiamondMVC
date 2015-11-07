<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * A simple performance system tracking execution times and memory of the PHP instance.
 * A profile
 */
defined('DIAMONDMVC') or die();

final class Profiler {
	
	/**
	 * Stack of currently active sections. Does not hold expired sections.
	 * Simply used to keep track of which sub section we are currently in.
	 * Cannot store sibling sections.
	 * @var array
	 */
	static private $sections = array();
	
	
	private function __construct( ) {}
	
	static public function startSection( $name = '' ) {
		if( Config::main()->get('DEBUG_MODE') ) {
			$time = round(microtime(true));
			self::$sections[] = array('name' => $name, 'start' => $time, 'end' => 0, 'memStart' => round(memory_get_usage() / 1024), 'memEnd' => 0);
			logMsg("- Section $name start -", 1, false);
		}
	}
	
	static public function endSection( ) {
		if( Config::main()->get('DEBUG_MODE') ) {
			$time = round(microtime(true));
			
			if( empty(self::$sections) ) {
				logMsg("--- No section to end ---", 1, 5);
				return $this;
			}
			
			$section = array_pop(self::$sections);
			$section['end']    = $time;
			$section['memEnd'] = round(memory_get_usage() / 1024);
			
			logMsg("- Section {$section['name']} end -", 1, false);
			logMsg("Start: {$section['start']}s", 1, false);
			logMsg("End:   {$section['end']}s", 1, false);
			logMsg("Delta: " . ($section['end'] - $section['start']) . 's', 1, false);
			logMsg("Memory: {$section['memStart']} kiB - {$section['memEnd']} kiB", 1, false);
			logMsg("Median: " . (($section['memEnd'] + $section['memStart']) / 2) . ' kiB', 1, false);
		}
	}
	
	static public function getCurrentSection( ) {
		return self::$currentSection;
	}
	
	static public function getBaseSection( ) {
		return self::$baseSection;
	}
	
}
