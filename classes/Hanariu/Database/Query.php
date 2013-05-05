<?php namespace Hanariu\Database;

class Query {

	protected $_type;
	protected $_force_execute = FALSE;
	protected $_lifetime = NULL;
	protected $_sql;
	protected $_parameters = array();
	protected $_as_object = FALSE;
	protected $_object_params = array();

	public function __construct($type, $sql)
	{
		$this->_type = $type;
		$this->_sql = $sql;
	}

	public function __toString()
	{
		try
		{
			return $this->compile(\Hanariu\Database::instance());
		}
		catch (\Exception $e)
		{
			return \Hanariu\Exception::text($e);
		}
	}

	public function type()
	{
		return $this->_type;
	}

	public function cached($lifetime = NULL, $force = FALSE)
	{
		if ($lifetime === NULL)
		{
			// Use the global setting
			$lifetime = Hanariu::$cache_life;
		}

		$this->_force_execute = $force;
		$this->_lifetime = $lifetime;

		return $this;
	}

	public function as_assoc()
	{
		$this->_as_object = FALSE;
		$this->_object_params = array();
		return $this;
	}


	public function as_object($class = TRUE, array $params = NULL)
	{
		$this->_as_object = $class;

		if ($params)
		{
			$this->_object_params = $params;
		}

		return $this;
	}

	public function param($param, $value)
	{
		$this->_parameters[$param] = $value;
		return $this;
	}

	public function bind($param, & $var)
	{
		$this->_parameters[$param] =& $var;
		return $this;
	}

	public function parameters(array $params)
	{
		$this->_parameters = $params + $this->_parameters;
		return $this;
	}

	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		$sql = $this->_sql;

		if ( ! empty($this->_parameters))
		{
			$values = array_map(array($db, 'quote'), $this->_parameters);
			$sql = strtr($sql, $values);
		}

		return $sql;
	}


	public function execute($db = NULL, $as_object = NULL, $object_params = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		if ($as_object === NULL)
		{
			$as_object = $this->_as_object;
		}

		if ($object_params === NULL)
		{
			$object_params = $this->_object_params;
		}

		$sql = $this->compile($db);

		if ($this->_lifetime !== NULL AND $this->_type === \Hanariu\Database::SELECT)
		{
			$cache_key = '\Hanariu\Database::query("'.$db.'", "'.$sql.'")';
			if (($result = \Hanariu\Hanariu::cache($cache_key, NULL, $this->_lifetime)) !== NULL
				AND ! $this->_force_execute)
			{
				return new \Hanariu\Database\Result\Cached($result, $sql, $as_object, $object_params);
			}
		}

		$result = $db->query($this->_type, $sql, $as_object, $object_params);

		if (isset($cache_key) AND $this->_lifetime > 0)
		{
			\Hanariu\Hanariu::cache($cache_key, $result->as_array(), $this->_lifetime);
		}

		return $result;
	}

} 
