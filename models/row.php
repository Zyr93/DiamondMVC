<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class ModelRow extends Model {
	
	/**
	 * Anfragenzustand für Lese- und Schreibzugriffe auf die database.
	 * @var array
	 */
	protected $querystate = array();
	
	/**
	 * Assoziatives Array, welches die Felder der Tabelle mit den Daten aus dem Datensatz in Verbindung
	 * bringt.
	 * @var array
	 */
	protected $data = array();
	
	
	/**
	 * Erzeugt ein neues Modell, welches die Daten eines Datensatzes beinhaltet.
	 * @param database      $db     Zu verwendendes DBO.
	 * @param string         $table  Tabelle des Datensatzes.
	 * @param string|array   $fields Die Felder, die das Modell auslesen soll. '*' liest alle Felder aus.
	 * @param stirng|integer $search UID oder Anzeige des Datensatzes.
	 */
	public function __construct( $db, $table, $fields, $search ) {
		parent::__construct($db);
		$this->querystate['table']  = $table;
		$this->querystate['fields'] = $fields;
		
		if( is_numeric($search) ) {
			$this->filter('UID=?')->bind('i', $search);
		}
		else {
			$this->filter('ANZEIGE=?')->bind('s', $search);
		}
	}
	
	/**
	 * Setzt die Typendefinition erwarteten Werte für die databasefelder.
	 * @param  string   $fielddef Typendefinition der Felder. Jeder Charakter repräsentiert ein Feld. Siehe mysqli_stmt::bind_param.
	 * @return ModelRow           Diese Instanz zur Methodenverkettung.
	 */
	public function fielddef( $fielddef ) {
		$this->querystate['fielddef'] = $fielddef;
		return $this;
	}
	
	
	public function read( $from = null ) {
		if( is_array($from) or is_object($from) and $from instanceof Model ) {
			// TODO: Eingabe verifizieren.
			if( is_array($from) ) {
				$data = $from;
			}
			else {
				$data = $from->getData();
			}
		}
		else {
			$this->db->pushpopState($this->querystate)->limit(1)->seek();
			$data = $this->db->getData();
			$this->db->popState();
		}
		
		if( $data and count($data) ) {
			foreach( $data as $field => $value ) {
				$key = is_string($field) ? strToLower($field) : $field;
				$this->data[$key] = $value;
			}
		}
		else {
			$this->data = array();
		}
		
		return $this;
	}
	
	/**
	 * Speichert die Daten dieser Zeile in der database.
	 * Dazu muss eine UID geladen worden sein!
	 * @return ModelRow            Diese Instanz zur Methodenverkettung.
	 */
	public function write( ) {
		if( !is_integer($this->data['uid']) ) {
			throw new InvalidArgumentException('Keine UID für Datensatz gegeben');
		}
		// Nur numerische Indizes verwenden.
		$values = filter_array($this->data, 'is_numeric', 1);
		$this->db->pushpopState($this->querystate)->filter('UID=?')->bind('i', $this->data['uid'])->replace($values)->popState();
		return $this;
	}
	
	
	public function get( $field ) {
		$field = strToLower($field);
		return isset($this->data[$field]) ? $this->data[$field] : null;
	}
	
	public function set( $field, $value ) {
		$field = strToLower($field);
		if( $field === 'uid' ) {
			throw new InvalidArgumentException("Cannot overwrite field UID!");
		}
		
		if( !isset($this->data[$field]) ) {
			$this->data[$field] = $this->data[] = $value;
		}
		
		else {
			$keys  = array_keys($this->data);
			$index = array_search($field, $keys, true);
			
			if( is_numeric($field) ) {
				$other = $keys[$index + 1];
			}
			else {
				$other = $keys[$index - 1];
			}
			
			$this->data[$field] = $this->data[$other] = $value;
		}
		
		return $this;
	}
	
}
