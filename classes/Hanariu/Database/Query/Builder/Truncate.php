<?php namespace Hanariu\Database\Query\Builder;

class Truncate extends \Hanariu\Database\Query\Builder {
	
	protected $_table;
	
	public function __construct($table)
	{
		$this->_table = $table;
		
		parent::__construct(\Hanariu\Database::TRUNCATE, '');
	}
	
	public function compile(\Hanariu\Database $db)
	{
		return 'TRUNCATE TABLE '.$db->quote_table($this->_table);
	}
	
	public function reset()
	{
		$this->_table = NULL;
	}
	
}
