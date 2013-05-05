<?php namespace Hanariu\Database\Query\Builder;

class Delete extends \HanariuDatabase\Query\Builder\Where {

	protected $_table;

	public function __construct($table = NULL)
	{
		if ($table)
		{
			$this->_table = $table;
		}

		return parent::__construct(\Hanariu\Database::DELETE, '');
	}

	public function table($table)
	{
		$this->_table = $table;

		return $this;
	}

	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		$query = 'DELETE FROM '.$db->quote_table($this->_table);

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
		$this->_where = array();
		$this->_parameters = array();
		$this->_sql = NULL;
		return $this;
	}

}
