<?php namespace Hanariu\Database\Column;

class Float extends Int {
	
	public $precision;
	public $exact;
	
	public function parameters($set = NULL)
	{
		if ($set === NULL)
		{
			$params = parent::parameters();
			return isset($this->precision) ? $params + array($this->precision) : $params;
		}
		else
		{
			parent::parameters($set[0]);
			$this->precision = $set[1];
		}
	}
	
	protected function _load_schema(array $schema)
	{
		$this->exact = \Hanariu\Arr::get($schema, 'exact', FALSE);
		$this->precision = \Hanariu\Arr::get($schema, 'numeric_precision');
	}
	
	protected function _constraints()
	{
		return array();
	}
	
}
