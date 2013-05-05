<?php namespace Hanariu\Database\Column;

class Bool extends \Hanariu\Database\Column {
	
	public function parameters($set = NULL)
	{
		return NULL;
	}
	
	protected function _load_schema(array $schema)
	{
		return;
	}
	
	protected function _constraints()
	{
		return array();
	}
	
}
