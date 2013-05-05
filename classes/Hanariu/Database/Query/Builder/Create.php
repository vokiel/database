<?php namespace Hanariu\Database\Query\Builder;

class Create extends \Hanariu\Database\Query\Builder {
	
	protected $_table;
	protected $_columns = array();
	protected $_options = array();
	protected $_constraints = array();
	
	public function __construct($table)
	{		
		$this->_table = $table;
		parent::__construct(\Hanariu\Database::CREATE, '');
	}
	
	public function columns(array $columns)
	{
		$this->_columns += $columns;
		return $this;
	}
	

	public function constraints(array $constraints)
	{
		$this->_constraints += $constraints;
		return $this;
	}
	

	public function options(array $options)
	{
		$this->_options += $options;
		return $this;
	}
	
	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		$sql = 'CREATE TABLE '.$db->quote_table($this->_table).' ';
		
		if ( ! empty($this->_columns))
		{
			$sql .= '(';
			
			foreach($this->_columns as $column)
			{
				$sql .= $column->compile($db).',';
			}
			
			foreach($this->_constraints as $constraint)
			{
				$sql .= $constraint->compile($db).',';
			}
			
			$sql = rtrim($sql, ',').') ';
		}
		
		foreach($this->_options as $key => $option)
		{
			$sql .= \Hanariu\Database\Query\Builder::compile_statement(array($key => $option)).' ';
		}
		
		return $sql;
	}
	
	public function reset()
	{
		$this->_table = NULL;
		$this->_columns =
		$this->_options =
		$this->_constraints = array();
	}
	
}
