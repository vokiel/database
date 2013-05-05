<?php namespace Hanariu\Database\Constraint;

class Check extends \Hanariu\Database\Constraint {
	

	protected $_checks;

	public function __construct($column, $operator, $value)
	{
		$this->name = 'ck_'.$column;
		
		$this->_checks[] = array(
			$column,
			$operator,
			$value
		);
	}
	
	public function check_and($column, $operator, $value)
	{
		$this->_add_rule($column, $operator, $value, 'AND');
		
		return $this;
	}

	public function check_or($column, $operator, $value)
	{
		$this->_add_rule($column, $operator, $value, 'OR');
		
		return $this;
	}
	
	public function compile(\Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		$sql = 'CONSTRAINT '.$db->quote_identifier($this->name).' CHECK (';
		
		foreach ($this->_checks as $check)
		{
			$key = key($check);
			
			if ( ! is_int($key))
			{
				$sql .= ' '.$key.' ';
				$check = current($check[$key]);
			}
			
			list($column, $operator, $value) = $check;
			
			$column = $db->quote_identifier($column);
			$value = $db->quote($value);
			
			$sql .= $column.$operator.$value;
		}
		
		return $sql.')';
	}
	
	public function drop($table, \Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		$this->compile($db);
		
		return \Hanariu\DB::alter($table)
			->drop($this->name, 'constraint')
			->execute($db);
	}
	
	protected function _add_rule($column, $operator, $value, $key)
	{
		$this->name .= '_'.$column;
		
		$this->_checks[] = array(
			'OR' => array(
				$column,
				$operator,
				$value
			)
		);
	}
	
}
