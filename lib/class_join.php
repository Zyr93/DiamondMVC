<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

/**
 * Repräsentiert einen SQL-JOIN.
 */
class Join {
	
	/**
	 * Superior object to return to upon invoking {@link #back()} for easy method chaining.
	 * @var Object
	 */
	private $returnTo;
	
	
	/**
	 * Type of the join: LEFT, RIGHT, INNER
	 * However MySQL does not support OUTER!
	 * @var string
	 */
	protected $jointype = 'INNER';
	
	/**
	 * Table to join
	 * @var string
	 */
	protected $tabname = '';
	
	/**
	 * Condition to join the tables.
	 * @var string
	 */
	protected $condition = '';
	
	/**
	 * Another Join object
	 * @var Join
	 */
	protected $more = null;
	
	/**
	 * Whether to ignore the deleted column in the ON condition.
	 * @var boolean
	 */
	protected $ignoreDeleted = false;
	
	/**
	 * Whether to ignore the hidden column in the ON condition.
	 * @var boolean
	 */
	protected $ignoreHidden = false;
	
	
	/**
	 * Eine abstrakte Repräsentation einer SQL-JOIN-Anweisung.
	 * @param object Objekt, welches zwecks Methodenverkettung in `back()` zurückgeliefert werden soll.
	 * @param Join   Ein anderes Join-Objekt, von welchem die Einstellungen übernommen werden sollen.
	 */
	public function __construct( $returnTo, $copy = null ) {
		$this->returnTo = $returnTo;
		
		if( $copy ) {
			$this->jointype  = $copy->jointype;
			$this->tabname   = $copy->tabname;
			$this->condition = $copy->condition;
			$this->more      = new Join($returnTo, $copy->more);
		}
	}
	
	public function left( ) {
		$this->jointype = 'LEFT';
		return $this;
	}
	
	public function right( ) {
		$this->jointype = 'RIGHT';
		return $this;
	}
	
	public function inner( ) {
		$this->jointype = 'INNER';
		return $this;
	}
	
	public function table( $tab ) {
		$this->tabname = $tab;
		return $this;
	}
	
	public function on( $condition ) {
		// TODO: Identifizierer normieren
		$this->condition = 'ON (' . $condition . ')';
		return $this;
	}
	
	/**
	 * Join another table. It will return {@link #back()} to the DBO.
	 * @param  string $table to join
	 * @return Join          The new Join object to further specify that Join.
	 */
	public function more( $table ) {
		return $this->more = (new Join($this->returnTo))->table($table);
	}
	
	/**
	 * Removes a previously set Join.
	 * @return [type] [description]
	 */
	public function nomore( ) {
		$this->more = null;
		return $this;
	}
	
	public function ignoreDeleted( $ignore = true ) {
		$this->ignoreDeleted = $ignore;
		return $this;
	}
	
	public function ignoreHidden( $ignore = true ) {
		$this->ignoreHidden = $ignore;
		return $this;
	}
	
	/**
	 * Konvertiert diesen Join in einen String.
	 * @param  string $db Name der database, die die Tabelle beinhaltet.
	 * @return string     Join-String
	 */
	public function str( $db ) {
		// TODO: Erwartet Filter-Klasse zur sichereren Abstrahierung der Anfragen für sowohl den Server als auch den Entwickler.
		$condition = $this->condition;
		if( Config::main()->get('DBO_ENFORCE_COL_DELETED') and !$this->ignoreDeleted ) {
			$condition .= ' AND `' . $this->tabname . '`.`DELETED`=0';
		}
		return implode(' ', array($this->jointype, 'JOIN', "`$db`.`" . $this->tabname . '`', $condition)) . (!empty($this->more) ? ' ' . $this->more->str($db) : '');
	}
	
	
	/**
	 * Liefert das Objekt zurück, welches im Konstruktor spezifiziert wurde. Dies ist hauptsächlich
	 * eine Nutz-Methode zur Methodenverkettung.
	 * @return Object
	 */
	public function back( ) {
		return $this->returnTo;
	}
	
}