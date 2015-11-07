<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

abstract class Model {
	
	/**
	 * database-Objekt welches zur Interaktion mit der database verwendet werden soll.
	 * @var database
	 */
	protected $db = null;
	
	/**
	 * Speichert die gelesenen Datensätze.
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Zustand für databaseanfragen.
	 * @var array
	 */
	protected $querystate = array();
	
	
	protected function __construct( $db ) {
		$this->db = $db;
	}
	
	
	/**
	 * Liest die assoziierten Daten aus der database.
	 * @param  Model|array $from Array oder Modell, welches die Daten bereitstellt.
	 * @return Model             Diese Instanz zur Methodenverkettung.
	 */
	abstract public function read( $from = null );
	
	/**
	 * Gibt die zuletzt gelesenen Daten zurück.
	 * @return array Array aus verschiedenen Daten, sowohl assoziativ als auch numerisch indiziert.
	 */
	public function getData( ) {
		return $this->data;
	}
	
	/**
	 * Ersetzt jeden Datensatz in $this->data mit einer Instanz von {@link ModelRow}, die dessen Daten bereitstellt.
	 * @return Model Diese Instanz zur Methodenverkettung.
	 */
	protected function wrapRowsInModels( $table, $fields = '*' ) {
		$result = array();
		foreach( $this->data as $row ) {
			$modelRow = (new ModelRow($this->db, $this->table, $fields, $row[0]))->read($row);
			$result[] = $modelRow;
		}
		$this->data = $result;
		return $this;
	}
	
	/**
	 * Setzt die Tabelle, mit der interagiert werden soll.
	 * @param  string $table Name der Zieltabelle.
	 * @return Model         Diese Instanz zur Methodenverkettung.
	 */
	protected function table( $table ) {
		$this->querystate['table'] = $table;
		return $this;
	}
	
	/**
	 * Setzt die Zielfelder innerhalb der Tabelle.
	 * @param  array|string $fields Komma-getrennte Liste der oder Array aus Feldnamen.
	 * @return Model                Diese Instanz zur Methodenverkettung.
	 */
	protected function fields( $fields ) {
		$this->querystate['fields'] = $fields;
		return $this;
	}
	
	/**
	 * Setzt den zu verwendenden Filter.
	 * @param  string $filter Zu verwendender Filter.
	 * @return Model          Diese Instanz zur Methodenverkettung.
	 */
	protected function filter( $filter ) {
		$this->querystate['filter'] = $filter;
		return $this;
	}
	
	/**
	 * Bindet die Parameter an die im Query vorkommenden Marker.
	 * @param  string   $binddef Typendefinition der zu bindenden Parameter.
	 * @param  mixed... $params  Zu bindende Parameter.
	 * @return Model             Diese Instanz zur Methodenverkettung.
	 */
	protected function bind( ) {
		$args    = func_get_args();
		$binddef = array_shift($args);
		
		$this->querystate['binddef']    = $binddef;
		$this->querystate['bindparams'] = $args;
		
		return $this;
	}
	
	/**
	 * Setzt die maximale Anzahl an Datensätzen, die gelesen werden sollen.
	 * @param  integer $limit Maximale Anzahl zu lesender Datensätze.
	 * @return Model          Diese Instanz zur Methodenverkettung.
	 */
	protected function limit( $limit ) {
		$this->querystate['limit'] = $limit;
		return $this;
	}
	
	/**
	 * Setzt die Anzahl der Datensätze, die am Anfang der Ergebnismenge
	 * ignoriert werden.
	 * @param  integer $offset Anzahl zu ignorierender Datensätze.
	 * @return Model           Diese Instanz zur Methodenverkettung.
	 */
	protected function offset( $offset ) {
		$this->querystate['offset'] = $offset;
		return $this;
	}
	
	/**
	 * Setzt die Sortierung der Anfrage. Mehrere Felder können dabei mit
	 * Kommata oder Leerzeichen getrennt oder als Array übergeben werden.
	 * @param  array|string $order Felder, nach denen sortiert werden soll.
	 * @return Model               Diese Instanz zur Methodenverkettung.
	 */
	protected function order( $order ) {
		$this->querystate['order'] = $order;
		return $this;
	}
	
	/**
	 * Erzeugt einen neuen Join für dieses Modell.
	 * Falls keine Tabelle angegeben wird, wird der Join zurückgesetzt.
	 * @param  string          $table Name der Tabelle, mit der verknüpft werden soll.
	 * @return ModelTable|Join        Das Join-Objekt, um dessen Eigenschaften genauer zu spezifizieren, oder diese Instanz zur Methodenverkettung falls der Join zurückgesetzt wurde.
	 */
	protected function join( $table = '' ) {
		if( empty($table) ) {
			unset($this->querystate['join']);
			return $this;
		}
		return $this->querystate['join'] = (new Join($this))->table($table);
	}
	
	
	/**
	 * Zählt die Anzahl der gelesenen Datensätze.
	 * @return integer Anzahl der gelesenen Datensätze.
	 */
	public function count( ) {
		$max = @max(array_keys($this->data));
		return $max !== false ? $max + 1 : 0;
	}
	
	/**
	 * Überprüft, ob mindestens ein Datensatz gelesen wurde.
	 * @return boolean Wahr falls mindestens ein Datensatz gelesen wurde, ansonsten falsch.
	 */
	public function found( ) {
		return !!$this->count();
	}
	
}
