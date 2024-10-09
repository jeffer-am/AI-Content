<?php

namespace AIMuse\Database;

use Closure;
use DateTimeInterface;
use Exception;
use AIMuseVendor\Illuminate\Database\Concerns\ManagesTransactions;
use AIMuseVendor\Illuminate\Database\Connection as BaseConnection;
use AIMuseVendor\Illuminate\Database\ConnectionInterface;
use AIMuseVendor\Illuminate\Database\DetectsConcurrencyErrors;
use AIMuseVendor\Illuminate\Database\DetectsLostConnections;
use AIMuseVendor\Illuminate\Database\Events\StatementPrepared;
use AIMuseVendor\Illuminate\Database\Grammar;
use AIMuseVendor\Illuminate\Database\Query\Grammars\MySqlGrammar as QueryGrammar;
use AIMuseVendor\Illuminate\Database\Schema\Grammars\MySqlGrammar as SchemaGrammar;
use AIMuseVendor\Illuminate\Database\Schema\MySqlBuilder;
use AIMuseVendor\Illuminate\Support\Facades\Log;
use mysqli;

class Connection extends BaseConnection implements ConnectionInterface
{
  use DetectsConcurrencyErrors;
  use DetectsLostConnections;
  use ManagesTransactions;

  /**
   * The active MySqli connection.
   *
   * @var \mysqli
   */
  protected $mysqli;

  /**
   * The active MySqli connection used for reads.
   *
   * @var mysqli
   */
  protected $readMySqli;

  /**
   * Create a new database connection instance.
   *
   * @param  \mysqli|\Closure $mysqli
   * @param  string $database
   * @param  string $tablePrefix
   * @param  array $config
   * @return void
   */
  public function __construct($mysqli, $database = '', $tablePrefix = '', array $config = [])
  {
    $this->mysqli = $mysqli;

    parent::__construct($mysqli, $database, $tablePrefix, $config);
  }

  /**
   * Reconnect to the database.
   *
   * @return void
   *
   * @throws \LogicException
   */
  public function reconnect()
  {
    /**
     * @var \wpdb $wpdb
     */
    global $wpdb;

    Log::info('Reconnecting to the database');

    $wpdb->db_connect(false);

    $this->setMySqli($wpdb->dbh);
  }

  /**
   * Get the default query grammar instance.
   *
   * @return Grammar|\AIMuseVendor\Illuminate\Database\Query\Grammars\MySqlGrammar
   */
  protected function getDefaultQueryGrammar()
  {
    return $this->withTablePrefix(new QueryGrammar());
  }

  /**
   * Get a schema builder instance for the connection.
   *
   * @return \AIMuseVendor\Illuminate\Database\Schema\MySqlBuilder
   */
  public function getSchemaBuilder()
  {
    if (is_null($this->schemaGrammar)) {
      $this->useDefaultSchemaGrammar();
    }

    return new MySqlBuilder($this);
  }

  /**
   * Get the default schema grammar instance.
   *
   * @return Grammar|\AIMuseVendor\Illuminate\Database\Schema\Grammars\MySqlGrammar
   */
  protected function getDefaultSchemaGrammar()
  {
    return $this->withTablePrefix(new SchemaGrammar());
  }

  /**
   * Set the table prefix and return the grammar.
   *
   * @param  \AIMuseVendor\Illuminate\Database\Grammar $grammar
   * @return \AIMuseVendor\Illuminate\Database\Grammar
   */
  public function withTablePrefix(Grammar $grammar)
  {
    $grammar->setTablePrefix($this->tablePrefix);

    return $grammar;
  }

  /**
   * Get the default post processor instance.
   *
   * @return \AIMuseVendor\Illuminate\Database\Query\Processors\Processor
   */
  protected function getDefaultPostProcessor()
  {
    return new Processor();
  }

  protected function getDoctrineDriver()
  {
    throw new Exception('Not implemented'); //return new DoctrineDriver;
  }

  /**
   * Run a select statement and return a single result.
   *
   * @param  string $query
   * @param  array $bindings
   * @param  bool $useRead
   * @return mixed
   */
  public function selectOne($query, $bindings = [], $useRead = true)
  {
    $records = $this->select($query, $bindings, $useRead);

    return array_shift($records);
  }

  /**
   * Run a select statement against the database.
   *
   * @param  string $query
   * @param  array $bindings
   * @return array
   */
  public function selectFromWriteConnection($query, $bindings = [])
  {
    return $this->select($query, $bindings, false);
  }

  /**
   * Run a select statement against the database.
   *
   * @param  string $query
   * @param  array $bindings
   * @param  bool $useRead
   * @return array
   */
  public function select($query, $bindings = [], $useRead = true)
  {
    return $this->run($query, $bindings, function ($query, $bindings) use ($useRead) {
      if ($this->pretending()) {
        return [];
      }

      // For select statements, we'll simply execute the query and return an array
      // of the database result set. Each element in the array will be a single
      // row from the database table, and will either be an array or objects.
      $statement = $this->getMySqliForSelect($useRead)->prepare($query);
      if ($statement === false) {
        throw new \Exception($this->getMySqliForSelect($useRead)->error);
      }

      $statement = $this->preparedCustom($statement);

      $this->bindValues($statement, $this->prepareBindings($bindings));

      $statement->execute();

      $result = $statement->get_result();

      if ($result) {
        return $result->fetch_all(MYSQLI_ASSOC);
      }

      return [];

      //return $statement->fetch_all(MYSQLI_ASSOC);
    });
  }

  /**
   * Get the MySqli connection to use for a select query.
   *
   * @param  bool $useRead
   * @return \mysqli
   */
  protected function getMySqliForSelect($useRead = true)
  {
    return $useRead ? $this->getReadMySqli() : $this->getMySqli();
  }


  /**
   * Run a select statement against the database and returns a generator.
   *
   * @param  string $query
   * @param  array $bindings
   * @param  bool $useReadPdo
   * @return \Generator
   */
  public function cursor($query, $bindings = [], $useReadPdo = true)
  {
    $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
      if ($this->pretending()) {
        return [];
      }

      $statement = $this->preparedCustom($this->getMySqliForSelect($useReadPdo)->prepare($query));

      $this->bindValues(
        $statement,
        $this->prepareBindings($bindings)
      );

      // Next, we'll execute the query against the database and return the statement
      // so we can return the cursor. The cursor will use a PHP generator to give
      // back one row at a time without using a bunch of memory to render them.
      $statement->execute();

      return $statement;
    });

    // @var \mysqli_result
    $result = $statement->get_result();

    while ($record = $result->fetch_object()) {
      yield $record;
    }
  }

  /**
   * Configure the mysqli prepared statement.
   *
   * @param  \mysqli_stmt $statement
   * @return \mysqli_stmt
   */
  protected function preparedCustom(\mysqli_stmt $statement)
  {
    //$statement->setFetchMode($this->fetchMode);

    $this->event(new StatementPrepared(
      $this,
      $statement
    ));

    return $statement;
  }

  /**
   * Execute an SQL statement and return the boolean result.
   *
   * @param  string $query
   * @param  array $bindings
   * @return bool
   */
  public function statement($query, $bindings = [])
  {
    return $this->run($query, $bindings, function ($query, $bindings) {
      if ($this->pretending()) {
        return true;
      }

      $statement = $this->getMySqli()->prepare($query);

      if (!$statement) {
        throw new \Exception($this->getMySqli()->error);
      }

      $this->bindValues($statement, $this->prepareBindings($bindings));

      if (!$statement) {
        throw new \Exception($this->getMySqli()->error);
      }

      return $statement->execute();
    });
  }

  /**
   * Run an SQL statement and get the number of rows affected.
   *
   * @param  string $query
   * @param  array $bindings
   * @return int
   */
  public function affectingStatement($query, $bindings = [])
  {
    return $this->run($query, $bindings, function ($query, $bindings) {
      if ($this->pretending()) {
        return 0;
      }

      // For update or delete statements, we want to get the number of rows affected
      // by the statement and return that back to the developer. We'll first need
      // to execute the statement and then we'll use PDO to fetch the affected.
      $statement = $this->getMySqli()->prepare($query);

      if (!$statement) {
        throw new \Exception($this->getMySqli()->error);
      }

      $this->bindValues($statement, $this->prepareBindings($bindings));

      $statement->execute();

      $result = $statement->get_result();

      if ($result) {
        return $result->num_rows;
      }

      return 0;
    });
  }

  /**
   * Run a raw, unprepared query against the PDO connection.
   *
   * @param  string $query
   * @return bool
   */
  public function unprepared($query)
  {
    return $this->run($query, [], function ($query) {
      if ($this->pretending()) {
        return true;
      }

      return (bool)$this->getMySqli()->query($query);
    });
  }

  /**
   * Bind values to their parameters in the given statement.
   *
   * @param  \mysqli_stmt $statement
   * @param  array $bindings
   * @return void
   */
  public function bindValues($statement, $bindings)
  {
    if (empty($bindings)) {
      return;
    }

    $types = '';

    foreach ($bindings as $key => $value) {
      if (!is_string($key)) {
        $types .= $this->getMySqliBindType($value);
      }
    }

    $params = [];
    $params[] = &$types;

    foreach ($bindings as $key => $value) {
      if (is_string($key)) {
        continue;
      }
      $params[] = &$bindings[$key];
    }

    call_user_func_array([$statement, 'bind_param'], $params);
  }

  /**
   * Prepare the query bindings for execution.
   *
   * @param  array $bindings
   * @return array
   */
  public function prepareBindings(array $bindings)
  {
    $grammar = $this->getQueryGrammar();

    foreach ($bindings as $key => $value) {
      // We need to transform all instances of DateTimeInterface into the actual
      // date string. Each query grammar maintains its own date string format
      // so we'll just ask the grammar for the format to get from the date.
      if ($value instanceof DateTimeInterface) {
        $bindings[$key] = $value->format($grammar->getDateFormat());
      } elseif ($value === false) {
        $bindings[$key] = 0;
      }
    }

    return $bindings;
  }

  /**
   * Reconnect to the database if a PDO connection is missing.
   *
   * @return void
   */
  protected function reconnectIfMissingConnection()
  {
    if (is_null($this->mysqli)) {
      $this->reconnect();
    }
  }

  /**
   * Disconnect from the underlying PDO connection.
   *
   * @return void
   */
  public function disconnect()
  {
    $this->setMySqli(null)->setReadMySqli(null);
  }

  /**
   * Is Doctrine available?
   *
   * @return bool
   */
  public function isDoctrineAvailable()
  {
    return false;
  }

  /**
   * Get the current MySqli connection.
   *
   * @return \mysqli
   */
  public function getMySqli()
  {
    if ($this->mysqli instanceof Closure) {
      return $this->mysqli = call_user_func($this->mysqli);
    }

    return $this->mysqli;
  }

  /**
   * Get the current MySqli connection used for reading.
   *
   * @return \mysqli
   */
  public function getReadMySqli()
  {
    if ($this->transactions >= 1) {
      return $this->getMySqli();
    }

    if ($this->readMySqli instanceof Closure) {
      return $this->readMySqli = call_user_func($this->readMySqli);
    }

    return $this->readMySqli ?: $this->getMySqli();
  }

  /**
   * Set the MySqli connection.
   *
   * @param  \mysqli|null $mysqli
   * @return $this
   */
  public function setMySqli($mysqli)
  {
    $this->transactions = 0;

    $this->mysqli = $mysqli;

    return $this;
  }

  /**
   * Set the mysqli connection used for reading.
   *
   * @param  \mysqli|null $mysqli
   * @return $this
   */
  public function setReadMySqli($mysqli)
  {
    $this->readMySqli = $mysqli;

    return $this;
  }

  /**
   * Get the current PDO connection.
   *
   * @return \mysqli
   */
  public function getPdo()
  {
    return $this->getMySqli();
  }

  /**
   * Get the current PDO connection used for reading.
   *
   * @return \mysqli
   */
  public function getReadPdo()
  {
    return $this->getReadMySqli();
  }

  /**
   * Set the PDO connection.
   *
   * @param  \mysqli|null  $mysqli
   * @return $this
   */
  public function setPdo($mysqli)
  {
    return $this->setMySqli($mysqli);
  }

  /**
   * Set the PDO connection used for reading.
   *
   * @param  \mysqli|null  $mysqli
   * @return $this
   */
  public function setReadPdo($mysqli)
  {
    return $this->setReadMySqli($mysqli);
  }

  /**
   * @param  mixed $value
   * @return string
   */
  protected function getMySqliBindType($value)
  {
    // Check if value is an expression
    if (is_callable($value)) {
      return 's';
    }

    switch (gettype($value)) {
      case 'double':
        return 'd';
      case 'integer':
      case 'boolean':
        return 'i';

      case 'string':
        return 's';
      default:
        return 's';
    }
  }
}
