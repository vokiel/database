<?php namespace Hanariu\Database\Constraint;

class Foreign extends \Hanariu\Database\Constraint {
	
	public $actions = array(
		'cascade',
		'restrict',
		'no action',
		'set null',
		'set default'
	);
		
	protected $_references;
	protected $_on_update = 'no action';
	protected $_on_delete = 'no action';
	protected $_column;
	
	public function __construct($column, $table)
	{
		$this->name = 'fk_'.$table.'_'.$column.'_';
		$this->_column = $column;
	}
	
	public function references($table, $column)
	{
		$this->_references = array(
			$table,
			$column
		);
		
		return $this;
	}
	

	public function on_update($action)
	{
		if (in_array($action, $this->actions, FALSE))
		{
			$this->_on_update = $action;
		}
		else
		{
			throw new \Hanariu\Exception('The foreign key constraint action ":act" was not recognised', array(
				':act'	=> $action
			));
		}
		
		return $this;
	}
	

	public function on_delete($action)
	{
		if (in_array($action, $this->actions, FALSE))
		{
			$this->_on_delete = $action;
		}
		else
		{
			throw new \Hanariu\Exception('The foreign key constraint action ":act" was not recognised', array(
				':act'	=> $action
			));
		}
		
		return $this;
	}
	
	public function compile(\Hanariu\Database $db = NULL)
	{
		if ($db === NULL)
		{
			$db = \Hanariu\Database::instance();
		}
		
		list($table, $column) = $this->_references;
		
		$this->name .= $table.'_'.$column;
		
		$sql = 'CONSTRAINT '.$db->quote_identifier($this->name).
			' FOREIGN KEY ('.$db->quote_identifier($this->_column).')'.
			' REFERENCES '.$db->quote_table($table).'('.$db->quote_identifier($column).')';
		
		if (isset($this->_on_update))
		{
			$sql .= ' ON UPDATE '.strtoupper($this->_on_update);
		}
		
		if(isset($this->_on_delete))
		{
			$sql .= ' ON DELETE '.strtoupper($this->_on_delete);
		}
		
		return $sql;
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
				->drop($this->name, 'foreign key')
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
