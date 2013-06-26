<?php namespace Hanariu;

abstract class Database {

	const SELECT =  1;
	const INSERT =  2;
	const UPDATE =  3;
	const DELETE =  4;
	const CREATE =  5;
	const ALTER =  6;
	const DROP =  7;
	const TRUNCATE =  8;

	public static $default = 'default';
	public static $instances = array();

	public static function instance($name = NULL, array $config = NULL)
	{
		if ($name === NULL)
		{
			$name = \Hanariu\Database::$default;
		}

		if ( ! isset(\Hanariu\Database::$instances[$name]))
		{
			if ($config === NULL)
			{
				$config = \Hanariu\Hanariu::$config->load('database')->$name;
			}

			if ( ! isset($config['type']))
			{
				throw new \Hanariu\Exception('Database type not defined in :name configuration',
					array(':name' => $name));
			}

			$driver = '\Hanariu\Database\\'.ucfirst($config['type']);
			$driver = new $driver($name, $config);
      \Hanariu\Database::$instances[$name] = $driver;
		}

		return \Hanariu\Database::$instances[$name];
	}

	public $last_query;
	protected $_identifier = '"';
	protected $_instance;
	protected $_connection;
	protected $_config;

	public function __construct($name, array $config)
	{
		$this->_instance = $name;
		$this->_config = $config;

		if (empty($this->_config['table_prefix']))
		{
			$this->_config['table_prefix'] = '';
		}
	}

	public function __destruct()
	{
		$this->disconnect();
	}

	public function __toString()
	{
		return $this->_instance;
	}

	abstract public function connect();

	public function disconnect()
	{
		unset(\Hanariu\Database::$instances[$this->_instance]);

		return TRUE;
	}

	abstract public function set_charset($charset);

	abstract public function query($type, $sql, $as_object = FALSE, array $params = NULL);

	abstract public function begin($mode = NULL);

	abstract public function commit();

	abstract public function rollback();

	public function count_records($table)
	{
		$table = $this->quote_table($table);

		return $this->query(\Hanariu\Database::SELECT, 'SELECT COUNT(*) AS total_row_count FROM '.$table, FALSE)
			->get('total_row_count');
	}

	public function datatype($type)
	{
		static $types = array
		(
			// SQL-92
			'bit'                           => array('type' => 'string', 'exact' => TRUE),
			'bit varying'                   => array('type' => 'string'),
			'char'                          => array('type' => 'string', 'exact' => TRUE),
			'char varying'                  => array('type' => 'string'),
			'character'                     => array('type' => 'string', 'exact' => TRUE),
			'character varying'             => array('type' => 'string'),
			'date'                          => array('type' => 'string'),
			'dec'                           => array('type' => 'float', 'exact' => TRUE),
			'decimal'                       => array('type' => 'float', 'exact' => TRUE),
			'double precision'              => array('type' => 'float'),
			'float'                         => array('type' => 'float'),
			'int'                           => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'integer'                       => array('type' => 'int', 'min' => '-2147483648', 'max' => '2147483647'),
			'interval'                      => array('type' => 'string'),
			'national char'                 => array('type' => 'string', 'exact' => TRUE),
			'national char varying'         => array('type' => 'string'),
			'national character'            => array('type' => 'string', 'exact' => TRUE),
			'national character varying'    => array('type' => 'string'),
			'nchar'                         => array('type' => 'string', 'exact' => TRUE),
			'nchar varying'                 => array('type' => 'string'),
			'numeric'                       => array('type' => 'float', 'exact' => TRUE),
			'real'                          => array('type' => 'float'),
			'smallint'                      => array('type' => 'int', 'min' => '-32768', 'max' => '32767'),
			'time'                          => array('type' => 'string'),
			'time with time zone'           => array('type' => 'string'),
			'timestamp'                     => array('type' => 'string'),
			'timestamp with time zone'      => array('type' => 'string'),
			'varchar'                       => array('type' => 'string'),

			// SQL:1999
			'binary large object'               => array('type' => 'string', 'binary' => TRUE),
			'blob'                              => array('type' => 'string', 'binary' => TRUE),
			'boolean'                           => array('type' => 'bool'),
			'char large object'                 => array('type' => 'string'),
			'character large object'            => array('type' => 'string'),
			'clob'                              => array('type' => 'string'),
			'national character large object'   => array('type' => 'string'),
			'nchar large object'                => array('type' => 'string'),
			'nclob'                             => array('type' => 'string'),
			'time without time zone'            => array('type' => 'string'),
			'timestamp without time zone'       => array('type' => 'string'),

			// SQL:2003
			'bigint'    => array('type' => 'int', 'min' => '-9223372036854775808', 'max' => '9223372036854775807'),

			// SQL:2008
			'binary'            => array('type' => 'string', 'binary' => TRUE, 'exact' => TRUE),
			'binary varying'    => array('type' => 'string', 'binary' => TRUE),
			'varbinary'         => array('type' => 'string', 'binary' => TRUE),
		);

		if (isset($types[$type]))
			return $types[$type];

		return array();
	}

	abstract public function list_tables($like = NULL);

	abstract public function list_columns($table, $like = NULL, $add_prefix = TRUE);


	protected function _parse_type($type)
	{
		if (($open = strpos($type, '(')) === FALSE)
		{
			return array($type, NULL);
		}

		$close = strrpos($type, ')', $open);
		$length = substr($type, $open + 1, $close - 1 - $open);
		$type = substr($type, 0, $open).substr($type, $close + 1);

		return array($type, $length);
	}

	public function table_prefix()
	{
		return $this->_config['table_prefix'];
	}


	public function quote($value)
	{
		if ($value === NULL)
		{
			return 'NULL';
		}
		elseif ($value === TRUE)
		{
			return "'1'";
		}
		elseif ($value === FALSE)
		{
			return "'0'";
		}
		elseif (is_object($value))
		{
			if ($value instanceof \Hanariu\Database\Query)
			{
				return '('.$value->compile($this).')';
			}
			elseif ($value instanceof \Hanariu\Database\Expression)
			{
				return $value->compile($this);
			}
			else
			{
				return $this->quote( (string) $value);
			}
		}
		elseif (is_array($value))
		{
			return '('.implode(', ', array_map(array($this, __FUNCTION__), $value)).')';
		}
		elseif (is_int($value))
		{
			return (int) $value;
		}
		elseif (is_float($value))
		{
			return sprintf('%F', $value);
		}

		return $this->escape($value);
	}

	public function quote_column($column)
	{
		$escaped_identifier = $this->_identifier.$this->_identifier;

		if (is_array($column))
		{
			list($column, $alias) = $column;
			$alias = str_replace($this->_identifier, $escaped_identifier, $alias);
		}

		if ($column instanceof \Hanariu\Database\Query)
		{
			$column = '('.$column->compile($this).')';
		}
		elseif ($column instanceof \Hanariu\Database\Expression)
		{
			$column = $column->compile($this);
		}
		else
		{
			$column = (string) $column;

			$column = str_replace($this->_identifier, $escaped_identifier, $column);

			if ($column === '*')
			{
				return $column;
			}
			elseif (strpos($column, '.') !== FALSE)
			{
				$parts = explode('.', $column);

				if ($prefix = $this->table_prefix())
				{
					$offset = count($parts) - 2;
					$parts[$offset] = $prefix.$parts[$offset];
				}

				foreach ($parts as & $part)
				{
					if ($part !== '*')
					{
						$part = $this->_identifier.$part.$this->_identifier;
					}
				}

				$column = implode('.', $parts);
			}
			else
			{
				$column = $this->_identifier.$column.$this->_identifier;
			}
		}

		if (isset($alias))
		{
			$column .= ' AS '.$this->_identifier.$alias.$this->_identifier;
		}

		return $column;
	}

	public function quote_table($table)
	{
		$escaped_identifier = $this->_identifier.$this->_identifier;

		if (is_array($table))
		{
			list($table, $alias) = $table;
			$alias = str_replace($this->_identifier, $escaped_identifier, $alias);
		}

		if ($table instanceof \Hanariu\Database\Query)
		{
			$table = '('.$table->compile($this).')';
		}
		elseif ($table instanceof \Hanariu\Database\Expression)
		{
			$table = $table->compile($this);
		}
		else
		{
			$table = (string) $table;

			$table = str_replace($this->_identifier, $escaped_identifier, $table);

			if (strpos($table, '.') !== FALSE)
			{
				$parts = explode('.', $table);

				if ($prefix = $this->table_prefix())
				{
					$offset = count($parts) - 1;
					$parts[$offset] = $prefix.$parts[$offset];
				}

				foreach ($parts as & $part)
				{
					$part = $this->_identifier.$part.$this->_identifier;
				}

				$table = implode('.', $parts);
			}
			else
			{
				$table = $this->_identifier.$this->table_prefix().$table.$this->_identifier;
			}
		}

		if (isset($alias))
		{
			$table .= ' AS '.$this->_identifier.$this->table_prefix().$alias.$this->_identifier;
		}

		return $table;
	}


	public function quote_identifier($value)
	{
		$escaped_identifier = $this->_identifier.$this->_identifier;

		if (is_array($value))
		{
			list($value, $alias) = $value;
			$alias = str_replace($this->_identifier, $escaped_identifier, $alias);
		}

		if ($value instanceof \Hanariu\Database\Query)
		{
			// Create a sub-query
			$value = '('.$value->compile($this).')';
		}
		elseif ($value instanceof \Hanariu\Database\Expression)
		{
			$value = $value->compile($this);
		}
		else
		{
			$value = (string) $value;

			$value = str_replace($this->_identifier, $escaped_identifier, $value);

			if (strpos($value, '.') !== FALSE)
			{
				$parts = explode('.', $value);

				foreach ($parts as & $part)
				{
					$part = $this->_identifier.$part.$this->_identifier;
				}

				$value = implode('.', $parts);
			}
			else
			{
				$value = $this->_identifier.$value.$this->_identifier;
			}
		}

		if (isset($alias))
		{
			$value .= ' AS '.$this->_identifier.$alias.$this->_identifier;
		}

		return $value;
	}

	abstract public function escape($value);

} 
