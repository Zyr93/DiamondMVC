<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class Template extends Snippet {
	
	/**
	 * Zugehöriger Kontroller, der die auszuführende Aktion beschreibt und den Inhalt des Templates
	 * liefert.
	 * @var Controller
	 */
	protected $controller = null;
	
	/**
	 * Name dieser Vorlage.
	 * @var string
	 */
	protected $name = '';
	
	/**
	 * Name der darzustellenden View. Wird in {@link #render(string)} gesetzt.
	 * @var string
	 */
	protected $view = '';
	
	/**
	 * Inhalt des <title> Tags.
	 * @var string
	 */
	protected $title = '';
	
	/**
	 * Meta-Tags
	 * @var string
	 */
	protected $meta = '';
	
	
	/**
	 * Erzeugt eine neue generische HTML-Vorlage (mit PHP-Unterstützung).
	 * @param Controller $controller Zugehöriger Kontroller.
	 * @param string     $name       Name dieser Vorlage. Verwendet, um sie von dem Dateisystem zu laden.
	 */
	public function __construct( $controller, $name ) {
		parent::__construct('');
		
		$this->controller = $controller;
		$this->name       = $name;
	}
	
	
	public function read( ) {
		$this->file = DIAMONDMVC_ROOT . '/templates/' . $this->name . '/index.php';
		return parent::read();
	}
	
	/**
	 * Gibt das Template aus.
	 * @param  string   $view Name der darzustellenden View.
	 * @return Template       Diese Instanz zur Methodenverkettung.
	 */
	public function render( $view = '' ) {
		$this->view = $view;
		
		$evt = new RenderEvent($this);
		DiamondMVC::instance()->trigger($evt);
		if( $evt->isDefaultPrevented() ) {
			return $this;
		}
		
		$view = $this->controller->getView($view)->read();
		
		ob_start();
		require_once(DIAMONDMVC_ROOT . '/templates/' . $this->name . '/index.php');
		$content = ob_get_contents();
		ob_end_clean();
		
		// Platzhalter ersetzen.
		foreach( $this->bind as $bind => $to ) {
			$content = str_replace('${' . $bind . '}', $to, $content);
		}
		$content = preg_replace('/\$\{[^\}]*\}/', '', $content);
		
		// Ressourcen ab <head> anhängen.
		$pos        = strpos($content, '</head>');
		$preInject  = substr($content, 0, $pos);
		$postInject = substr($content, $pos);
		
		$inject = $this->meta . $view->getMeta();
		
		// Inject the AMD modules and their dependencies.
		$scripts = array_merge($this->scripts, $view->getScripts());
		$sheets  = array_merge($this->stylesheets, $view->getStylesheets());
		
		// Assemble list of stylesheets to include.
		foreach( $this->controller->getAllModules() as $modules ) {
			foreach( $modules as $module ) {
				foreach( $module->getScripts() as $script ) {
					$scripts[] = $script;
				}
				foreach( $module->getStylesheets() as $sheet ) {
					$sheets[] = $sheet;
				}
			}
		}
		
		// No duplicates
		array_unique($sheets);
		array_unique($scripts);
		
		$inject .= '<script type="applicaton/json" id="amd-modules">' . json_encode($scripts) . '</script>';
		foreach( $sheets as $sheet ) {
			$mime = 'stylesheet';
			if( ($index = strpos($sheet, ';')) !== false ) {
				$mime  = substr($sheet, $index + 1);
				$sheet = substr($sheet, 0, $index);
			}
			$inject .= '<link rel="' . $mime . '" href="' . $sheet . '">';
		}
		
		$content = $preInject . $inject . $postInject;
		
		$evt = new Event('render');
		$evt->source  = 'template';
		$evt->content = $content;
		DiamondMVC::instance()->trigger($evt);
		if( !$evt->isDefaultPrevented() ) {
			echo $evt->content;
		}
		
		return $this;
	}
	
	
	/**
	 * Gets or sets the contents of the <title> tag.
	 * @param  string          $title Content of the <title> tag. Pass an empty string to reset. Omit to get the current value.
	 * @return Template|string        This instance to enable method chaining if using as a setter. The current value if using as a getter.
	 */
	public function title( $title = '' ) {
		if( !func_num_args() ) {
			return $this->title;
		}
		$this->title = $title;
		return $this;
	}
	
	
	/**
	 * Fügt einen Meta-Tag in den Head-Tag ein.
	 * @param  string $meta Inhalt des Meta-Tags ohne "<meta" und schließendem ">".
	 * @return View         Diese Instanz zur Methodenverkettung.
	 */
	public function addMeta( $meta ) {
		$this->meta .= "<meta $meta>";
		return $this;
	}
	
	/**
	 * Gibt die Standard-Skripte, -Stylesheets und -Meta-Tags aus.
	 * @return string
	 */
	public function getDefaultHead( ) {
		return '<link rel="stylesheet" href="' . DIAMONDMVC_URL . '/assets/bootstrap/css/bootstrap.min.css">' .
			   '<link rel="stylesheet" href="' . DIAMONDMVC_URL . '/assets/bootstrap/css/bootstrap-theme.min.css">' .
			   '<link rel="stylesheet" href="' . DIAMONDMVC_URL . '/assets/font-awesome/css/font-awesome.min.css">' .
			   '<script>var DIAMONDMVC_URL = "' . DIAMONDMVC_URL . '";</script>' .
			   '<script data-main="' . DIAMONDMVC_URL . '/assets/diamondmvc.js" src="' . DIAMONDMVC_URL . '/assets/require.js"></script>' .
			   '<!-- DiamondMVC by Brian Cobile aka Zyr -->' .
			   '<meta http-equiv="content-type" content="text/html;charset=utf-8">' .
			   '<meta charset="UTF-8">';
	}
	
	/**
	 * Gets the body of the website, generated through the view.
	 * @return string
	 */
	public function getBody( ) {
		return $this->controller->getView($this->view)->getContents();
	}
	
	/**
	 * Zählt die Module an der gegebenen Position.
	 * @param  string  $position Modul-Position
	 * @return integer           Anzahl der Module. Falls die Position nicht existiert, wird 0 zurück gegeben.
	 */
	public function countModules( $position ) {
		return $this->controller->countModules($position);
	}
	
	/**
	 * Holt die Module, die für die gegebene Position definiert wurden.
	 * @param  string $position Name der Modul-Position.
	 * @return array            Array aus Modulen.
	 */
	public function getModules( $position ) {
		return $this->controller->getModules($position);
	}
	
	/**
	 * Holt das Modul an gegebener Position mit gegebenem Index aus dem Kontroller.
	 * @param  string  $position Modul-Position
	 * @param  integer $index    Modul-Index
	 * @return Module            Das Modul oder null falls entweder Position oder Index oder beide nicht existieren.
	 */
	public function getModule( $position, $index ) {
		return $this->controller->getModule($position, $index);
	}
	
	/**
	 * Holt die Module, die der Kontroller für die Sidebar definiert hat.
	 * @return string HTML-Inhalt der Sidebar.
	 */
	public function getSidebar( ) {
		return $this->controller->getSidebar();
	}
	
	/**
	 * Gibt das HTML der Sidebar aus.
	 * @return string HTML der Sidebar.
	 */
	public function renderSidebar( ) {
		return $this->renderModules('sidebar');
	}
	
	/**
	 * Gibt den HTML-Code aller Module an gegebener Position aus.
	 * @param  string $position Modul-Position
	 * @return string           HTML der Modul-Position
	 */
	public function renderModules( $position ) {
		$modules = $this->getModules($position);
		$result  = '';
		foreach( $modules as $module ) {
			$result .= $module;
		}
		return $result;
	}
	
	/**
	 * Gibt den HTML-Code des Moduls an gegebener Position und gegebenem Index aus.
	 * @param  string  $position Modul-Position
	 * @param  integer $index    Modul-Index
	 * @return string            Modul-HTML
	 */
	public function renderModule( $position, $index ) {
		return $this->getModule($position, $index) . '';
	}
	
	/**
	 * Generic approach to assembling the base URL for all templates.
	 * @return string
	 */
	public function getBaseUrl( ) {
		return '/templates/' . $this->name;
	}
	
}