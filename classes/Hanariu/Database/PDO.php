<?php namespace Hanariu\Database;

class PDO extends \Hanariu\Database {

	protected $_identifier = '';

	public function __construct($name, array $config)
	{
		parent::__construct($name, $config);

		if (isset($this->_config['identifier']))
		{
			$this->_identifier = (string) $this->_config['identifier'];
		}
	}

	public function connect()
	{
		if ($this->_connection)
			return;

		extract($this->_config['connection'] + array(
			'dsn'        => '',
			'username'   => NULL,
			'password'   => NULL,
			'persistent' => FALSE,
		));

		unset($this->_config['connection']);
		$options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;

		if ( ! empty($persistent))
		{
			$options[\PDO::ATTR_PERSISTENT] = TRUE;
		}

		try
		{
			$this->_connection = new \PDO($dsn, $username, $password, $options);
		}
		catch (\PDOException $e)
		{
			throw new \Hanariu\Database\Exception(':error',
				array(':error' => $e->getMessage()),
				$e->getCode());
		}
	}

	public function create_aggregate($name, $step, $final, $arguments = -1)
	{
		$this->_connection or $this->connect();

		return $this->_connection->sqliteCreateAggregate(
			$name, $step, $final, $arguments
		);
	}

	public function create_function($name, $callback, $arguments = -1)
	{
		$this->_connection or $this->connect();

		return $this->_connection->sqliteCreateFunction(
			$name, $callback, $arguments
		);
	}

	public function disconnect()
	{
		$this->_connection = NULL;
		return parent::disconnect();
	}

	public function set_charset($charset)
	{
		$this->_connection OR $this->connect();
		$this->_connection->exec('SET NAMES '.$this->quote($charset));
	}

	public function query($type, $sql, $as_object = FALSE, array $params = NULL)
	{
		$this->_connection or $this->connect();

		if (\Hanariu\Hanariu::$profiling)
		{
			$benchmark = \Hanariu\Profiler::start("Database ({$this->_instance})", $sql);
		}

		try
		{
			$result = $this->_connection->query($sql);
		}
		catch (\Exception $e)
		{
			if (isset($benchmark))
			{
				\Hanariu\Profiler::delete($benchmark);
			}

			throw new \Hanariu\Database\Exception(':error [ :query ]',
				array(
					':error' => $e->getMessage(),
					':query' => $sql
				),
				$e->getCode());
		}

		if (isset($benchmark))
		{
			\Hanariu\Profiler::stop($benchmark);
		}

		$this->last_query = $sql;

		if ($type === \Hanariu\Database::SELECT)
		{
			if ($as_object === FALSE)
			{
				$result->setFetchMode(\PDO::FETCH_ASSOC);
			}
			elseif (is_string($as_object))
			{
				$result->setFetchMode(\PDO::FETCH_CLASS, $as_object, $params);
			}
			else
			{
				$result->setFetchMode(\PDO::FETCH_CLASS, 'stdClass');
			}

			$result = $result->fetchAll();
			return new \Hanariu\Database\Result\Cached($result, $sql, $as_object, $params);
		}
		elseif ($type === \Hanariu\Database::INSERT)
		{
			return array(
				$this->_connection->lastInsertId(),
				$result->rowCount(),
			);
		}
		else
		{
			return $result->rowCount();
		}
	}

	public function begin($mode = NULL)
	{
		$this->_connection or $this->connect();
		return $this->_connection->beginTransaction();
	}

	public function commit()
	{
		$this->_connection or $this->connect();
		return $this->_connection->commit();
	}

	public function rollback()
	{
		$this->_connection or $this->connect();
		return $this->_connection->rollBack();
	}

	public function list_tables($like = NULL)
	{
		throw new \Hanariu\Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function list_columns($table, $like = NULL, $add_prefix = TRUE)
	{
		throw new \Hanariu\Exception('Database method :method is not supported by :class',
			array(':method' => __FUNCTION__, ':class' => __CLASS__));
	}

	public function escape($value)
	{
		$this->_connection or $this->connect();
		return $this->_connection->quote($value);
	}

}
