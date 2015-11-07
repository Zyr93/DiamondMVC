<?php
/**
 * @package  DiamondMVC
 * @author   Zyr <zyrius@live.com>
 * @version  1.0
 * @license  CC-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/)
 */
defined('DIAMONDMVC') or die();

class database {
	
	/**
	 * MySQL interface object.
	 * @var null
	 */
	protected $mysqli = null;
	
	/**
	 * Name of the currently used database.
	 * @var string
	 */
	protected $db = '';
	
	/**
	 * Table to be used in the next query.
	 * @var string
	 */
	protected $table = '';
	
	/**
	 * One of the few fields not stored on the stack. It is used to allow multiple installations
	 * to use the same database without interfering with each other and thus set only once per
	 * DBO instance.
	 * @var string
	 */
	protected $prefix = '';
	
	/**
	 * Columns to be considered in the next query.
	 * @var array
	 */
	protected $fields = array('*');
	
	/**
	 * Associative list of MySQL function calls. The keys are the column names the values
	 * will be available under. Only available for SELECT / seek queries.
	 * @var array
	 */
	protected $fns = array();
	
	/**
	 * Associative array where the keys are the column names of subselects.
	 * @var array
	 */
	protected $subselects = array();
	
	/**
	 * Type definition of fields. Used in {@link #append()} and {@link #replace()} to
	 * determine the expected types of data.
	 * @var string
	 */
	protected $fielddef = '';
	
	/**
	 * The JOIN object to be used in the next query.
	 * @var Join
	 */
	protected $join = null;
	
	/**
	 * Query filter
	 * @var string
	 */
	protected $filter = '';
	
	/**
	 * Number of rows to skip. 0 = none.
	 * @var integer
	 */
	protected $offset = 0;
	
	/**
	 * Maximum number of rows to read. -1 = all.
	 * @var integer
	 */
	protected $limit = -1;
	
	/**
	 * Columns to sort by.
	 * @var string
	 */
	protected $order = array();
	
	/**
	 * Columns to group by. Usually just one.
	 * @var string
	 */
	protected $group = '';
	
	/**
	 * Typedefinition of the bind parameters.
	 * @var string
	 */
	protected $binddef = '';
	
	/**
	 * Bind parameters.
	 * @var array
	 */
	protected $bindparams = array();
	
	/**
	 * Whether to ignore the deleted flag of data sets.
	 * @var boolean
	 */
	protected $ignoreDeleted   = false;
	
	/**
	 * Whether to ignore the hidden flag of data sets.
	 * @var boolean
	 */
	protected $ignoreHidden    = false;
	
	/**
	 * Stack of query states. Ideal to store and restore previous states.
	 * @var array
	 */
	protected $states = array(
		'db'                => array(),
		'table'             => array(),
		'fields'            => array(),
		'fns'               => array(),
		'subselects'        => array(),
		'fielddef'          => array(),
		'join'              => array(),
		'filter'            => array(),
		'order'             => array(),
		'group'             => array(),
		'limit'             => array(),
		'offset'            => array(),
		'data'              => array(),
		'counter'           => array(),
		'countAffectedRows' => array(),
		'binddef'           => array(),
		'bindparams'        => array(),
		'ignoreDeleted'     => array(),
		'ignoreHidden'      => array(),
		'error'             => array(),
		'query'             => array(),
	);
	
	/**
	 * The result of the last query as an array of associative arrays. If no query has been made
	 * or the last request failed, this will be empty.
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Index of the row to return next.
	 * @var integer
	 */
	protected $counter = 0;
	
	/**
	 * Number of manipulated or retrieved rows.
	 * @var integer
	 */
	protected $countAffectedRows = 0;
	
	/**
	 * Error of the last query.
	 * @var string
	 */
	public $error = '';
	
	/**
	 * The last executed query. This field is only filled by the query functions and will not
	 * be considered in the query functions themselves!
	 * @var string
	 */
	public $query = '';
	
	
	
	public function __construct( $user = 'root', $pass = '', $host = 'localhost', $port = 3306, $prefix = '' ) {
		$this->connect($user, $pass, $host, $port);
		$this->prefix = $prefix;
	}
	
	public function connect( $user = 'root', $pass = '', $host = 'localhost', $port = 3306 ) {
		if( $this->mysqli != null )
			$this->close();
		
		$this->mysqli = new mysqli($host, $user, $pass, '', $port);
		if( $this->mysqli->connect_error) {
			throw new Exception('Es konnte keine Verbindung zur database hergestellt werden.');
		}
		$this->mysqli->set_charset('utf8');
		return $this;
	}
	
	public function close( ) {
		$this->mysqli->close();
		$this->mysqli = null;
		return $this;
	}
	
	/**
	 * Setzt die database, die in der nächsten Anfrage betrachtet werden soll oder liefert die
	 * derzeit verwendete database zurück falls kein Parameter übergeben wurde.
	 * @param  string?           $database Name der database
	 * @return database|string            Diese Instanz zur Methodenverkettung oder der Name der database, die derzeit verwendet wird.
	 */
	public function useDB( $database = '' ) {
		if( empty($database) ) {
			return $this->db;
		}
		$this->db = $database;
		$this->mysqli->select_db($database);
		return $this;
	}
	
	/**
	 * Setzt die Tabelle, die in der nächsten Anfrage betrachtet werden soll oder liefert die
	 * derzeit verwendete Tabelle zurück falls kein Parameter übergeben wurde.
	 * @param  string?          $table Name der Tabelle
	 * @return database|string        Diese Instanz zur Methodenverkettung oder der Name der database, die derzeit verwendet wird.
	 */
	public function select( $table = '' ) {
		if( func_num_args() === 0 ) {
			return $this->table;
		}
		$this->table = $this->prefix . $table;
		return $this;
	}
	
	/**
	 * Proxy-Methode für {@link #select()}
	 * @see #select()
	 */
	public function table( $table = '' ) {
		return $this->select($table);
	}
	
	/**
	 * Setzt die bei der nächsten Anfrage zu beachtenden Felder oder liefert die derzeit gesetzten
	 * zurück.
	 * @param  string|array           $fields Zu setzende Felder. Optional.
	 * @return string|array|database         Setter: Diese Instanz zur Methodenverkettung. Getter: Derzeit gesetzte Felder.
	 */
	public function fields( $fields = '' ) {
		if( empty($fields) ) {
			return $this->fields;
		}
		$this->fields = $fields;
		return $this;
	}
	
	/**
	 * Adds a MySQL function call to the SELECT query.
	 * Note 1: Currently the MySQL function call is not sanitized.
	 * Note 2: In future this function might be natively integrated in the `fields` method.
	 * 
	 * TODO: Utilize an automaton to parse the function call for commas and nested functions. Everything
	 * that is not a nested function is interpreted as an identifier and as such is stripped off of
	 * dangerous characters.
	 * 
	 * @param  string   $name Column name to 
	 * @param  string   $fn   MySQL function chain to call.
	 * @return Database       This instance to enable method chaining.
	 */
	public function fn( $name, $fn ) {
		$this->fns[$name] = $fn;
		return $this;
	}
	
	/**
	 * Fügt eine Unterabfrage von Daten in die Spalten einer SELECT-Abfrage ein.
	 * @param  string    $as         Spaltenname der Unterabfrage.
	 * @param  array     $querystate Spezifikation der Unterabfrage.
	 * @return database             Diese Instanz zur Methodenverkettung.
	 */
	public function subselect( $as, $querystate ) {
		if( empty($querystate) and isset($this->subselects[$as]) ) {
			unset($this->subselects[$as]);
		}
		else {
			$this->subselects[$as] = $querystate;
		}
		return $this;
	}
	
	/**
	 * Fügt eine Unterabfrage in den Filter der Abfrage ein. Um auf die Abfrage zugreifen zu
	 * können, werden Unix Shell Variablenkonstrukte dieser Form verwendet:
	 *   ${ABFRAGENNAME}
	 * @param  string    $selectname Abfragenname zur Identifikation innerhalb eines Filters.
	 * @param  array     $querystate Spezifikationen der Abfrage.
	 * @return database             Diese Instanz zur Methodenverkettung.
	 */
	public function whereselect( $selectname, $querystate ) {
		if( empty($querystate) and isset($this->whereselects[$selectname]) ) {
			unset($this->whereselects[$querystate]);
		}
		else {
			$this->whereselects[$selectname] = $querystate;
		}
		return $this;
	}
	
	/**
	 * Setzt die Typdefinition für die Felder für beispielsweise eine replace- oder append-Anfrage.
	 * @param  string    $fielddef Typendefinition der Felder.
	 * @return database           Diese Instanz zur Methodenverkettung.
	 */
	public function fielddef( $fielddef ) {
		$this->fielddef = $fielddef;
		return $this;
	}
	
	/**
	 * Setzt die maximale Anzahl der Datensätze, die bei der nächsten Anfrage beachtet werden sollen.
	 * Falls -1 übergeben wird, werden alle Datensätze beachtet.
	 * @param  integer   $limit Maximale Anzahl zu ermittelnder Datensätze.
	 * @return database        Diese Instanz zur Methodenverkettung.
	 */
	public function limit( $limit ) {
		$this->limit = $limit;
		return $this;
	}
	
	/**
	 * Setzt die Anzahl der Datensätze, die am Anfang übersprungen werden sollen. Mit 0 werden keine
	 * Datensätze übersprungen.
	 * @param  integer   $offset Anzahl der zu überspringenden Datensätze.
	 * @return database         Diese Instanz zur Methodenverkettung.
	 */
	public function offset( $offset ) {
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * Setzt die Felder, nach denen sortiert werden soll.
	 * 
	 * Mehrere Felder können entweder mittels Kommata getrennt werden. Nach einem Feld
	 * können zwei Schlüsselwörter stehen: ASC oder DESC für aufsteigende bzw. absteigende Sortierung
	 * nach diesem jeweiligen Feld. Nicht erkannte Schlüsselwörter werden mit ASC ersetzt.
	 * 
	 * @param  string    $order Komma-getrente Liste der Felder, nach denen sortiert werden soll.
	 * @return database        Diese Instanz zur Methodenverkettung.
	 */
	public function order( $order ) {
		$this->order = $order;
		return $this;
	}
	
	/**
	 * Setzt die Felder, die verwendet werden sollen, um Datensätze zu gruppieren.
	 * @param  string    $group Leerzeichen- oder Komma-getrennte Liste der Felder, die zur Gruppierung verwendet werden sollen.
	 * @return database        Diese Instanz zur Methodenverkettung.
	 */
	public function group( $group ) {
		$this->group = $group;
		return $this;
	}
	
	/**
	 * Proxy-Methode für {@link #group(string)}.
	 * @see database#group(string)
	 */
	public function groupBy( $group ) {
		return $this->group($group);
	}
	
	/**
	 * Creates a new JOIN with the given table. The returned object can be used to further specify the JOIN.
	 * Then use {@link Join#back()} to get this instance again and continue specifying the next query with
	 * method chaining. The Join also provides a {@link Join#more()} method to add more JOINs.
	 * If no table is specified, the JOIN is reset.
	 * @param  string        $tabname Name of the table to join.
	 * @return Join|database          The constructed Join object for further specification of the properties of the join or this instance to enable method chaining in case the join is reset.
	 */
	public function join( $tabname = '' ) {
		if( empty($tabname) ) {
			$this->join = null;
			return $this;
		}
		return $this->join = (new Join($this))->table($tabname);
	}
	
	/**
	 * Forces setting the new join. To reset the join, use $this->join()
	 * @param  Join     $join
	 * @return Database This instance to enable method chaining.
	 */
	public function setJoin( Join $join ) {
		$this->join = new Join($db, $join);
		return $this;
	}
	
	/**
	 * Gets the last set join.
	 * @return Join
	 */
	public function getJoin( ) {
		return $this->join;
	}
	
	/**
	 * Sets the filter to use in subsequent queries.
	 * @param  string   $filter
	 * @return Database This instance to enable method chaining.
	 */
	public function filter( $filter = '' ) {
		if( empty($filter) ) {
			return $this->filter;
		}
		$this->filter = $filter;
		return $this;
	}
	
	/**
	 * Proxy method for {@link #filter()}.
	 * @see #filter()
	 */
	public function where( $filter = '' ) {
		return $this->filter($filter);
	}
	
	/**
	 * Binds parameters based on type to the markers in the query.
	 * @param  string       $typedef   Type definition of the parameters. Each character represents a parameter. The length of the string must match with the parameter count.
	 * @param  array|string $params... Parameters to bind to the markers in the query.
	 * @return Database                This instance to enable method chaining.
	 */
	public function bind( ) {
		$args    = func_get_args();
		$typedef = array_shift($args);
		
		if( count($args) !== strlen($typedef) ) {
			throw new InvalidArgumentException('Die Anzahl der definierten Parameter stimmt nicht überein mit der Anzahl der übergebenen Parameter.');
		}
		
		$this->binddef    = $typedef;
		$this->bindparams = $args;
		return $this;
	}
	
	/**
	 * Binds the parameters to the markers.
	 * This workaround is necessary since MySQLi::bind_param does not support pass by value.
	 * @param mysqli_stmt $stmt Prepared statement to which to bind the parameters.
	 */
	private function _realBind( $stmt ) {
		$params = array($this->binddef);
		for( $i = 0; $i < count($this->bindparams); ++$i ) {
			$params[] = &$this->bindparams[$i];
		}
		call_user_func_array(array($stmt, 'bind_param'), $params);
	}
	
	/**
	 * Executes a raw, unsanitized query on the database. It still supports parameter
	 * sanitization using {@link #bind()}!
	 * @param  string   $query
	 * @return Database This instance to enable method chaining.
	 */
	public function query( $query ) {
		$this->cleanup();
		
		$this->query = $query;
		if( empty($query) ) {
			return $this;
		}
		
		$stmt = $this->mysqli->prepare($query);
		if( !$stmt ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error, 9);
			return $this;
		}
		if( !empty($this->binddef) ) {
			$this->_realBind($stmt);
		}
		
		logQuery($query, $this->binddef, $this->bindparams);
		
		if( !$stmt->execute() ) {
			$this->error = $stmt->error;
			logMsg('MySQL error: ' . $this->error, 9);
		}
		else {
			$result = $stmt->get_result();
			if( is_object($result) ) {
				while( ($row = $result->fetch_array()) !== null ) {
					$this->data[] = $row;
				}
				$this->countAffectedRows = count($this->data);
			}
			else {
				$this->data = array();
				$this->countAffectedRows = 0;
			}
		}
		
		$stmt->free_result();
		$stmt->close();
		return $this;
	}
	
	/**
	 * Performs a SELECT query on the database.
	 * @param  boolean  $allowDeleted Whether to allow data sets marked as deleted.
	 * @return database               This instance to enable method chaining.
	 */
	public function seek( ) {
		$this->cleanup();
		
		$query = $this->query = $this->generateSelectQuery($this->getState());
		if( empty($query) ) {
			return $this;
		}
		
		$stmt = $this->mysqli->prepare($query);
		if( !$stmt ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error, 9);
			return $this;
		}
		if( !empty($this->binddef) ) {
			$this->_realBind($stmt);
		}
		
		logQuery($query, $this->binddef, $this->bindparams);
		
		// Die Datensätze zwischenspeichern und das MySQLi_Result wieder freigeben.
		if( !$stmt->execute() ) {
			$this->error = $stmt->error;
			logMsg('MySQL error: ' . $this->error, 9);
		}
		else {
			$result = $stmt->get_result();
			while( ($row = $result->fetch_array()) !== null ) {
				$this->data[] = $row;
			}
			$this->countAffectedRows = count($this->data);
		}
		
		$stmt->free_result();
		$stmt->close();
		return $this;
	}
	
	/**
	 * Replaces values of the fields set using {@link #fields()} with the passed values in all
	 * matching rows.
	 * 
	 * You may use {@link #fielddef()} to specify the expected types of the new values. In case
	 * the field def does not provide enough information for all arguments, the remaining, unspecified
	 * arguments are assumed to be strings.
	 * 
	 * @param  mixed    $values... Values to replace with. Optionally may be ab array containing said values.
	 * @return Database            This instance to enable method chaining.
	 */
	public function replace( $values ) {
		$this->cleanup();
		
		$fields    = $this->fields === '*' ? $this->getColumns() : $this->prepareFields();
		$typedef   = str_pad($this->fielddef, count($fields), 's');
		if( !empty($this->binddef) ) {
			$typedef  .= $this->binddef;
		}
		
		$args = func_get_args();
		if( is_array($args[0]) ) {
			$values = $args[0];
		}
		else {
			$values = $args;
		}
		
		if( !is_array($values) ) {
			throw new InvalidArgumentException('Expected parameter 1 (values) to be an array');
		}
		
		$query = $this->query = $this->generateUpdateQuery($this->getState());
		if( empty($query) ) {
			return $this;
		}
		
		$this->query = $query;
		$stmt = $this->mysqli->prepare($query);
		if( !$stmt ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error, 9);
			return $this;
		}
		
		$args   = array($typedef);
		$params = array();
		for( $i = 0; $i < count($values); ++$i ) {
			$args[]   = &$values[$i];
			$params[] =  $values[$i];
		}
		for( $i = 0; $i < count($this->bindparams); ++$i ) {
			$args[]   = &$this->bindparams[$i];
			$params[] =  $this->bindparams[$i];
		}
		call_user_func_array(array($stmt, 'bind_param'), $args);
		
		logQuery($query, $typedef, $params);
		
		
		// Die betroffenen Datensätze zählen.
		if( !$stmt->execute() ) {
			$this->error = $stmt->error;
			logMsg('MySQL error: ' . $this->error, 9);
		}
		else {
			$this->countAffectedRows = $stmt->affected_rows;
		}
		$stmt->close();
		
		return $this;
	}
	
	/**
	 * Appends a new data set to the end of the table. Fields are specified using {@link #fields()}
	 * and values are passed as arguments to this method in the same order as the fields.
	 * 
	 * It is possible to not specify any fields. In that case, '*' is assumed and the order of fields
	 * as registered in MySQL is retrieved and used.
	 * 
	 * @param  mixed|array $values... to insert into the new data set.
	 * @return Database               This instance to enable method chaining.
	 */
	public function append( ) {
		$this->cleanup();
		
		$args = func_get_args();
		if( is_array($args[0]) ) {
			$values = $args[0];
		}
		else {
			$values = $args;
		}
		$fields   = $this->fields === '*' ? $this->getColumns() : $this->prepareFields();
		$values   = $this->prepareValues($values);
		$fielddef = str_pad($this->fielddef, count($fields), 's');
		
		if( !is_array($values) ) {
			throw new InvalidArgumentException('Expected parameter 1 (values) to be an array');
		}
		
		$query = $this->query = $this->generateInsertQuery($this->getState());
		if( empty($query) ) {
			return $this;
		}
		
		$this->query = $query;
		$stmt = $this->mysqli->prepare($query);
		if( !$stmt ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error, 9);
			return $this;
		}
		
		// Eingaben binden
		$args   = array($fielddef);
		$params = array();
		for( $i = 0; $i < count($values); ++$i ) {
			$args[]   = &$values[$i];
			$params[] =  $values[$i];
		}
		call_user_func_array(array($stmt, 'bind_param'), $args);
		
		logQuery($query, $fielddef, $params);
		
		// Betroffene Datensätze "zählen". Hauptsächlich zur Bestätigung, dass der Datensatz tatsächlich eingefügt wurde.
		if( !$stmt->execute() ) {
			$this->error = $stmt->error;
			logMsg('MySQL error: ' . $this->error);
		}
		else {
			$this->countAffectedRows = $stmt->affected_rows;
		}
		$stmt->close();
		
		return $this;
	}
	
	/**
	 * Similar to {@link #append()}, except this method insert multiple data sets into the database. The multiple arrays
	 * can be passed as infinite arguments, or as single nested array.
	 * @return Database This instance to enable method chaining.
	 */
	public function appendAll( ) {
		$this->cleanup();
		
		$args = func_get_args();
		if( count($args) === 1 ) {
			$values = $args[0];
		}
		else {
			$values = $args;
		}
		
		$fields    = $this->fields === '*' ? $this->getColumns() : $this->prepareFields();
		$typedef   = str_repeat(str_pad($this->fielddef, count($fields), 's'), count($values));
		
		if( !is_array($values) ) {
			throw new InvalidArgumentException('Expected arrays as parameter');
		}
		
		$query = $this->query = $this->generateMultipleInsertQuery($this->getState(), $values);
		if( empty($query) ) {
			return $this;
		}
		
		$stmt = $this->mysqli->prepare($query);
		if( !$stmt ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error);
			return $this;
		}
		
		// Eingaben binden
		$args   = array($typedef);
		$params = array();
		for( $i = 0; $i < count($values); ++$i ) {
			$row = &$values[$i];
			for( $j = 0; $j < count($row); ++$j ) {
				$args[]   = &$row[$j];
				$params[] =  $row[$j];
			}
		}
		call_user_func_array(array($stmt, 'bind_param'), $args);
		
		logQuery($query, $typedef, $params);
		
		if( !$stmt->execute() ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error);
		}
		else {
			$this->countAffectedRows = $stmt->affected_rows;
		}
		$stmt->close();
		
		return $this;
	}
	
	/**
	 * Marks a set of rows as deleted.
	 * @return Database This instance to enable method chaining.
	 */
	public function delete( ) {
		$this->cleanup();
		
		$db         = $this->prepareIdentifier($this->db);
		$table      = $this->prepareIdentifier($this->table);
		$filter     = $this->prepareFilter();
		
		// Sanity check
		if( empty($db) or empty($table) ) {
			logMsg("Database: attempted to delete rows from unnamed database or table", 3, 5);
			return $this;
		}
		if( empty($filter) ) {
			logMsg("Database: rejected deleting all rows in $db.$table, please use Database::truncate() instead", 3, 5);
			return $this;
		}
		
		$query = "UPDATE `$db`.`$table` ";
		if( !empty($this->join) ) {
			$query .= $this->join->str($db);
		}
		$query .= " SET `$db`.`$table`.`DELETED`=1 WHERE $filter";
		$this->query = $query;
		
		if( !($stmt = $this->mysqli->prepare($query)) ) {
			$this->error = $this->mysqli->error;
		}
		else {
			if( !empty($this->binddef) ) {
				$this->_realBind($stmt);
			}
			
			logQuery($query, $this->binddef, $this->bindparams);
			
			if( !$stmt->execute() ) {
				$this->error = $this->mysqli->error;
				logMsg('MySQL error: ' . $this->error);
			}
			else {
				$this->countAffectedRows = $this->mysqli->affected_rows;
				logMsg("Database: marked {$this->countAffectedRows} rows as deleted", 3, 5);
			}
			$stmt->close();
		}
		
		return $this;
	}
	
	/**
	 * Actually deletes a set of rows from the table. The row can no longer be restored afterwards.
	 * @return Database This instance to enable method chaining.
	 */
	public function realDelete( ) {
		$this->cleanup();
		
		$db     = $this->prepareIdentifier($this->db);
		$table  = $this->prepareIdentifier($this->table);
		$filter = $this->prepareFilter();
		
		// Sanity check
		if( empty($db) or empty($table) ) {
			logMsg("Database: attempted to delete rows from unnamed database or table", 3, 5);
			return $this;
		}
		if( empty($filter) ) {
			logMsg("Database: rejected to delete all rows in $db.$table, please use Database::truncate() instead", 3, 5);
			return $this;
		}
		
		$query = "DELETE FROM `$db`.`$table` ";
		if( !empty($this->join) ) {
			$query .= $this->join->str($db);
		}
		$query .= " WHERE $filter";
		$this->query = $query;
		
		if( !($stmt = $this->mysqli->prepare($query)) ) {
			$this->error = $this->mysqli->error;
			logMsg('MySQL error: ' . $this->error);
		}
		else {
			if( !empty($this->binddef) ) {
				$this->_realBind($stmt);
			}
			
			logQuery($query, $this->binddef, $this->bindparams);
			
			if( !$stmt->execute() ) {
				$this->error = $this->mysqli->error;
				logMsg('MySQL error: ' . $this->error);
			}
			else {
				$this->countAffectedRows = $this->mysqli->affected_rows;
				logMsg("Database: deleted {$this->countAffectedRows} rows", 3, 5);
			}
		}
		
		return $this;
	}
	
	/**
	 * Marks all rows of a table as deleted.
	 * @return Database This instance to enable method chaining.
	 */
	public function truncate( ) {
		$this->cleanup();
		
		$db     = $this->prepareIdentifier($this->db);
		$table  = $this->prepareIdentifier($this->table);
		
		// Sanity check
		if( empty($db) or empty($table) ) {
			logMsg("Database: attempted to delete unnamed table", 3, 5);
			return $this;
		}
		
		$this->query = $query = "UPDATE `$db`.`$table` SET DELETED=1";
		logQuery($query);
		if( !$this->mysqli->query($query) ) {
			$this->error = $this->mysqli->error;
		}
		else {
			$this->countAffectedRows = $this->mysqli->affected_rows;
			logMsg("Database: marked {$this->countAffectedRows} rows as deleted", 3, 5);
		}
		
		return $this;
	}
	
	/**
	 * Actually truncates a table. The rows can no longer be restored. Depending on your database
	 * engine, the automatically incremented columns may be reset.
	 * @return Database This instance to enable method chaining.
	 */
	public function realTruncate( ) {
		$this->cleanup();
		
		$db    = $this->prepareIdentifier($this->db);
		$table = $this->prepareIdentifier($this->table);
		
		// Sanity check
		if( empty($db) or empty($table) ) {
			logMsg("Database: no database or table given", 3, 5);
			return $this;
		}
		
		$this->query = $query = "TRUNCATE TABLE `$db`.`$table`";
		logQuery($query);
		if( !$this->mysqli->query($query) ) {
			$this->error = $this->mysqli->error;
		}
		else {
			$this->countAffectedRows = 1;
			logMsg("Database: successfully truncated table $db.$table", 3, 5);
		}
		
		return $this;
	}
	
	/**
	 * Whether to ignore the deleted flag of a data set in subsequent queries. Defaults to false.
	 * @param  boolean  $value
	 * @return Database This instance to enable method chaining.
	 */
	public function ignoreDeleted( $value = true ) {
		$this->ignoreDeleted = !!$value;
		return $this;
	}
	
	/**
	 * Whether to ignore the hidden flag of a data set in subsequent queries. Defaults to false.
	 * @param  boolean  $value
	 * @return Database This instance to enable method chaining.
	 */
	public function ignoreHidden( $value = true ) {
		$this->ignoreHidden = !!$value;
		return $this;
	}
	
	/**
	 * Liefert die Anzahl der bearbeiteten / zurückgelieferten Reihen aus dem letzten Query.
	 * @return number Die Anzahl der Reihen.
	 */
	public function count( ) {
		return $this->countAffectedRows;
	}
	
	/**
	 * Überprüft, ob die letzte Anfrage mehr als eine Reihe betraf.
	 * @return boolean Wahr falls die letzte Anfrage mehr als eine Reihe betraf, ansonten falsch.
	 */
	public function found( ) {
		return $this->count() > 0;
	}
	
	/**
	 * Holt die nächste Ergebniszeile aus der letzten Ergebnismenge oder null falls kein
	 * Ergebnis mehr vorhanden ist.
	 * @return array|null Die nächste Ergebniszeile oder null falls keine mehr vorhanden ist.
	 */
	public function getData( ) {
		if( $this->data === null or $this->counter >= count($this->data) )
			return null;
		return $this->data[$this->counter++];
	}
	
	/**
	 * Holt die gesamte Ergebnismenge.
	 * @return array Array aus Arrays, die die Ergebnisreihen darstellen.
	 */
	public function getResult( ) {
		return $this->data;
	}
	
	
	/**
	 * Speichert den derzeitigen Query-Zustand auf dem Stack.
	 * @return database Diese Instanz zur Methodenverkettung.
	 */
	public function pushState( ) {
		$this->states['db'][]                = $this->db;
		$this->states['table'][]             = $this->table;
		$this->states['fields'][]            = $this->fields;
		$this->states['fns'][]               = $this->fns;
		$this->states['subselects'][]        = $this->subselects;
		$this->states['fielddef'][]          = $this->fielddef;
		$this->states['join'][]              = $this->join;
		$this->states['filter'][]            = $this->filter;
		$this->states['order'][]             = $this->order;
		$this->states['group'][]             = $this->group;
		$this->states['limit'][]             = $this->limit;
		$this->states['offset'][]            = $this->offset;
		$this->states['data'][]              = $this->data;
		$this->states['counter'][]           = $this->counter;
		$this->states['countAffectedRows'][] = $this->countAffectedRows;
		$this->states['binddef'][]           = $this->binddef;
		$this->states['bindparams'][]        = $this->bindparams;
		$this->states['ignoreDeleted'][]     = $this->ignoreDeleted;
		$this->states['ignoreHidden'][]      = $this->ignoreHidden;
		$this->states['error'][]             = $this->error;
		$this->states['query'][]             = $this->query;
		return $this;
	}
	
	/**
	 * Restores a previous or given query state.
	 * 
	 * If no parameter is passed in, the last state stored on stack will be restored.
	 * If an associative array has been passed, extracts as much information as possible from
	 * the array, considering the following keys:
	 * 
	 *  - db                - String. Name of the database to use
	 *  - table             - String. Name of the table to use
	 *  - fields            - String or array. Columns of the table to consider
	 *  - subselects        - Associative array. Column names associated with query states representing subselects.
	 *  - fns               - Associative array. Column names associated with MySQL function calls (strings). See {@link #fn()}.
	 *  - fielddef          - String. Information on expected data types of the fields. Used during {@link #append()} and {@link #replace()} only.
	 *  - join              - Join instance. Represents a chain of table joins
	 *  - filter            - String. Query filter.
	 *  - order             - String or array. Komma-separated list of column names to sort the rows by.
	 *  - group             - String. Comma-separated list of column names to group the rows by. Usually just one.
	 *  - limit             - Integer. Maximum amount of rows to read.
	 *  - offset            - Integer. Number of rows to skip from the beginning of the dataset.
	 *  - data              - Associative and indexed array. Result of the last query.
	 *  - counter           - Integer. Index of the row to be returned next.
	 *  - countAffectedRows - Integer. Number of manipulated / retrieved rows.
	 *  - binddef           - String. Type definition of bind parameters
	 *  - bindparams        - Array. Bind parameters.
	 *  - error             - String. The MySQL error which occurred during the last query.
	 *  - query             - String. The last performed query - read only! It will not be considered during the query methods.
	 * 
	 * @param  array    $state Optional. Query state to copy.
	 * @return database        This instance to enable method chaining.
	 */
	public function popState( $state = array() ) {
		// Restoration based upon the provided associative array.
		if( !empty($state) ) {
			if( isset($state['db'])    and !empty($state['db']) )    $this->useDB($state['db']);
			if( isset($state['table']) and !empty($state['table']) ) $this->select($state['table']);
			$this->fields            = isset($state['fields'])            ? $state['fields']            : array('*');
			$this->fns               = isset($state['fns'])               ? $state['fns']               : array();
			$this->subselects        = isset($state['subselects'])        ? $state['subselects']        : array();
			$this->fielddef          = isset($state['fielddef'])          ? $state['fielddef']          : '';
			$this->join              = isset($state['join'])              ? $state['join']              : null;
			$this->filter            = isset($state['filter'])            ? $state['filter']            : '';
			$this->order             = isset($state['order'])             ? $state['order']             : array();
			$this->group             = isset($state['group'])             ? $state['group']             : '';
			$this->limit             = isset($state['limit'])             ? $state['limit']             : -1;
			$this->offset            = isset($state['offset'])            ? $state['offset']            : 0;
			$this->data              = isset($state['data'])              ? $state['data']              : array();
			$this->counter           = isset($state['counter'])           ? $state['counter']           : 0;
			$this->countAffectedRows = isset($state['countAffectedRows']) ? $state['countAffectedRows'] : 0;
			$this->binddef           = isset($state['binddef'])           ? $state['binddef']           : '';
			$this->bindparams        = isset($state['bindparams'])        ? $state['bindparams']        : array();
			$this->ignoreDeleted     = isset($state['ignoreDeleted'])     ? $state['ignoreDeleted']     : false;
			$this->ignoreHidden      = isset($state['ignoreHidden'])      ? $state['ignoreHidden']      : false;
			$this->error             = isset($state['error'])             ? $state['error']             : '';
			$this->query             = isset($state['query'])             ? $state['query']             : '';
		}
		// Restoration based on the last pushed state.
		else {
			// No more states on the stack.
			if( !count($this->states['db']) ) {
				$this->fields            = array('*');
				$this->fns               = array();
				$this->subselects        = array();
				$this->fielddef          = '';
				$this->join              = '';
				$this->filter            = '';
				$this->order             = '';
				$this->group             = '';
				$this->limit             = -1;
				$this->offset            = 0;
				$this->data              = array();
				$this->counter           = 0;
				$this->countAffectedRows = 0;
				$this->binddef           = '';
				$this->bindparams        = array();
				$this->ignoreDeleted     = false;
				$this->ignoreHidden      = false;
				$this->error             = '';
				$this->query             = '';
			}
			// Pop the last pushed state from the stack.
			else {
				$this->useDB(array_pop($this->states['db']));
				$this->select(array_pop($this->states['table']));
				$this->fields            = array_pop($this->states['fields']);
				$this->fns               = array_pop($this->states['fns']);
				$this->subselects        = array_pop($this->states['subselects']);
				$this->fielddef          = array_pop($this->states['fielddef']);
				$this->join              = array_pop($this->states['join']);
				$this->filter            = array_pop($this->states['filter']);
				$this->order             = array_pop($this->states['order']);
				$this->group             = array_pop($this->states['group']);
				$this->limit             = array_pop($this->states['limit']);
				$this->offset            = array_pop($this->states['offset']);
				$this->data              = array_pop($this->states['data']);
				$this->counter           = array_pop($this->states['counter']);
				$this->countAffectedRows = array_pop($this->states['countAffectedRows']);
				$this->binddef           = array_pop($this->states['binddef']);
				$this->bindparams        = array_pop($this->states['bindparams']);
				$this->ignoreDeleted     = array_pop($this->states['ignoreDeleted']);
				$this->ignoreHidden      = array_pop($this->states['ignoreHidden']);
				$this->error             = array_pop($this->states['error']);
				$this->query             = array_pop($this->states['query']);
			}
		}
		return $this;
	}
	
	/**
	 * Speichert den derzeitigen Query-Zustand auf dem Stack und überschreibt den Zustand
	 * mit den Daten aus dem Parameter.
	 * Diese Methode ist ohne Parameter nutzlos.
	 * @param  array     $state Assoziatives Array, welches den Query-Zustand bereitstellt.
	 * @return database        Diese Instanz zur Methodenverkettung.
	 */
	public function pushpopState( $state ) {
		$this->pushState();
		$this->popState($state);
		return $this;
	}
	
	/**
	 * Gibt den derzeitigen Query-Zustand aus.
	 * @return array Assoziatives Array, welches den derzeitigen Query-Zustand enthält.
	 */
	public function getState( ) {
		$result = array();
		$result['db']                = $this->db;
		$result['table']             = $this->table;
		$result['fields']            = $this->fields;
		$result['fns']               = $this->fns;
		$result['subselects']        = $this->subselects;
		$result['fielddef']          = $this->fielddef;
		$result['join']              = $this->join;
		$result['filter']            = $this->filter;
		$result['order']             = $this->order;
		$result['group']             = $this->group;
		$result['limit']             = $this->limit;
		$result['offset']            = $this->offset;
		$result['data']              = $this->data;
		$result['counter']           = $this->counter;
		$result['countAffectedRows'] = $this->countAffectedRows;
		$result['binddef']           = $this->binddef;
		$result['bindparams']        = $this->bindparams;
		$result['ignoreDeleted']     = $this->ignoreDeleted;
		$result['ignoreHidden']      = $this->ignoreHidden;
		$result['error']             = $this->error;
		$result['query']             = $this->query;
		return $result;
	}
	
	
	/**
	 * Liest die Spalten der ausgewählten Tabelle aus.
	 * @return array Array aus Namen der Spalten.
	 */
	public function getColumns( ) {
		$result = $this->mysqli->query('SHOW COLUMNS IN `' . $this->prepareIdentifier($this->table) . '`');
		$data = $result->fetch_all();
		$result->free();
		
		$result = array();
		foreach( $data as $row ) {
			$result[] = $row[0];
		}
		return $result;
	}
	
	
	/**
	 * Generates a SELECT query of specific data.
	 * @param  array   $querystate   Associative array providing the specifications of the query.
	 * @param  boolean $allowDeleted Whether data sets marked as deleted are allowed in the result set. Defaults to false.
	 * @return string                MySQL query string
	 */
	public function generateSelectQuery( $querystate ) {
		$this->pushpopState($querystate);
		$db     = $this->prepareIdentifier($this->db);
		$table  = $this->prepareIdentifier($this->table);
		$fields = $this->prepareFields();
		$fns    = $this->prepareFns();
		$filter = $this->prepareFilter();
		
		// Sanity check
		if( empty($db) or empty($table) or !count($fields) ) {
			$this->popState();
			return '';
		}
		
		// SubSelects für komplexere Queries.
		if( !empty($this->subselects) ) {
			foreach( $this->subselects as $colname => $querystate ) {
				$colname = $this->prepareIdentifier(strToUpper($colname));
				$query   = $this->generateSelectQuery($querystate);
				if( !empty($query) ) {
					$fields[] = "($query) AS `$colname`";
				}
			}
		}
		if( !empty($fns) ) {
			foreach( $fns as $colname => $fn ) {
				$colname  = $this->prepareIdentifier($colname);
				$fields[] = $fn . ' AS ' . $colname;
			}
		}
		
		// Query erzeugen
		$query  = 'SELECT ' . implode(',', $fields) . " FROM `$db`.`$table`";
		
		// Join erzeugen
		if( $this->join ) {
			$query .= ' ' . $this->join->str($db);
		}
		
		// Filter anhängen.
		if( !empty($filter) ) {
			$query .= ' WHERE ' . $filter;
		}
		
		// Sortierung erzeugen.
		if( !empty($this->order) ) {
			$query .= ' ORDER BY ' . implode(', ', $this->prepareOrder());
		}
		
		// Gruppierung erzeugen.
		if( !empty($this->group) ) {
			$query .= ' GROUP BY ' . implode(', ', $this->prepareGroup());
		}
		
		// Offset / Limit erzeugen.
		if( $this->offset !== 0 or $this->limit !== -1 ) {
			$query .= ' LIMIT ' . $this->prepareLimit();
		}
		
		$this->popState();
		return $query;
	}
	
	/**
	 * Erzeugt eine Anfrage zum Aktualisieren eines oder mehrerer Datensätze. Die
	 * Werte werden dabei auf denselben bereitgestellten Wert gesetzt.
	 * @param  array  $querystate Assoziatives Array, welches die Spezifikationen der Anfrage bereitstellt.
	 * @return string             MySQL-Anfragenstring
	 */
	public function generateUpdateQuery( $querystate ) {
		$this->pushpopState($querystate);
		$db      = $this->prepareIdentifier($this->db);
		$table   = $this->prepareIdentifier($this->table);
		$fields  = $this->fields === '*' ? $this->getColumns() : $this->prepareFields();
		$filter  = $this->prepareFilter();
		$order   = $this->prepareOrder();
		
		// Sanity check
		if( !count($fields) ) {
			$this->popState();
			return '';
		}
		
		// Query erzeugen
		$query  = "UPDATE `$db`.`$table` SET ";
		
		$tmp = array();
		foreach( $fields as $field ) {
			$tmp[] = "$field=?";
		}
		$query .= implode(', ', $tmp);
		
		// Join anhängen.
		if( $this->join ) {
			$query .= ' ' . $this->join->str($db);
		}
		
		// Filter anhängen.
		if( !empty($filter) ) {
			$query .= " WHERE $filter";
		}
		
		// Sortierung erzeugen.
		if( !empty($order) ) {
			$query .= ' ORDER BY ' . implode(', ', $order);
		}
		
		// Offset / Limit erzeugen.
		if( $this->offset !== 0 or $this->limit !== -1 ) {
			$query .= ' LIMIT ' . $this->prepareLimit();
		}
		
		$this->popState();
		return $query;
	}
	
	/**
	 * Erzeugt eine Anfrage zum Einfügen eines einzelnen Datensatzes in die database.
	 * @param  array  $querystate Assoziatives Array, welches die Spezifikationen der Anfrage bereitstellt.
	 * @return string             MySQL-Anfragenstring
	 */
	public function generateInsertQuery( $querystate ) {
		$this->pushpopState($querystate);
		$db       = $this->prepareIdentifier($this->db);
		$table    = $this->prepareIdentifier($this->table);
		$fields   = $this->fields === '*' ? $this->getColumns() : $this->prepareFields();
		
		// Sanity check
		if( !count($fields) ) {
			$this->popState();
			return '';
		}
		
		// Query erzeugen
		$query = "INSERT INTO `$db`.`$table` (" . implode(',', $fields) . ') VALUES ' .
			'(' . implode(',', array_fill(0, count($fields), '?')) . ')';
		
		// Join anhängen.
		if( $this->join ) {
			$query .= ' ' . $this->join->str($db);
		}
		
		$this->popState();
		return $query;
	}
	
	/**
	 * Erzeugt eine Anfrage, die mehrere Datensätze in die database schreiben soll.
	 * @param  array  $querystate Assoziatives Array, welches die Spezifikationen der Anfrage bereitstellt.
	 * @return string             MySQL-Anfragenstring
	 */
	public function generateMultipleInsertQuery( $querystate, $values ) {
		$this->pushpopState($querystate);
		$db        = $this->prepareIdentifier($this->db);
		$table     = $this->prepareIdentifier($this->table);
		$fields    = $this->fields === '*' ? $this->getColumns() : $this->prepareFields();
		
		// Sanity check
		if( !count($fields) or !count($values) ) {
			$this->popState();
			return '';
		}
		
		// Query erzeugen
		$query = "INSERT INTO `$db`.`$table` (" . implode(', ', $fields) . ') VALUES ';
		
		$rows  = array();
		foreach( $values as $row ) {
			$rows[] = '(' . implode(',', array_fill(0, count($fields), '?')) . ')';
		}
		$query .= implode(',', $rows);
		
		// Join anhängen.
		if( $this->join ) {
			$query .= ' ' . $this->join->str($db);
		}
		
		$this->popState();
		return $query;
	}
	
	
	protected function cleanup( ) {
		$this->data              = array();
		$this->counter           = 0;
		$this->countAffectedRows = 0;
		$this->error             = '';
	}
	
	/**
	 * Säubert und normalisiert den gegebenen String als wäre er ein Identifier, d.h. ein
	 * databasename, Tabellenname, oder Feldname.
	 * @param  string $identifier Zu säubernden und zu normalisierenden String.
	 * @return string             Diese Instanz zur Methodenverkettung.
	 */
	public function prepareIdentifier( $identifier ) {
		if( $identifier === '*' ) {
			return '*';
		}
		
		$identifier = preg_replace('/[^A-z0-9\$_]/', '', $identifier);
		$identifier = strToUpper($identifier);
		return $identifier;
	}
	
	/**
	 * Normiert das Feld. Es werden database und Tabelle ergänzt, falls diese nicht im Feld
	 * spezifiziert wurden.
	 * @param  string $defaultDB    Name der ausgewählten database.
	 * @param  string $defaultTable Name der ausgewählten Tabelle.
	 * @param  string $field        Zu normierendes Feld.
	 * @return string               Gültiger Feld-Identifizierer mit database- und Tabellenidentifizierern.
	 */
	protected function prepareField( $defaultDB, $defaultTable, $field ) {
		$parts = explode('.', $field, 3);
		
		$col   = strToLower($parts[count($parts) - 1]);
		$pos   = strpos($col, ' as ');
		$as    = '';
		if( $pos !== false ) {
			$as  = ' AS ' . $this->prepareIdentifier(substr($col, $pos + 4));
			$col = substr($col, 0, $pos);
			
			$parts[count($parts) - 1] = $col;
			$field = implode('.', $parts);
		}
		
		if( count($parts) === 1 ) {
			if( $field === '*') {
				return "`$defaultTable`.*";
			}
			return "`$defaultTable`.`" . $this->prepareIdentifier($field) . '`' . $as;
		}
		else {
			if( $this->prepareIdentifier($parts[1]) === '*' ) {
				return '`' . $this->prepareIdentifier($parts[0]) . '`.*';
			}
			return "`" . $this->prepareIdentifier($parts[0]) . '`.`' . $this->prepareIdentifier($parts[1]) . '`' . $as;
		}
	}
	
	/**
	 * Säubert und normalisiert die Felder, die in der nächsten Anfrage Verwendung finden sollen.
	 * @return array Array aus den gesäuberten Feldnamen.
	 */
	protected function prepareFields( ) {
		$fields = $this->fields;
		if( is_string($fields) ) {
			$fields = explode(',', $fields);
		}
		if( !is_array($fields) ) {
			return array();
		}
		
		$db    = $this->prepareIdentifier($this->db);
		$table = $this->prepareIdentifier($this->table);
		
		$result = array();
		foreach( $fields as $field ) {
			if( !empty($field) ) {
				$cleaned = $this->prepareField($db, $table, $field);
				if( !empty($cleaned) ) {
					$result[] = $cleaned;
				}
			}
		}
		return $result;
	}
	
	/**
	 * Sanitizes MySQL function calls. Actually it doesn't. Yet. So handle with care!
	 * @return array Array of sanitized function calls.
	 */
	protected function prepareFns( ) {
		return $this->fns;
	}
	
	/**
	 * Säubert den gegebenen String so, dass dieser sicher in einer Anfrage verwendet werden kann.
	 * @param  string $value Zu säubernden Wert
	 * @return string        Gesäuberter Wert.
	 */
	protected function prepareString( $value ) {
		return $this->mysqli->real_escape_string($value);
	}
	
	/**
	 * Säubert alle Werte in dem Array so, dass diese sicher in einer Anfrage verwendet werden könnten.
	 * @param  array $values Array aus zu säubernden Werten.
	 * @return array         Array aus gesäuberten Werten.
	 */
	public function prepareValues( $values ) {
		$result = array();
		
		foreach( $values as $value ) {
			if( is_string($value) ) {
				$result[] = $this->prepareString($value);
			}
			else if( is_object($value) ) {
				// Attempt to encode simple objects.
				if( $value instanceof stdClass) {
					$result[] = $this->prepareString(json_encode($value));
				}
				// Attempt to encode the string representation of the object.
				else {
					$result[] = $this->prepareString($value);
				}
			}
			else if( is_numeric($value) ) {
				$result[] = $value;
			}
			else if( is_array($value) ) {
				$result[] = $this->prepareString(json_encode($value));
			}
			else {
				$result[] = null;
			}
		}
		
		return $result;
	}
	
	/**
	 * PLACEHOLDER
	 * Attempts to sanitize and normalize the WHERE condition.
	 * @param  boolean $allowDeleted Whether to include data sets marked as deleted.
	 * @return string                Sanitized and normalized WHERE condition.
	 */
	protected function prepareFilter( ) {
		$db    = $this->prepareIdentifier($this->db);
		$table = $this->prepareIdentifier($this->table);
		
		$filters = array();
		
		if( !empty($this->filter) ) {
			$filters[] = $this->filter; // TODO: Normierung der Identifizierer.
		}
		
		if( Config::main()->get('DBO_ENFORCE_COL_DELETED') and !$this->ignoreDeleted ) {
			$filters[] = "`$db`.`$table`.`DELETED`=0";
		}
		if( Config::main()->get('DBO_ENFORCE_COL_HIDDEN')  and !$this->ignoreHidden ) {
			$filters[] = "`$db`.`$table`.`HIDDEN`=0";
		}
		
		return empty($filters) ? '' : '(' . implode(') AND (', $filters) . ')';
	}
	
	/**
	 * Säubert und normiert die Sortierungsfelder.
	 * @return array Array aus gesäuberten Sortierungsstrings.
	 */
	protected function prepareOrder( ) {
		$result = array();
		$fields = $this->order;
		if( is_string($fields) ) {
			$fields = explode(',', $fields);
		}
		
		$db     = $this->prepareIdentifier($this->db);
		$table  = $this->prepareIdentifier($this->table);
		
		foreach( $fields as $field ) {
			$field = trim($field);
			if( !empty($field) ) {
				$direction = 'ASC';
				$pos = strpos($field, ' ');
				if( $pos !== false ) {
					$direction = strToUpper(trim(substr($field, $pos + 1)));
					$field = substr($field, 0, $pos);
					if( $direction !== 'ASC' and $direction !== 'DESC' ) {
						$direction = 'ASC';
					}
				}
				
				$result[] = $this->prepareField($db, $table, $field) . ' ' . $direction;
			}
		}
		
		return $result;
	}
	
	/**
	 * Säubert die Felder innerhalb des group-Strings.
	 * @return array Array aus Feldidentifizierern.
	 */
	protected function prepareGroup( ) {
		$result = array();
		$fields = preg_split('/[\s,]+/', $this->group);
		
		$db     = $this->prepareIdentifier($this->db);
		$table  = $this->prepareIdentifier($this->table);
		
		foreach( $fields as $field ) {
			if( !empty($field) ) {
				$result[] = $this->prepareField($db, $table, $field);
			}
		}
		
		return $result;
	}
	
	/**
	 * Säubert sowohl offset als auch limit.
	 * @return string "<offset>, <limit>"
	 */
	protected function prepareLimit( ) {
		$limit  = $this->limit === -1 ? '18446744073709551615' : preg_replace('/[^0-9]+/', '', $this->limit);
		$offset = preg_replace('/[^0-9]+/', '', $this->offset);
		return "$offset, $limit";
	}
	
}
