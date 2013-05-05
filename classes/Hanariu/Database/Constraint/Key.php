<?php namespace Hanariu\Database\Constraint;

class Key extends \Hanariu\Database\Constraint {
	

	protected $_keys;
	
	public function __construct(array $keys, $table)
	{
		$this->name = implode('_', $keys);
		
		$this->_keys = $keys;
	}
	
	public function compile(\Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		$key = implode(',', array_map(array($db, 'quote_identifier'), $this->_keys));
		return ' KEY '.$key.' ('.$key.')';
	}
	
	public function drop($table, \Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		$this->compile($db);
		
		if ($db instanceof \Hanariu\Database\MySQL)
		{
			return \Hanariu\DB::alter($table)
				->drop(\Hanariu\DB::expr(''), 'primary key')
				->execute($db);
		}
		else
		{
			return \Hanariu\DB::alter($table)
				->drop($this->name, 'constraint')
				->execute($db);
		}
	}
	
}
