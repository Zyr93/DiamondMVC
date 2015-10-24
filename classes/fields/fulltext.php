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

class FieldFulltext extends Field {
	
	public function __construct( ) {
		parent::__construct('fulltext');
	}
	
	public function _render( $id, $name, $default, $placeholder ) {
		return '<textarea name="' . $name . '" placeholder="' . htmlspecialchars(htmlspecialchars_decode($placeholder)) . '" id="' . $id . '" class="form-control">' . $default . '</textarea>';
	}
	
	public function _renderFilter( $operator, $id, $name ) {
		return '<input type="text" name="' . $name . '" id="' . $id . '" class="form-control">';
	}
	
	public function getFilterOperators( ) {
		return array('has' => 'enthält');
	}
	
	public function getFilter( $column, $type, $values ) {
		return empty($values) ? null : array($column . ' LIKE ?', 's', array('%' . $values . '%'));
	}
	
}
