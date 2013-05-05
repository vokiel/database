<?php namespace Hanariu\Database\Query\Builder;

class Alter extends \Hanariu\Database\Query\Builder {
	
	protected $_table;
	protected $_modify;
	protected $_add_columns = array();
	protected $_add_constraints = array();
	protected $_drop = array();
	

	public function __construct($table)
	{
		$this->_table = $table;
		parent::__construct(\Hanariu\Database::ALTER, '');
	}
	

	public function modify(\Hanariu\Database\Column $column)
	{
		$this->_modify = $column;
		return $this;
	}
	
	public function drop($name, $type = 'column')
	{
		$this->_drop = array($name, $type);
		return $this;
	}
	

	public function add($object)
	{
		if ($object instanceof \Hanariu\Database\Column)
		{
			$this->_add_columns[] = $object;
		}
		elseif ($object instanceof \Hanariu\Database\Constraint)
		{
			$this->_add_constraints[] = $object;
		}
		else
		{
			throw new \Hanariu\Exception('Unrecognised add object :obj', array(
				':obj' => $object
			));
		}
		
		return $this;
	}
	
	public function compile($db= NULL)
	{
		if ($sql = $this->_compile_add($db))
		{
			return 'ALTER TABLE '.$db->quote_table($this->_table).' '.$sql;
		}
		elseif ($sql = $this->_compile_modify($db))
		{
			return 'ALTER TABLE '.$db->quote_table($this->_table).' '.$sql;
		}
		elseif ($sql = $this->_compile_drop($db))
		{
			return 'ALTER TABLE '.$db->quote_table($this->_table).' '.$sql;
		}
		else
		{
			return NULL;
		}
	}
	
	public function reset()
	{
		$this->_modify =
		$this->_drop =
		$this->_table = NULL;
		$this->_add_columns =
		$this->_add_constraints = array();
	}
	
	protected function _compile_add(\Hanariu\Database $db)
	{
		$sql = '';
		
		if ( ! empty($this->_add_columns) OR ! empty($this->_add_constraints))
		{
			$multi = count($this->_add_columns) + count($this->_add_constraints) > 1;
			
			$sql .= 'ADD '.($multi ? '(' : '');
			
			foreach ($this->_add_columns as $column)
			{
				$sql .= $column->compile().',';
			}
			
			foreach ($this->_add_constraints as $constraint)
			{
				$sql .= $constraint->compile($db).',';
			}
			
			$sql = rtrim($sql, ',').($multi ? ')' : '').';';
		}
		
		return $sql;
	}
	
	protected function _compile_modify(\Hanariu\Database $db)
	{
		if (isset($this->_modify))
		{
			return 'MODIFY '.$this->_modify->compile($db);
		}
		
		return '';
	}
	

	protected function _compile_drop(\Hanariu\Database $db)
	{
		if ( ! empty($this->_drop))
		{
			list($name, $type) = $this->_drop;
			return \Hanariu\DB::drop($type, $name)->compile($db);
		}
		
		return '';
	}
	
}
