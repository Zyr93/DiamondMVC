<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * A driving class for HTML representation of and user interaction with fields.
 * TODO: Once the template class is adapted for general purpose use, this class ought to inherit from it.
 * {@link #render()} currently is a weak substitute.
 */
defined('DIAMONDMVC') or die();

abstract class Field {
	
	/**
	 * Type of the field used for distinguishing them on client side.
	 * @var string
	 */
	private $type;
	
	/**
	 * HTML ID of the element. By default none. Automatically
	 * generated when needed but none provided.
	 * @var string
	 */
	protected $id = '';
	
	/**
	 * Input name unique to the <form>.
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * Form name of the filter operator input.
	 * @var string
	 */
	protected $nameOperators = '';
	
	/**
	 * Map of key-value pairs to attach to the resulting HTML as data attributes.
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Display text of this element. By default none, thus no
	 * associated <label> element would be generated.
	 * @var string
	 */
	protected $label = '';
	
	/**
	 * Tooltip shown while hovering the mouse over the element.
	 * @var string
	 */
	protected $tooltip = '';
	
	/**
	 * Default / previously stored value to auto fill out the field's HTML input with.
	 * @var string
	 */
	protected $default = '';
	
	/**
	 * Placeholder to use if no previous value was stored thus far.
	 * @var string
	 */
	protected $placeholder = '';
	
	/**
	 * Whether this field is hidden from the current user.
	 * @var boolean
	 */
	protected $hidden = false;
	
	/**
	 * Whether this field is meant to be searchable. If false, it should not be rendered
	 * as a searchable field in a GUI.
	 * @var boolean
	 */
	protected $searchable = true;
	
	/**
	 * Main client side script of this class. It's always the first element in
	 * {@link #scripts}.
	 * @var string
	 */
	private $mainScript = '';
	
	/**
	 * JavaScripts to run on client side.
	 * @var array
	 */
	private $scripts = array();
	
	/**
	 * Main stylesheet of this field. It's always the first element in {@link #stylesheets}.
	 * @var string
	 */
	private $mainStylesheet = '';
	
	/**
	 * Stylesheets to correctly render the field on client side.
	 * @var array
	 */
	private $stylesheets = array();
	
	
	/**
	 * Whether to use the canonic main script. If true, all new fields created
	 * beyond the assignment will automatically use the canonic main script.
	 * If false, all new fields created beyond the assignment will automatically
	 * use their individual main script.
	 * 
	 * When setting a new main script, the main script will be overridden with
	 * the canonic main script if this flag is set, otherwise the passed main
	 * script will be set instead.
	 * 
	 * Using a canonic main script is useful to minimize the client payload if
	 * many scripts are used for the various fields. Thus it is recommended to
	 * minimize and merge the client side field scripts into a single file and
	 * use that file as the canonic main script.
	 * @var boolean
	 */
	static public $useCanonicMainScript = false;
	
	/**
	 * URL of the canonic main script to use if and only if
	 * {@link #useCanonicMainScript} is set to true.
	 * @var string
	 */
	static public $canonicMainScript = '';
	
	/**
	 * Analogous to {@link #useCanonicMainScript}.
	 * @var boolean
	 */
	static public $useCanonicMainStylesheet = false;
	
	/**
	 * URL of the canonic main stylesheet to use if and only if
	 * {@link #useCanonicMainStylesheet} is set to true.
	 * @var string
	 */
	static public $canonicMainStylesheet = '';
	
	
	protected function __construct( $type ) {
		$this->type = $type;
		$scripts[] = &$this->mainScript;
		if( self::$useCanonicMainScript ) {
			$this->mainScript = self::$canonicMainScript;
		}
	}
	
	/**
	 * Gets the type of this field.
	 * @return string
	 */
	public function getType( ) {
		return $this->type;
	}
	
	/**
	 * Generates the field's HTML - including label - based on set parameters.
	 * 
	 * Note 1: If there's need for more variety in the render types, we should be able to easily
	 * adapt this methode to accept a string defining the render type... like "regular" and "filter".
	 * NOte 2: In case of note 1 we'll also need to adjust the Bootstrap column span variables.
	 * @return string HTML of this field, with parameters set.
	 */
	public function render( ) {
		$result      = '';
		$default     = !empty($this->default)     ? $this->default     : '';
		$placeholder = !empty($this->placeholder) ? $this->placeholder : '';
		
		$id = $this->id;
		if( empty($id) ) {
			$id = generateRandomName('A-Za-z0-9', 10);
		}
		
		$result .= '<div class="col1">';
		if( !empty($this->label) ) {
			$result .= '<label for="' . $id . '" data-toggle="tooltip" title="' . $this->tooltip . '">' . $this->label . '</label>';
		}
		$result .= '</div>';
		
		// For standardization, always wrap with the tooltip wrapper, even if the title attribute is empty.
		$result .= '<div class="field-meta col2" title="' . $this->tooltip . '" data-toggle="tooltip"';
		foreach( $this->data as $key => $value ) {
			$key = strToLower($key);
			$result .= " data-$key=\"$value\"";
		}
		$result .= '>';
		$result .= $this->_render($id, $this->name, $default, $placeholder);
		$result .= '</div>';
		
		return $result;
	}
	
	/**
	 * Generates HTML for displaying a filter - including operator selection and label - based on set parameters
	 * @return string Field filter HTML
	 */
	public function renderFilter( ) {
		$result = '';
		
		$id = $this->id;
		if( empty($id) ) {
			$id = generateRandomName('A-Za-z0-9', 10);
		}
		
		$result .= '<div class="col1 field-column-wrapper">';
		$result .= '<label for="' . $id . '" data-toggle="tooltip" title="' . $this->tooltip . '">' . $this->label . '</label>';
		$result .= '</div>';
		
		// Avoid a dropdown select if we only have a single operator.
		$result .= '<div class="col2 field-operators-wrapper">';
		$operators = $this->getFilterOperators();
		if( count($operators) === 1 ) {
			foreach( $operators as $operator => $display ) {
				$result .= '<input type="hidden" name="' . $this->nameOperators . '" value="' . $operator . '">';
				$result .= $display;
			}
		}
		else {
			$result .= '<select name="' . $this->nameOperators . '" id="' . $id . '_type" class="search-type">';
			foreach( $operators as $operator => $display ) {
				$result .= '<option value="' . $operator . '">' . $display . '</option>';
			}
			$result .= '</select>';
		}
		$result .= '</div>';
		
		$result .= '<div class="field-meta col3"';
		foreach( $this->data as $key => $value ) {
			$key = strToLower($key);
			$result .= " data-$key=\"$value\"";
		}
		$result .= '>';
		
		$result .= '<div class="current-filter">';
		foreach( $operators as $operator => $display ) {
			$result .= $this->_renderFilter($operator, $id, $this->name);
			break;
		}
		$result .= '</div>';
		
		$result .= '</div>';
		
		return $result;
	}
	
	/**
	 * Gets or sets this element's unique HTML ID.
	 * @param  boolean|string $id The new unique ID to set. Pass false to get the current unique ID. Defaults to false.
	 * @return string|Field       If used as a getter, returns the element's ID. If used as a setter, returns this instance to enable method chaining.
	 */
	public function id( $id = false ) {
		if( $id === false ) {
			return $this->id;
		}
		$this->id = $id;
		return $this;
	}
	
	/**
	 * Gets or sets this element's input name. The input name
	 * is required for standard-submission forms using <form> and <input> elements.
	 * @param  boolean|string $name Input element's name.
	 * @return string|Field         If used as a getter, returns the element current input name. If used as a setter, returns this instance to enable method chaining.
	 */
	public function name( $name = false ) {
		if( $name === false ) {
			return $this->name;
		}
		$this->name = $name;
		return $this;
	}
	
	/**
	 * Gets or sets the form name of the filter operator to use for filtering. Only considered
	 * when rendering with {@link #renderFilter()}.
	 * @param  boolean|string? $name Form name of the filter operator input.
	 * @return string|Field          The form name of the filter operator if used as a getter, this instance if used as a setter.
	 */
	public function nameOperators( $name = false ) {
		if( $name === false ) {
			return $this->nameOperators;
		}
		$this->nameOperators = $name;
		return $this;
	}
	
	/**
	 * Gets or sets this element's display text.
	 * If no unique ID is provided, a random 10 character ID will be generated
	 * during render.
	 * @param  boolean|string $label Display text to set. Pass false to get the current input name. Defaults to false.
	 * @return string|Field          If used as a getter, returns the element's ID. If used as a setter, returns this instance to enable method chaining.
	 */
	public function label( $label = false ) {
		if( $label === false ) {
			return $this->label;
		}
		$this->label = $label;
		return $this;
	}
	
	/**
	 * Gets or sets this element's tooltip.
	 * @param  boolean|string $label Tooltip to set. Pass false to get the current tooltip. Defaults to false.
	 * @return string|Field          If used as a getter, returns the element's tooltip. If used as a setter, returns this instance to enable method chaining.
	 */
	public function tooltip( $tooltip = false ) {
		if( $tooltip === false ) {
			return $this->tooltip;
		}
		$this->tooltip = $tooltip;
		return $this;
	}
	
	/**
	 * Sets the default value of this field. This is unlike the placeholder! The placeholder will be used if
	 * no value has been previously stored in a field and should shortly describe what is to be entered in
	 * the field.
	 * @param  string $default Default / previously stored value
	 * @return Field           This instance to enable method chaining.
	 */
	public function setDefault( $default ) {
		$this->default = $default;
		return $this;
	}
	
	/**
	 * Gets the default value of this field.
	 * @return string Default / previously stored value.
	 */
	public function getDefault( ) {
		return $this->default;
	}
	
	/**
	 * Gets or sets the placeholder of this field. The placeholder is used if no value has been previously
	 * entered and will be automatically cleared when the user attempts to enter something.
	 * @param  string|boolean $placeholder Placeholder string to set or `false` if using as a getter. Defaults to false.
	 * @return string|Field                Current placeholder if using as a setter, otherwise this instance to enable method chaining if using as a getter.
	 */
	public function placeholder( $placeholder = false ) {
		if( $placeholder === false ) {
			return $this->placeholder;
		}
		$this->placeholder = $placeholder;
		return $this;
	}
	
	/**
	 * Sets this field as hidden from the current user.
	 * @return Field This instance to enable method chaining.
	 */
	public function hide( ) {
		$this->hidden = true;
		return $this;
	}
	
	/**
	 * Sets this field as not hidden from the current user.
	 * @return Field This instance to enable method chaining.
	 */
	public function show( ) {
		$this->hidden = false;
		return $this;
	}
	
	/**
	 * Checks if this Field is hidden to the current user.
	 * @return boolean
	 */
	public function isHidden( ) {
		return !!$this->hidden;
	}
	
	/**
	 * Sets whether this field is meant to be searched.
	 * @param  boolean $searchable Whether or not this field is meant to be searched.
	 * @return Field               This instance to enable method chaining.
	 */
	public function setSearchable( $searchable ) {
		$this->searchable = $searchable;
		return $this;
	}
	
	/**
	 * Checks if this field is meant to be searchable.
	 * @return boolean
	 */
	public function isSearchable( ) {
		return $this->searchable;
	}
	
	/**
	 * Adds custom data to or gets custom data from the field.
	 * If an array is passed $value is ignored. Each associative mapping of the array will be added individually.
	 * @param  array|string? $key   Name of the custom data. If omitted the method acts as a getter.
	 * @param  string?       $value 
	 * @return Field|array          This instance to enable method chaining when used as a setter, or the custom data associated with this field if used as a getter.
	 */
	public function data( $key = '', $value = false ) {
		if( empty($key) ) {
			return $this->data;
		}
		
		if( is_array($key) ) {
			foreach( $key as $first => $second ) {
				$this->data[$first] = $second;
			}
		}
		else if( is_string($key) and $value === false ) {
			return isset($this->data[$key]) ? $this->data[$key] : '';
		}
		else {
			$this->data[$key] = $value;
		}
		return $this;
	}
	
	/**
	 * Reads the value of the named custom data. Returns false if the
	 * given key was not found.
	 * @param  string         $key Name of the custom data.
	 * @return boolean|string      Value of the custom data or false if it wasn't found.
	 */
	public function getData( $key ) {
		return isset($this->data[$key]) ? $this->data[$key] : false;
	}
	
	/**
	 * Gets all the custom data stored with this field.
	 * @return array Associative array of custom data.
	 */
	public function getAllData( ) {
		return $this->data;
	}
	
	/**
	 * Removes the given custom data from the field.
	 * @param  string $key Name of the custom data to remove.
	 * @return Field       This instance to enable method chaining.
	 */
	public function removeData( $key ) {
		unset($this->data[$key]);
		return $this;
	}
	
	/**
	 * Generates the HTML of the actual input element.
	 * This differs from {@link #render()} in that it only generates the input element's HTML code while
	 * {@link #render()} generates a standardized HTML structure featuring a label and tooltip, next to
	 * the element HTML. {@link #render()} internally calls this method.
	 * 
	 * @param  string $id          HTML ID to use.
	 * @param  string $name        Input name to use.
	 * @param  string $default     Default / previously stored value to auto fill out the HTML input with.
	 * @param  string $placeholder Placeholder value if no value was previously stored.
	 * @return string              HTML of the actual input element.
	 */
	abstract public function _render( $id, $name, $default, $placehoder );
	
	/**
	 * Generates the HTML to display the input for filtering the represented field.
	 * @param  string $operator Filter operator to render the filter for.
	 * @param  string $id       HTML ID to use
	 * @param  string $name     HTML form name to use
	 * @return string           Generated HTML
	 */
	public function _renderFilter( $operator, $id, $name ) {
		$result = '<input type="text" name="' . $name . '"';
		if( !empty($id) ) {
			$result .= ' id="' . $id . '"';
		}
		$result .= '>';
		return $result;
	}
	
	
	/**
	 * Gets the operators which can be used when filtering this field.
	 * @return array Associative array mapping operator string IDs with their respective display texts.
	 */
	public function getFilterOperators( ) {
		return array('=' => '=', '<' => 'bis', '>' => 'ab', '<=' => 'bis einschließlich', '>=' => 'ab einschließlich', 'range' => 'von ... bis', 'has' => 'enthält');
	}
	
	
	/**
	 * Sets the main stylesheet for this field. It's always the first item of the
	 * internal sheets array.
	 * If {@link #useCanonicMainStylesheet} is set to true, the {@link #canonicMainStylesheet}
	 * will be used instead of the passed URL.
	 * @param string $url URL of the main stylesheet.
	 */
	protected function setMainStylesheet( $url ) {
		if( self::$useCanonicMainStylesheet ) {
			$this->mainStylesheet = self::$canonicMainStylesheet;
		}
		else {
			$this->mainStylesheet = $url;
		}
		return $this;
	}
	
	/**
	 * Gets stylesheets used for this field for rendering both the regular input field and filter.
	 * @return array of URLs of stylesheets.
	 */
	public function getStylesheets( ) {
		return $this->stylesheets;
	}
	
	/**
	 * Adds the given URL to the list of stylesheets to load.
	 * @param  string $url
	 * @return Field       This instance to enable method chaining.
	 */
	public function addStylesheet( $url ) {
		$this->stylesheets[] = $url;
		return $this;
	}
	
	
	/**
	 * Sets the main client side script for this field. It's always the first item of
	 * the internal scripts array.
	 * If {@link #useCanonicMainScript} is set to true, the {@link #canonicMainScript}
	 * will be used instead of the passed URL.
	 * @param string $url URL of the main client side script.
	 */
	protected function setMainScript( $url ) {
		if( self::$useCanonicMainScript ) {
			$this->mainScript = self::$canonicMainScript;
		}
		else {
			$this->mainScript = $url;
		}
		return $this;
	}
	
	/**
	 * Gets the JavaScripts required on client side for best functioning of the field.
	 * @return array of module IDs and dependencies or URLs of JavaScripts.
	 */
	public function getScripts( ) {
		return $this->scripts;
	}
	
	/**
	 * Adds the given module ID or URL to the scripts to load.
	 * @param  string $script Module ID or URL of the JavaScript to add.
	 * @return Field          This instance to enable method chaining.
	 */
	public function addScript( $script ) {
		$this->scripts[] = $script;
		return $this;
	}
	
	
	/**
	 * Gets the filter for the given filter type (likely filtering operator).
	 * @param  string       $column Name of the database column to perform the filtering on.
	 * @param  string       $type   of the filtering operation.
	 * @param  string|array $values A single value or an array of values depending on user input as defined per {@link #renderFilter()}.
	 * @return array                The first item provides the filter, the second item stores the bind definition and the third item stores the bind parameters in form of another array. Return anything that evaluates to false to ignore this filter.
	 */
	public function getFilter( $column, $type, $values ) {
		if( empty($values) ) {
			return null;
		}
		
		switch( $type ) {
		case '=':
			return array($column . '=?', 's', array($values));
			
		case '>':
		case '>=':
		case '<':
		case '<=':
			return array($column . $type . '?', 's', array($values));
			
		case 'range':
			return !is_array($values) ? null : array($column . '>=? AND ' . $column . '<=?', 'ss', array($values[0], $values[1]));
		
		case 'has':
			return array($column . ' LIKE ?', 's', '%' . $values . '%');
			
		default:
			logMsg('Unsupported filter operation ' . $type, 4, 0);
			return null;
		}
	}
	
	/**
	 * If any non-empty string is returned, that string will be used as the full filter of the search.
	 * It can be used to search through multiple field values instead of filtering individual ones.
	 * @return string Non-empty full filter to apply to the search. Omit if opting to the default behavior.
	 */
	public function getCustomFullFilter( ) {
		return '';
	}
	
	/**
	 * Whether to use the custom filtering algorithm specified in {@link #filter()}.
	 * Using a callback is likely less efficient. Avoid if possible.
	 * @param  string       $operator Filter operator in question. Allows using filter callback for certain operations only.
	 * @param  string|array $values   Values for which to filter. These are not passed to filter already at this point but rather to determine whether invoking a custom callback is really necessary.
	 * @return boolean
	 */
	public function useFilterCallback( $operator, $values ) {
		return false;
	}
	
	/**
	 * Filters the given array of {@link ModelRow}s using a customized algorithm.
	 * @param  database    $db       Database interface object to use to access data associated with the rows.
	 * @param  array        $rows     Array of {@link ModelRow}s containing ALL data to be filtered.
	 * @param  string       $operator Filter operator in question.
	 * @param  string|array $values   A single or multiple values to search for.
	 * @return array                  Filtered array of {@link ModelRow}s.
	 */
	public function filter( $db, $rows, $operator, $values ) {
		return $rows;
	}
	
}
