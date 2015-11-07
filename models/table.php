<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

abstract class ModelTable extends Model {
	
	/**
	 * Tabelle dieses Modells.
	 * @var string
	 */
	protected $table = '';
	
	/**
	 * Spalten, die aus der Tabelle gelesen werden.
	 * @var string
	 */
	protected $fields = '*';
	
	
	/**
	 * Konfiguriert dieses Modell für den Gebrauch mit einer bestimmten Tabelle
	 * und den gegebenen Feldern.
	 * @param Database     $db     database-Schnittstellen-Objekt
	 * @param string       $table  Name der Tabelle
	 * @param string|array $fields Komma-getrennte Liste oder Array der Feldnamen
	 */
	protected function __construct( $db, $table, $fields = '*', $filter = '' ) {
		parent::__construct($db);
		$this->table($table)->fields($fields)->filter($filter);
		$this->table  = $table;
		$this->fields = $fields;
	}
	
	
	/**
	 * Hängt einen Datensatz an die Tabelle an.
	 * Der Datensatz wird nicht in den Cache dieser Instanz aufgenommen!
	 * @param  array      $values Anzuhängende Werte. Die Anzahl der Werte muss mit der Anzahl der im Konsturktor gesetzten Felder übereinstimmen.
	 * @return ModelTable         Diese Instanz zur Methodenverkettung.
	 */
	public function append( $values ) {
		$args = func_get_args();
		$this->db->pushpopState($this->querystate);
		call_user_func_array(array($this->db, 'append'), $args);
		$this->db->popState();
		return $this;
	}
	
	/**
	 * Ersetzt die gefilterten Daten mit den gegebenen Daten. Die Daten
	 * werden nicht automatisch in den Cache übernommen!
	 * 
	 * Das übergebene Array aus Werten muss mit der Anzahl der im
	 * Konstruktor gesetzten Felder übereinstimmen!
	 * @param  array      $key   Ersetzende Werte.
	 * @return ModelTable        Diese Instanz zur Methodenverkettung.
	 */
	public function replace( $values ) {
		$args = func_get_args();
		$this->db->pushpopState($this->querystate);
		call_user_func_array(array($this->db, 'replace'), $args);
		$this->db->popState();
		return $this;
	}
	
	
	public function read( $from = null ) {
		if( is_array($from) or is_object($from) and $from instanceof Model ) {
			if( is_array($from) ) {
				$this->data = $from;
			}
			else {
				$this->data = $from->getData();
			}
			return $this;
		}
		
		$db = $this->db;
		$db->pushpopState($this->querystate)->seek();
		if( !$db->found() ) {
			$this->data = array();
			$db->popState();
		}
		else {
			$this->data = $db->getResult();
			$this->wrapRowsInModels($this->table, $this->fields);
			$db->popState();
		}
		return $this;
	}
	
	protected function indexedDataOnly( ) {
		return filter_array($this->data, 'is_numeric', 1);
	}
	
}
