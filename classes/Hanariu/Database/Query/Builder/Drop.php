<?php namespace Hanariu\Database\Query\Builder;

class Drop extends \Hanariu\Database\Query\Builder {
	
	protected $_name;
	
	protected $_drop_type;
	
	public function __construct($type, $name)
	{
		$this->_drop_type = strtolower($type);
		$this->_name = $name;
		
		parent::__construct(\Hanariu\Database::DROP, '');
	}
	
	public function compile($db = NULL)
	{
		switch($this->_drop_type)
		{
			case 'database':
				return 'DROP DATABASE '.$db->quote($this->_name);
			
			case 'table':
				return 'DROP TABLE '.$db->quote_table($this->_name);
				
			default:
				return 'DROP '.strtoupper($this->_drop_type).' '.$db->quote_identifier($this->_name);
		}
	}
	
	public function reset()
	{
		$this->_drop_type = NULL;
		$this->_name = NULL;
	}
	
}
