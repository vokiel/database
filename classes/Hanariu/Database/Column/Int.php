<?php namespace Hanariu\Database\Column;

class Int extends \Hanariu\Database\Column {
	
	public $max_value;
	public $min_value;
	public $scale;
	public $auto_increment;
	
	public function parameters($set = NULL)
	{
		if ($set === NULL)
		{
			return isset($this->scale) ? array($this->scale) : array();
		}
		else
		{
			$this->scale = $set;
		}
	}
	
	protected function _load_schema(array $schema)
	{
		$this->scale = \Hanariu\Arr::get($schema, 'numeric_scale');
		$this->max_value = \Hanariu\Arr::get($schema, 'max');
		$this->min_value = \Hanariu\Arr::get($schema, 'min');
	}
	
	protected function _constraints()
	{
		if ($this->auto_increment)
		{
			return array('auto_increment');
		}
		
		return array();
	}
	
}
