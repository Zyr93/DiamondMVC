<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 2.5 (https://creativecommons.org/licenses/by-sa/2.5/)
 */
defined('DIAMONDMVC') or die();

abstract class Controller {
	
	/**
	 * Die zu verwendende databaseschnittstelle. Standardmäßig
	 * Config::main()->getDbo().
	 * @var database
	 */
	protected $db = null;
	
	private $_controllername = '';
	
	/**
	 * Assoziatives Array, welches die bisher erzeugten Views cachet.
	 * @var array
	 */
	protected $_controllerviews = array();
	
	/**
	 * Der Titel der Website wird definiert durch die jeweilige Aktion.
	 * @var string
	 */
	protected $title = '';
	
	/**
	 * Sammlung der Nachrichten, die an den Klienten übermittelt werden sollen.
	 * Kein HTML!
	 * @var array
	 */
	protected $messages = array();
	
	/**
	 * Das Resultat einer Aktion. Dies kann mit {@link #getResult()} ausgelesen werden und
	 * per JSON an Klienten gesendet werden, oder in Views dargestellt werden.
	 * @var array
	 */
	protected $result = array();
	
	/**
	 * Assoziatives Array diverser Module zur Anzeige auf der Website.
	 * Assoziiert Modul-Positionen mit einem Array aus Modulen.
	 * @var array
	 */
	protected $modules = array();
	
	/**
	 * Speichert die durchgeführte aufgerufene Aktion damit die entsprechende View geladen
	 * werden kann.
	 * @var string
	 */
	protected $action = 'main';
	
	
	/**
	 * Erzeugt einen neuen Kontroller mit Namen. Der Name gibt an, von wo er die Views
	 * laden wird.
	 * @param string $name Name des Kontrollers.
	 */
	protected function __construct( $name, $db = null ) {
		$this->_controllername = $name;
		$this->title = Config::main()->get('DEFAULT_CONTROLLER_TITLE');
		$this->db = is_null($db) ? Config::main()->getDbo() : $db;
	}
	
	/**
	 * Liefert den Namen dieses Kontrollers wieder.
	 * @return string Name dieses Kontrollers
	 */
	public function getName( ) {
		return $this->_controllername;
	}
	
	/**
	 * Generates the path to the view class.
	 * @param  string $view name of the view
	 * @param  string $type of the view. By default html.
	 * @return string       Absolute path to the view, if it exists, otherwise an empty string.
	 */
	public function getViewPath( $view = '', $type = 'html' ) {
		// Are there even any views?
		if( !is_dir(DIAMONDMVC_ROOT . '/views/' . $this->_controllername) ) {
			return '';
		}
		
		if( empty($view) ) {
			$view = $this->action === 'main' ? 'default' : $this->action;
		}
		
		$type = strToLower($type);
		$typeSuffix = (!empty($type) and $type !== 'html') ? ".$type" : '';
		
		$jail = realpath(DIAMONDMVC_ROOT . '/views/' . $this->_controllername);
		$path = jailpath($jail, "$view$typeSuffix.php");
		
		return $path;
	}
	
	/**
	 * Gets the path to the given template with type
	 * @param  string $template
	 * @param  string $type
	 * @return string The path to the template if it exists, otherwise an empty string.
	 */
	public function getTemplatePath( $template = '', $type = '' ) {
		$jail = realpath(DIAMONDMVC_ROOT . "/views/$this->_controllername/templates");
		if( $jail === false ) {
			return '';
		}
		
		if( empty($template) ) {
			$template = $this->action === 'main' ? 'default' : $this->action;
		}
		
		$type       = strToLower($type);
		$typeSuffix = (!empty($type) and $type !== 'html') ? ".$type" : '';
		$path       = jailpath($jail, "$template$typeSuffix.php");
		
		return $path;
	}
	
	/**
	 * Holt eine bestimmte View oder erzeugt sie, falls noch nicht vorhanden.
	 * @param  string $view Name der View.
	 * @param  string $type Typ der View, standardmäßig HTML.
	 * @return View
	 */
	public function getView( $view = '', $type = 'html' ) {
		if( empty($view) ) {
			$view = $this->action === 'main' ? 'default' : $this->action;
		}
		
		$type = strToLower($type);
		if( empty($type) ) {
			$type = 'html';
		}
		
		// Gecachete View laden.
		if( isset($this->_controllerviews[$view]) and isset($this->_controllerviews[$view][$type]) ) {
			$result = $this->_controllerviews[$view][$type];
		}
		// View laden und cachen.
		else {
			logMsg("Loading view {$this->getName()}/$view.$type.php", 1, false);
			
			@include_once($this->getViewPath($view));
			$class = "View" . $this->getName() . $view;
			
			if( !isset($this->_controllerviews[$view]) ) {
				$this->_controllerviews[$view] = array();
			}
			
			// Spezifische View erzeugen
			if( class_exists($class) ) {
				$result = new $class($this, $type);
				$this->_controllerviews[$view][$type] = $result;
			}
			// Generische View erzeugen
			else {
				$result = new View($this, $view, $type);
				$this->_controllerviews[$view][$type] = $result;
			}
		}
		return $result;
	}
	
	/**
	 * Checks if the requested view exists.
	 * @param  string  $view Name of the view
	 * @param  string  $type of the view. By default html / empty. (See {@link #getViewPath()}).
	 * @return boolean       True if the view of the requested type exists, otherwise false.
	 */
	public function hasView( $view = '', $type = '' ) {
		return file_exists($this->getViewPath($view, $type));
	}
	
	/**
	 * Checks if the requested template exists.
	 * @param  string  $template Name of the template.
	 * @param  string  $type     of the template, by default HTML.
	 * @return boolean           True if the template exists, otherwise false.
	 */
	public function hasTemplate( $template = '', $type = '' ) {
		return file_exists($this->getTemplatePath($template, $type));
	}
	
	/**
	 * Performs the given action, if it exists. The action saves its result in {@link #result},
	 * retrievable through {@link #getResult()}.
	 * 
	 * This method is supposed to act as a sort of factory method for all actions a controller
	 * provides. As such the actions themselves ought to be protected from external direct access.
	 * However, if you wish to directly expose your actions, be sure to clean the controller's
	 * state using {@link #cleanup()}.
	 * @param  string $action  Name of the action to perform
	 * @param  mixed  $args... Arguments to pass to the action. Usually none
	 * @return mixed           Result of the action, usually stored in {@link #result}.
	 */
	public function action( ) {
		$args = func_get_args();
		$action = array_shift($args);
		
		$this->cleanup();
		
		if( method_exists($this, "action_$action") ) {
			$this->action = $action;
			DiamondMVC::instance()->trigger('controller::action', $this);
			return call_user_func_array(array($this, "action_$action"), $args);
		}
		return null;
	}
	
	/**
	 * Resets the controller's state, i.e. title, result, modules and messages.
	 */
	protected function cleanup( ) {
		$this->title    = '';
		$this->result   = null;
		$this->modules  = array();
		$this->messages = array();
	}
	
	/**
	 * Adds a message to transmit to the client.
	 * @param  string     $title of the message
	 * @param  string     $msg   Message body
	 * @param  string     $level Message importance, by default 'info' = fairly unimportant
	 * @return Controller        This instance to enable method chaining.
	 */
	public function addMessage( $title, $msg, $level = 'info' ) {
		// Level-Synonyme
		$level = strToLower($level);
		switch( $level ) {
		case 'success':
		case 'done':
			$level = 'success';
			break;
			
		case 'info': default:
		case 'notice':
		case 'message':
		case 'msg':
			$level = 'info';
			break;
			
		case 'warning':
		case 'warn':
			$level = 'warning';
			break;
			
		case 'danger':
		case 'alert':
		case 'fail':
		case 'error':
		case 'exception':
			$level = 'danger';
			break;
		}
		
		$this->messages[] = array('level' => $level, 'title' => $title, 'msg' => $msg );
		return $this;
	}
	
	/**
	 * Gets the messages to transmit to the client.
	 * @return array
	 */
	public function getMessages( ) {
		return $this->messages;
	}
	
	/**
	 * Generates the HTML to display a message.
	 * @return string
	 */
	public function getMessagesHTML( ) {
		$result = '';
		foreach( $this->messages as $message ) {
			$result .= '<div class="alert alert-' . $message['level'] . '" role="alert">';
			$result .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
			$result .= '<strong>' . $message['title'] . '</strong> ' . $message['msg'];
			$result .= '</div>';
		}
		return $result;
	}
	
	
	/**
	 * Gets the result of the last action, if any.
	 * @return mixed
	 */
	public function getResult( ) {
		return $this->result;
	}
	
	/**
	 * Liefert die im Rahmen der zuletzt durchgeführten Aktion definierten Sidebar-Module
	 * zurück.
	 * @return array Array aus Modulen
	 */
	public function getSidebar( ) {
		return $this->getModules('sidebar');
	}
	
	/**
	 * Liest den Titel der Website definiert durch die Aktion aus.
	 * @return string Titel der Website.
	 */
	public function getTitle( ) {
		return $this->title;
	}
	
	/**
	 * Hängt ein Modul an das Ende der Sidebar an.
	 * @param  Module     $module Anzuhängendes Modul.
	 * @return Controller         Diese Instanz zur Methodenverkettung.
	 */
	public function addSidebarModule( $module ) {
		if( !isset($this->modules['sidebar']) ) {
			$this->modules['sidebar'] = array();
		}
		$this->modules['sidebar'][] = $module;
		return $this;
	}
	
	/**
	 * Hängt ein Modul an die gegebene Position an. Falls noch kein Modul an dieser
	 * Position eingetragen ist, wird diese erzeugt.
	 * @param  string $position [description]
	 * @param   $module   [description]
	 * @return [type]           [description]
	 */
	public function appendModule( $position, $module ) {
		if( !$this->countModules($position) ) {
			$this->modules[$position] = array();
		}
		$this->modules[$position][] = $module;
		return $this;
	}
	
	/**
	 * Fügt ein Modul in die gegebene Position vor das Modul mit gegebenem Index ein.
	 * Falls das Modul bereits in der Liste steht, wird es nicht vorher entfernt!
	 * Falls der Index die Länge der Liste überschreitet oder ihr gleicht, wird das
	 * Modul angehängt.
	 * @param  string     $position Modul-Position
	 * @param  Module     $module   Einzufügendes Modul.
	 * @param  integer    $index    Index des Moduls, vor welches das neue Modul eingefügt werden soll.
	 * @return Controller           Diese Instanz zur Methodenverkettung.
	 */
	public function insertModule( $position, $module, $index ) {
		if( $this->countModules($position) <= $index ) {
			return $this->appendModule($position, $module);
		}
		else {
			array_splice($this->modules[$position], $index, 0, $module);
			return $this;
		}
	}
	
	/**
	 * Entfernt das Modul an gegebener Position. Falls der Index nicht existiert,
	 * geschieht nichts.
	 * @param  string     $position Modul-Position
	 * @param  integer    $index    Modul-Index
	 * @return Controller           Diese Instanz zur Methodenverkettung.
	 */
	public function removeModule( $position, $index ) {
		if( $this->countModules($position) > $index ) {
			array_splice($this->modules[$position], $index, 1);
		}
		return $this;
	}
	
	/**
	 * Holt das Modul an gegebener Position und gegebenem Index.
	 * @param  string  $position Modul-Position
	 * @param  integer $index    Modul-Index
	 * @return Module            Das Modul oder null falls entweder Position oder Index oder beide nicht existieren.
	 */
	public function getModule( $position, $index ) {
		return (isset($this->modules[$position]) and isset($this->modules[$position][$index])) ? $this->modules[$position][$index] : null;
	}
	
	/**
	 * Holt alle Module an gegebener Position.
	 * @param  string $position Modul-Position
	 * @return array            Array aus Modulen. Das Array ist leer, falls die Position nicht existiert.
	 */
	public function getModules( $position ) {
		return isset($this->modules[$position]) ? $this->modules[$position] : array();
	}
	
	/**
	 * Liefert sämtliche definierte Positionen und die dort registrierten Module zurück.
	 * @return array Assoziatives Array aus Positionen und Modulen.
	 */
	public function getAllModules( ) {
		return $this->modules;
	}
	
	/**
	 * Zählt die Anzahl der Module an der gegebenen Position. Falls die Position nicht
	 * existiert, wird 0 zurück gegeben.
	 * @param  string  $position Modul-Position
	 * @return integer           Anzahl der Module an der gegebenen Position.
	 */
	public function countModules( $position ) {
		return isset($this->modules[$position]) ? count($this->modules[$position]) : 0;
	}
	
}
