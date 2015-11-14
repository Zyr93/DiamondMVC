<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 * 
 * TODO: Localization
 */
defined('DIAMONDMVC') or die();

final class DiamondMVC {
	
	/**
	 * Singleton instance
	 * @var DiamondMVC
	 */
	static private $_instance = null;
	
	/**
	 * Configuration to use
	 * @var Config
	 */
	private $config = null;
	
	/**
	 * Database interface object to use
	 * @var database
	 */
	private $db = null;
	
	/**
	 * Currently logged in user, if any
	 * @var ModelUser
	 */
	private $user = null;
	
	/**
	 * List of loaded plugins
	 * @var array
	 */
	private $plugins = array();
	
	/**
	 * Map of <event => listeners> pairs.
	 * @var array
	 */
	private $eventListeners = array();
	
	/**
	 * Routed controller
	 * @var Controller
	 */
	private $controller = null;
	
	/**
	 * User requested action and/or event routed action.
	 * @var string
	 */
	private $action = '';
	
	/**
	 * User requested view and/or event routed view.
	 * @var string
	 */
	private $view = '';
	
	/**
	 * User requested template and/or event routed template.
	 * @var string
	 */
	private $tpl = '';
	
	
	private function __construct( $config = null ) {
		if( !($config instanceof Config) ) {
			$config = Config::main();
		}
		$this->config = $config;
		$this->db     = $config->getDBO();
	}
	
	
	/**
	 * Retrieves the singleton instance
	 * @return DiamondMVC
	 */
	static public function instance( ) {
		if( !self::$_instance ) {
			self::$_instance = new DiamondMVC();
		}
		return self::$_instance;
	}
	
	/**
	 * Checks whether the system has been installed. It does so by checking whether the
	 * "/firstinstall" directory in the system root exists. It should contain the installation
	 * script. After the installation this directory is removed to prevent accidental
	 * reinstallation of the system.
	 * @return boolean
	 */
	public function isInstalled( ) {
		return !is_dir(DIAMONDMVC_ROOT . '/firstinstallation');
	}
	
	/**
	 * Main server procedure. index.php in the system's root calls this method by default.
	 */
	public function run( ) {
		$db = Config::main()->getDbo();
		
		// Prepare necessities.
		$this->user = new ModelUser($db);
		if( $this->user->isLoggedIn() ) {
			$this->user->restoreSession()->refreshSession();
		}
		Cache::init($db);
		
		Profiler::startSection('DiamondMVC');
		
		Profiler::startSection('Initiating i18n');
		logMsg('DiamondMVC: using compressed language file: ' . (Config::main()->get('DEBUG_MODE') ? 'yes' : 'no'), 1, false);
		i18n::init(Config::main()->get('DEBUG_MODE'));
		Profiler::endSection();
		
		// Load plugins
		Profiler::startSection('Load plugins');
		$this->plugins = Plugin::getPlugins();
		Profiler::endSection();
		
		Profiler::startSection('Load permissions');
		Permissions::init($db);
		Profiler::endSection();
		
		// Register field classes
		Profiler::startSection('Register fields');
		FormBuilder::registerFields();
		Profiler::endSection();
		
		
		$evt = new SystemBeforeRouteEvent();
		$this->trigger($evt);
		if( !$evt->isDefaultPrevented() ) {
			Profiler::startSection('Routing');
			
			// Route requested controller
			if( isset($_REQUEST['control']) and !empty($_REQUEST['control']) ) {
				$control = 'Controller' . str_replace('-', '_', $_REQUEST['control']);
				if( !class_exists($control) ) {
					$control = Config::main()->get('DEFAULT_CONTROLLER');
				}
			}
			else {
				$control = Config::main()->get('DEFAULT_CONTROLLER');
			}
			logMsg("DiamondMVC: routed controller to " . $control, 1, false);
			
			// Detect requested action, by default "main"
			if( isset($_REQUEST['action']) )
				$action = $_REQUEST['action'];
			else
				$action = 'main';
			$action = preg_replace('/[^A-z_]+/', '_', $action);
			logMsg("DiamondMVC: action is " . $action, 1, false);

			// Detect type of action. Currently possible: HTML, AJAX, JSON
			if( isset($_REQUEST['type']) )
				$type = strToLower($_REQUEST['type']);
			else
				$type = 'html';
			logMsg("DiamondMVC: request type is " . $type, 1, false);

			// Detect requested template. Ignored in JSON requests
			if( isset($_REQUEST['tpl']) ) {
				$_REQUEST['tpl'] = trim($_REQUEST['tpl']);
				if( !empty( $_REQUEST['tpl']) )
					$tpl = $_REQUEST['tpl'];
				else
					$tpl = 'default';
			}
			else {
				$tpl = 'default';
			}
			if( !file_exists(jailpath(DIAMONDMVC_ROOT . '/templates', $tpl)) ) {
				$tpl = 'default';
			}
			logMsg("DiamondMVC: jailed template is " . $tpl, 1, false);

			// Detect requested view
			if( isset($_REQUEST['view']) )
				$view = $_REQUEST['view'];
			else
				$view = '';
			logMsg("DiamondMVC: view is " . $view, 1, false);

			// The controller is the heart of the MVC system.
			logMsg("DiamondMVC: constructing controller", 1, false);
			$controller = new $control();
			
			// Before actually performing the action, we'll give plugins the chance to change the controller, action, view and template.
			logMsg("DiamondMVC: triggering pre-action event", 1, false);
			$this->controller = $controller;
			$this->action     = $action;
			$this->view       = $view;
			$this->tpl        = $tpl;
			$evt = new SystemActionEvent($controller, $action, $view, $tpl);
			$this->trigger($evt);
			$contrller = $this->controller = $evt->controller;
			$action    = $this->action     = $evt->action;
			$view      = $this->view       = $evt->view;
			$tpl       = $this->tpl        = $evt->template;
			
			Profiler::endSection();
			
			logMsg("DiamondMVC: new controller is: Controller" . $controller->getName() . '; new action is: ' . $action . '; new view is: ' . $view . '; new type is: ' . $type, 1, false);
			Profiler::startSection('Perform action');
			$controller->action($action);
			Profiler::endSection();
			
			Profiler::startSection('Output');
			switch( $type ) {
			// Not specially treated view type
			default:
				// Does the view type exist in the controller?
				if( $controller->hasView($view, $type) ) {
					logMsg("DiamondMVC: typed view $view ($type) found", 1, false);
					$controller->getView($view, $type)->render();
					break;
				}
				// If not we'll simply default to an HTML view.
				else {
					logMsg("DiamondMVC: no specific typed view found, defaulting to HTML view", 1, false);
				}
				
			// The entire website is built and sent to the client.
			case 'html':
				logMsg("DiamondMVC: rendering HTML view", 1, false);
				(new Template($controller, $tpl))->title(Config::main()->get('WEBSITE_TITLE'))->render($view);
				break;

			// TODO: AJAX request differ from JSON requests in that they make use of the client side engine to update particular parts of the web page (content, header, etc.) and request missing css and js.
			case 'ajax':
				logMsg("DiamondMVC: AJAX view still needs implementation!", 1, false);
				break;

			// Send JSON formatted raw data. By default the result of the controller is simply encoded, but a specialized JSON view can manipulate and format data before sending it out. This is
			// useful to remove circular references and PHP objects.
			case 'json':
				logMsg('DiamondMVC: hasView - ' . ($controller->hasView($view, 'json') ? 'yes' : 'no') . ' | hasTemplate - ' . ($controller->hasTemplate($view, 'json') ? 'yes' : 'no'), 1, false);
				if( $controller->hasView($view, 'json') or $controller->hasTemplate($view, 'json') ) {
					logMsg("DiamondMVC: JSON view for " . (empty($view) ? 'default' : $view) . " found", 1, false);
					$controller->getView($view, 'json')->read()->render();
				}
				else {
					logMsg("DiamondMVC: using generic JSON stringification on controller result", 1, false);
					echo json_encode($controller->getResult());
				}
				break;
			}
			Profiler::endSection();
		}
		else {
			logMsg('DiamondMVC: Skipping routing', 1, false);
		}
		
		Profiler::endSection();
	}
	
	
	/**
	 * Gets the currently logged in user, if any.
	 * @return ModelUser
	 */
	public function getCurrentUser( ) {
		return $this->user;
	}
	
	/**
	 * Gets a registered plugin object by name.
	 * @param  string $name of the plugin object.
	 * @return Plugin       Plugin object
	 */
	public function getPluginByName( $name ) {
		foreach( $this->plugins as $plugin ) {
			if( $plugin->getName() === $name ) {
				return $plugin;
			}
		}
		return null;
	}
	
	/**
	 * Loads the named library found under /classes/libs/<$libname>.php
	 * The library usually simply registers a new autoloader to automatically load
	 * classes with a particular prefix, but may also simply define a set of classes
	 * for use.
	 * @param string $libname Name of the library to load.
	 */
	public function loadLibrary( $libname ) {
		require_once(jailpath(DIAMONDMVC_ROOT . '/classes/libs', strToLower($libname) . '.php'));
	}
	
	/**
	 * Checks if a user - optionally with at least the given user permissions level -
	 * is logged into the system.
	 * @param  integer $userLevel Optional. Minimum required user level. If a user is logged in but does not have at least this user level, false will be returned nonetheless.
	 * @return boolean            True if a user is currently logged in.
	 */
	public function isLoggedIn( ) {
		return $this->user ? $this->user->isLoggedIn() : false;
	}
	
	
	/**
	 * Registers an object to receive a single or a list of events.
	 * @param  string     $event    Space separated list of names of events to handle.
	 * @param  object     $listener Event handling object.
	 * @return DiamondMVC           This instance to enable method chaining.
	 */
	public function on( $event, $listener ) {
		$events = explode(' ', preg_replace('/\s{2,}/', ' ', $event));
		
		foreach( $events as $event ) {
			if( !isset($this->eventListeners[$event]) ) {
				$this->eventListeners[$event] = array();
			}
			
			$this->eventListeners[$event][] = $listener;
		}
		
		return $this;
	}
	
	/**
	 * Unregisters an object from a single or a list of events.
	 * @param  string     $event    Space separated list of names of events to unregister from.
	 * @param  object     $listener Event handling object to unregister.
	 * @return DiamondMVC           This instance to enable method chaining.
	 */
	public function off( $event, $listener ) {
		$events = explode(' ', preg_replace('/\s{2,}/', ' ', $event));
		
		foreach( $events as $event ) {
			if( isset($this->eventListeners[$event]) ) {
				$listeners = &$this->eventListeners[$event];
				foreach( $listeners as $index => $curr ) {
					if( $curr === $listener ) {
						array_splice($listeners, $index, 1);
					}
				}
			}
		}
		
		return $this;
	}
	
	/**
	 * Triggers the given event(s). Registered plugins will be invoked on their {@link Plugin#handle()} method.
	 * @param  string|Event|array $event Case-sensitive name of the triggered event(s) OR a single event object OR an array of event objects.
	 * @param  any...             $data  Additional parameters to pass to the handlers.
	 * @return DiamondMVC                This instance to enable method chaining.
	 */
	public function trigger( ) {
		$args   = func_get_args();
		$event  = array_shift($args);
		
		if( is_object($event) ) {
			$events = array($event);
		}
		else if( is_array($event) ) {
			$events = $event;
		}
		else {
			$events = explode(' ', preg_replace('/\s{2,}/', ' ', $event));
		}
		
		foreach( $events as $event ) {
			$evtObj = is_object($event) ? $event : new Event($event);
			
			// Only process events which have registered listeners.
			if( isset($this->eventListeners[$evtObj->getFullName()]) and !empty($this->eventListeners[$evtObj->getFullName()]) ) {
				$listeners = &$this->eventListeners[$evtObj->getFullName()];
				
				$handlerArgs = $args;
				array_unshift($handlerArgs, $evtObj);
				
				foreach( $listeners as $listener ) {
					try {
						call_user_func_array(array($listener, 'handle'), $handlerArgs);
					}
					catch( Exception $ex ) {
						logMsg('[CORE] A plugin failed to properly handle an event: ' . $evtObj->getFullName(), 6);
					}
					
					// If a handler requested propagation stop, we'll abide.
					if( $evtObj->isPropagationStopped() ) {
						break;
					}
				}
			}
		}
		
		return $this;
	}
	
}
