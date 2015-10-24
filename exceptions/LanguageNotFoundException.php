<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

class LanguageNotFoundException extends BaseException {
	
	public function __construct( $base, $path, $lang1, $country1, $lang2, $country2, $lang3, $country3, $code = 0, $prev = null ) {
		$langs = array();
		if( !empty($lang1) ) {
			if( empty($country1) ) {
				$langs[] = $lang1;
			}
			else {
				$langs[] = "$lang1_$country1";
			}
		}
		if( !empty($lang2) ) {
			if( empty($country2) ) {
				$langs[] = $lang2;
			}
			else {
				$langs[] = "$lang2_$country2";
			}
		}
		if( !empty($lang3) ) {
			if( empty($country3) ) {
				$langs[] = $lang3;
			}
			else {
				$langs[] = "$lang3_$country3";
			}
		}
		
		parent::__construct("Language not found", "Languages " . implode(', ', $langs) . " and en_US not found for $base in $path", $code, $prev);
	}
	
}
