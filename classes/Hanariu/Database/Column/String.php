<?php namespace Hanariu\Database\Column;

class String extends \Hanariu\Database\Column {
	
	public $max_length;
	public $exact;
	public $binary;
	
	public function parameters($set = NULL)
	{
		if ($this->exact)
		{
			return array();
		}
		else
		{
			if ($set === NULL)
			{
				return isset($this->max_length) ? array($this->max_length) : array();
			}
			else
			{
				$this->max_length = $set;
			}
		}
	}
	
	protected function _load_schema(array $schema)
	{
		$this->max_length = \Hanariu\Arr::get($schema, 'character_maximum_length');
		$this->binary = \Hanariu\Arr::get($schema, 'binary', FALSE);
		$this->exact = \Hanariu\Arr::get($schema, 'exact', FALSE);
	}
	
	protected function _constraints()
	{
		if ($this->binary)
		{
			return array('binary');
		}
		
		return array();
	}
	
}
