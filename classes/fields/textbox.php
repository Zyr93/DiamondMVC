<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * @param View $this
 * 
 * A simplistic text box field. No special scripts.
 */
defined('DIAMONDMVC') or die();

class FieldTextBox extends Field {
	
	public function __construct( ) {
		parent::__construct('textbox');
	}
	
	public function _render( $id, $name, $default, $placeholder ) {
		$result = '<input type="text" name="' . $name . '" placeholder="' . htmlspecialchars(htmlspecialchars_decode($placeholder)) . '" class="form-control"';
		if( !empty($id) ) {
			$result .= ' id="' . $id . '"';
		}
		if( !empty($default) ) {
			$result .= ' value="' . htmlspecialchars(htmlspecialchars_decode($default)) . '"';
		}
		$result .= '>';
		return $result;
	}
	
	public function _renderFilter( $operator, $id, $name ) {
		return $this->_render($id, $name, '', '');
	}
	
}
