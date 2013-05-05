<?php namespace Hanariu\Database\Query\Builder;

class Insert extends \Hanariu\Database\Query\Builder {

	protected $_table;
	protected $_columns = array();
	protected $_values = array();

	public function __construct($table = NULL, array $columns = NULL)
	{
		if ($table)
		{
			$this->_table = $table;
		}

		if ($columns)
		{
			$this->_columns = $columns;
		}

		return parent::__construct(\Hanariu\Database::INSERT, '');
	}

	public function table($table)
	{
		$this->_table = $table;
		return $this;
	}


	public function columns(array $columns)
	{
		$this->_columns = $columns;
		return $this;
	}


	public function values(array $values)
	{
		if ( ! is_array($this->_values))
		{
			throw new \Hanariu\Exception('INSERT INTO ... SELECT statements cannot be combined with INSERT INTO ... VALUES');
		}

		$values = func_get_args();
		$this->_values = array_merge($this->_values, $values);
		return $this;
	}


	public function select(\Hanariu\Database\Query $query)
	{
		if ($query->type() !== \Hanariu\Database::SELECT)
		{
			throw new \Hanariu\Exception('Only SELECT queries can be combined with INSERT queries');
		}

		$this->_values = $query;

		return $this;
	}


	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		$query = 'INSERT INTO '.$db->quote_table($this->_table);
		$query .= ' ('.implode(', ', array_map(array($db, 'quote_column'), $this->_columns)).') ';

		if (is_array($this->_values))
		{
			$quote = array($db, 'quote');

			$groups = array();
			foreach ($this->_values as $group)
			{
				foreach ($group as $offset => $value)
				{
					if ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE)
					{
						$group[$offset] = $db->quote($value);
					}
				}

				$groups[] = '('.implode(', ', $group).')';
			}

			// Add the values
			$query .= 'VALUES '.implode(', ', $groups);
		}
		else
		{
			// Add the sub-query
			$query .= (string) $this->_values;
		}

		$this->_sql = $query;

		return parent::compile($db);;
	}

	public function reset()
	{
		$this->_table = NULL;
		$this->_columns =
		$this->_values  = array();
		$this->_parameters = array();
		$this->_sql = NULL;
		return $this;
	}

}
