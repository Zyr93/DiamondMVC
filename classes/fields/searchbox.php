<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * A form input which supports autocompletion based on the user's input so far.
 * Filtering is limited to available input values.
 * 
 */
defined('DIAMONDMVC') or die();

class FieldSearchBox extends Field {
	
	/**
	 * URL to query the search at.
	 * @var string
	 */
	protected $searchUrl = '';
	
	
	public function __construct( ) {
		parent::__construct('searchbox');
		$this->addScript('diamondmvc/selectize.values');
		$this->addStylesheet('/assets/selectize/dist/css/selectize.bootstrap3.css');
	}
	
	public function _render( $id, $name, $default, $placeholder ) {
		$result = '<input class="selectize-searchbox form-control" type="text" name="' . $name . '"';
		if( !empty($id) ) {
			$result .= ' id="' . $id . '"';
		}
		if( !empty($default) ) {
			$result .= ' value="' . htmlspecialchars($default) . '"';
		}
		if( !empty($placeholder) ) {
			$result .= ' placeholder="' . htmlspecialchars($placeholder) . '"';
		}
		
		$result .= $this->generateDataString();
		
		$result .= '>';
		return $result;
	}
	
	public function _renderFilter( $operator, $id, $name ) {
		switch( $operator ) {
		default:
		case '=':
			return $this->_render($id, $name, '', '');
			
		case '<':
		case '>':
		case '<=':
		case '>=':
			return '<input id="' . $id . '" name="' . $name . '" class="selectize-searchbox form-control" ' . $this->generateDataString() . ' data-searchbox-create="1">';
			
		case 'range':
			return '<input id="' . $id . '" name="' . $name . '[]" class="selectize-searchbox form-control" ' . $this->generateDataString() . ' data-searchbox-create="1"> - <input id="' .
				$id . '_2" name="' . $name . '[]" class="selectize-searchbox form-control" ' . $this->generateDataString() . ' data-searchbox-create="1">';
			
		case 'has':
			return '<input id="' . $id . '" name="' . $name . '" class="form-control" ' . $this->generateDataString() . '>';
		}
	}
	
	protected function generateDataString( $exclude = array() ) {
		$result = '';
		foreach( $this->data as $key => $value ) {
			if( left($key, 7) === 'search-' and !in_array($key, $exclude) ) {
				$result .= ' data-' . $key . '="' . $value . '"';
			}
		}
		return $result;
	}
	
}
