<?php namespace Hanariu\Database\Constraint;

class Unique extends \Hanariu\Database\Constraint {
	
	protected $_keys;
	
	public function __construct($keys)
	{
		if ( ! is_array($keys))
		{
			$keys = array($keys);
		}
		
		$this->name = 'key_'.implode('_', $keys);
		$this->_keys = $keys;
	}
	
	public function compile(\Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		return 'CONSTRAINT '.$db->quote_identifier($this->name).' UNIQUE ('.
			implode(',', array_map(array($db, 'quote_identifier'), $this->_keys)).')';
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
				->drop($this->name, 'index')
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
