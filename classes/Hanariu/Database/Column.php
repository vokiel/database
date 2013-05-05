<?php namespace Hanariu\Database;

abstract class Column {
	

	public static function factory($datatype, $db = NULL)
	{
		if ($db === NULL OR ! $db instanceof \Hanariu\Database)
		{
			$db = \Hanariu\Database::instance();
		}
		
		$schema = $db->datatype($datatype);
		
		$class = '\\Hanariu\\Database\\Column\\'.ucfirst($schema['type']);
		
		if (class_exists($class))
		{
			$obj = new $class($db, $schema);
			$obj->datatype = $datatype;
			
			return $obj;
		}
		else
		{
			throw new \Hanariu\Exception('The given schema type :type is not supported by the current dbforge build.', array(
				':type'	=> $schema['type']
			));
		}
	}
	

	public static function instance($table, $name)
	{
		if ($table = \Hanariu\Database\Table::instance($table))
		{
			$db = \Hanariu\Database::instance();
			
			if ($column = $db->list_columns($table->name, $name))
			{
				$class = '\\Hanariu\\Database\\Column\\'.ucfirst($column['type']);
				
				if (class_exists($class))
				{
					$column = new $class($db, $column);
				}
				else
				{
					throw new \Hanariu\Exception('The given schema type :type is not supported by the current dbforge build.', array(
						':type'	=> $column['type']
					));
				}
			}
		}
		
		return NULL;
	}
	

	public $name;
	public $default;
	public $nullable;
	public $datatype;
	public $ordinal_position;
	public $after;
	protected $_parameters;
	protected $_loaded = FALSE;
	protected $_db;
	

	public function __construct($database, array $schema = NULL)
	{
		$this->_db = $database;
		
		if ($schema !== NULL)
		{
			$this->name = \Hanariu\Arr::get($schema, 'column_name');
			$this->default = \Hanariu\Arr::get($schema, 'column_default');
			$this->is_nullable = \Hanariu\Arr::get($schema, 'is_nullable') === 'YES';
			$this->ordinal_position = \Hanariu\Arr::get($schema, 'ordinal_position');
			$this->after = \Hanariu\Arr::get($schema, 'after');
			$this->datatype = \Hanariu\Arr::get($schema, 'data_type');
			
			$this->_load_schema($schema);
			
			$this->_loaded = TRUE;
		}
	}

	public function loaded()
	{
		return $this->_loaded;
	}
	
	public function parameters($set = NULL)
	{
		if ($set === NULL)
		{
			return $this->_parameters;
		}
		else
		{
			if ( ! is_array($set))
			{
				$set = array($set);
			}
			
			$this->_parameters = $set;
		}
	}
	

	final public function compile($db = NULL)
	{
		return \Hanariu\Database\Query\Builder::compile_column($this, $db);
	}


	final public function constraints()
	{
		$constraints = array();
		
		if ($this->nullable === FALSE)
		{
			$constraints[] = 'not null';
		}

		if ($this->default)
		{
			$constraints['default'] = $this->default;
		}

		if ($this->after)
		{
			$constraints['after'] = \Hanariu\DB::expr($this->_db->quote_identifier($this->after));
		}

		return array_merge($constraints, $this->_constraints());
	}
	

	public function __clone()
	{
		$this->_loaded = FALSE;
	}
	

	abstract protected function _load_schema(array $schema);
	
	abstract protected function _constraints();
	
}
