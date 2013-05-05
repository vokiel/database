<?php namespace Hanariu\Database\Query\Builder;

class Update extends \Hanariu\Database\Query\Builder\Where {

	protected $_table;
	protected $_set = array();

	public function __construct($table = NULL)
	{
		if ($table)
		{
			$this->_table = $table;
		}

		return parent::__construct(\Hanariu\Database::UPDATE, '');
	}


	public function table($table)
	{
		$this->_table = $table;
		return $this;
	}

	public function set(array $pairs)
	{
		foreach ($pairs as $column => $value)
		{
			$this->_set[] = array($column, $value);
		}

		return $this;
	}

	public function value($column, $value)
	{
		$this->_set[] = array($column, $value);
		return $this;
	}

	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		$query = 'UPDATE '.$db->quote_table($this->_table);
		$query .= ' SET '.$this->_compile_set($db, $this->_set);

		if ( ! empty($this->_where))
		{
			$query .= ' WHERE '.$this->_compile_conditions($db, $this->_where);
		}

		if ( ! empty($this->_order_by))
		{
			$query .= ' '.$this->_compile_order_by($db, $this->_order_by);
		}

		if ($this->_limit !== NULL)
		{
			$query .= ' LIMIT '.$this->_limit;
		}

		$this->_sql = $query;

		return parent::compile($db);
	}

	public function reset()
	{
		$this->_table = NULL;
		$this->_set   =
		$this->_where = array();
		$this->_limit = NULL;
		$this->_parameters = array();
		$this->_sql = NULL;
		return $this;
	}


}
