<?php namespace Hanariu\Database;

class Table {
	

	public static function instance($name, \Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		$schema = $db->list_tables($name);
		
		if ( ! empty($schema))
		{
			return new self($name, $db, $schema);
		}
		
		return NULL;
	}
	
	public static function factory($name, \Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		return new self($name, $db);
	}
	
	public $name;
	protected $_columns = array();
	protected $_options = array();
	protected $_constraints = array();
	protected $_loaded;
	protected $_db;
	
	public function __construct($name, $db, array $schema = NULL)
	{
		$this->name = $name;
		$this->_db = $db;
		$this->_loaded = $schema !== NULL;
	}
	
	public function loaded()
	{
		return $this->_loaded;
	}
	
	public function constraints($name = NULL)
	{
		if ($name === NULL)
		{
			return $this->_constraints;
		}
		else
		{
			return $this->_constraints[$name];
		}
	}
	
	public function options($key = NULL)
	{
		if ($key === NULL)
		{
			return $this->_options;
		}
		else
		{
			return $this->_options[$key];
		}
	}
	
	public function columns($like = NULL)
	{
		if ($this->_loaded)
		{
			if ($name !== NULL)
			{
				return \Hanariu\Database\Column::instance($this->name, $name);
			}
			
			$columns = $this->_db->list_columns($this->name);
			
			foreach ($columns as $name => $schema)
			{
				$this->_columns[$column] = \Hanariu\Database\Column::instance($this->name, $name);
			}
		}
		else
		{
			return $this->_columns;
		}
	}
	
	public function add_column(array $column)
	{
		if(!array_key_exists('type', $column) or !array_key_exists('name', $column))
		{
			throw new \Hanariu\Exception('Column need to get name and type.');
		}
		else{
			$c = \Hanariu\Database\Column::factory($column['type']);
			foreach ($column as $key => $val)
			{
				$c->$key = $val;
			}
			$this->_columns[$column['name']] = $c;
		}

		return $this;
	}
	
	public function add_constraint($type, $val)
	{
		switch ($type)
		{
			case 'check':
				list($column, $operator, $value) = $val;
				$c = \Hanariu\Database\Constraint::check($column, $operator, $value);
			break;

			case 'primary_key':
				$c = \Hanariu\Database\Constraint::primary_key(array($val),$this->name);
			break;

			case 'key':
				$c = \Hanariu\Database\Constraint::key(array($val),$this->name);
			break;

			case 'foreign_key':
				list($key, $reft, $refc) = $val;
				$c = \Hanariu\Database\Constraint::foreign_key($key,$this->name)
						->references($reft, $refc)
						->on_update('cascade')
						->on_delete('cascade');
			break;
		}

		if(!empty($c->name))
		{
			$this->_constraints[$c->name] = $c;
		}

		
		return $this;
	}
	
	public function add_option($key, $value = NULL)
	{
		if ($value === NULL)
		{
			$this->_options[] = $key;
		}
		else
		{
			$this->_options[$key] = $value;
		}
		
		return $this;
	}
	
	public function create()
	{
		$this->_loaded = TRUE;
		
		\Hanariu\DB::create($this->name)
			->columns($this->_columns)
			->constraints($this->_constraints)
			->options($this->_options)
			->execute($this->_db);
			
		return $this;
	}
	
	public function __clone()
	{
		$this->_loaded = FALSE;
	}
	
}
