<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 * 
 * Builds HTML forms based on the fields defined in the database.
 */
defined('DIAMONDMVC') or die();

DiamondMVC::instance()->loadLibrary('fields');

class FormBuilder {
	
	/**
	 * DBO to use to access the field information.
	 * @var database
	 */
	protected $db = null;
	
	/**
	 * Semi-indexed array mapping field types to respective class names.
	 * @var array
	 */
	static protected $fieldmap = array();
	
	/**
	 * Stores all fields of the form being currently built.
	 * @var array
	 */
	protected $fields = array();
	
	
	public function __construct( $db = null ) {
		$this->db = $db;
	}
	
	/**
	 * Registers standard fields and triggers the formbuilder::register-fields
	 * {@link Event} for plugins to register additional fields.
	 */
	static public function registerFields( ) {
		self::registerField(0, 'FieldTextBox');
		self::registerField(1, 'FieldFulltext');
		self::registerField(2, 'FieldSearchBox');
		DiamondMVC::instance()->trigger('formbuilder::register-fields');
		
		logMsg("FormBuilder: registered fields:\n" . print_r(self::$fieldmap, true), 1, 0);
	}
	
	/**
	 * Maps a field type to its field class.
	 * @param $integer Unique field type. Will override if already in use!
	 * @param $class   Name of the class to use. Class must exist, otherwise the mapping will not be saved!
	 */
	static public function registerField( $type, $class ) {
		if( class_exists($class) ) {
			if( isset($fieldmap[$type]) ) {
				logMsg("FormBuilder: Warning! Overriding field mapping $type -> " . self::$fieldmap[$type] . " with $class", 4, false);
			}
			self::$fieldmap[$type] = $class;
		}
		else {
			logMsg("FormBuilder: invalid class $class passed", 4);
		}
	}
	
	/**
	 * Removes a mapping.
	 * @param integer $type Unique field type to remove.
	 */
	static public function unregisterField( $type ) {
		if( isset(self::$fieldmap[$type]) ) {
			logMsg("FormBuilder: Removing field mapping $type -> " . self::$fieldmap[$type], 4);
			unset(self::$fieldmap[$type]);
		}
		else {
			logMsg("FormBuilder: Nothing to do. Field mapping $type not found.", 4);
		}
	}
	
	/**
	 * Field object factory using the field type as defined in the database.
	 * @param  integer $type Unique identifier of the field.
	 * @return Field         Factory generated field class or null if no such type was registered.
	 */
	static public function fromType( $type ) {
		if( !isset(self::$fieldmap[$type]) ) {
			return null;
		}
		
		$class = self::$fieldmap[$type];
		return new $class;
	}
	
	/**
	 * Appends a Field instance to the form in construction.
	 * @param  Field       $field Field to add.
	 * @return FormBuilder        This instance to enable method chaining.
	 */
	public function addElement( Field $field ) {
		$this->fields[] = $field;
		return $this;
	}
	
	/**
	 * Removes a field at the given index within this FormBuilder's field list.
	 * @param  integer $index of the field to remove.
	 * @return Field          The removed field or null if index < 0 or index > length of the field list.
	 */
	public function removeElement( $index ) {
		if( $index < 0 or $index > count($this->fields) ) {
			return null;
		}
		
		$field = $this->fields[$index];
		array_splice($this->fields, $index, 1);
		return $field;
	}
	
	/**
	 * Removes the first occurrence of a field with the given name.
	 * @param  string $name of the field to remove.
	 * @return Field        The removed field or null if not found.
	 */
	public function removeElementByName( $name ) {
		foreach( $this->fields as $index => $field ) {
			if( $field->name() === $name ) {
				array_splice($this->fields, $index, 1);
				return $field;
			}
		}
		return null;
	}
	
	/**
	 * Removes the field with the given HTML ID. It should be unique anyway.
	 * @param  string $id Unique HTML ID of the field to remove.
	 * @return Field      The removed field or null if not found.
	 */
	public function removeElementById( $id ) {
		foreach( $this->fields as $index => $field ) {
			if( $fieldName->id() === $id ) {
				array_splice($this->fields, $index, 1);
				return $field;
			}
		}
		return null;
	}
	
	/**
	 * Gets a field by its index.
	 * @param  integer $index of the field within this FormBuilder's field list.
	 * @return Field          The Field instance or null if index < 0 or index > length of field list.
	 */
	public function getField( $index ) {
		if( $index < 0 or $index > count($this->fields) ) {
			return null;
		}
		return $this->fields[$index];
	}
	
	/**
	 * Gets all fields named by the given name, optionally including arrays - i.e. 'name[]' === 'name'.
	 * @param  string  $name          of the fields to search for.
	 * @param  boolean $includeArrays Optional. Whether array brackets are ignored when matching names. Defaults to false.
	 * @return array                  Array of matched fields.
	 */
	public function getFieldsByName( $name, $includeArrays = false ) {
		$result = array();
		if( $includeArrays ) {
			$name = $this->_stripLastArrayBrackets($name);
			foreach( $this->fields as $field ) {
				if( $field->name() === $name ) {
					$result[] = $field;
				}
			}
		}
		else {
			foreach( $this->fields as $field ) {
				if( $field->name() === $name ) {
					$result[] = $field;
				}
			}
		}
		return $result;
	}
	
	/**
	 * Strips the last array-indicating brackets off a field name, including the index within.
	 * @param  string $name Field name to strip brackets off.
	 * @return string       Stripped field name.
	 */
	protected function _stripLastArrayBrackets( $name ) {
		$pos = strrpos($name, '[');
		return $pos === false ? $name : substr($name, 0, $pos);
	}
	
	/**
	 * Gets the Field with the given HTML ID. The ID ought to be unique.
	 * @param  string $id Unique HTML ID of the field.
	 * @return Field      The field instance or null if not found.
	 */
	public function getFieldById( $id ) {
		foreach( $this->fields as $field ) {
			if( $field->id() === $id ) {
				return $field;
			}
		}
		return null;
	}
	
	/**
	 * Builds the HTML of the form.
	 * @return string
	 */
	public function build( ) {
		return 'TODO';
	}
	
	public function __toString( ) {
		return $this->build();
	}
	
}
