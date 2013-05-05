<?php namespace Hanariu\Database;

abstract class Constraint {
	

	public static function primary_key(array $keys, $table)
	{
		return new \Hanariu\Database\Constraint\Primary($keys, $table);
	}


	public static function key(array $keys, $table)
	{
		return new \Hanariu\Database\Constraint\Key($keys, $table);
	}


	public static function foreign_key($identifier, $table)
	{
		return new \Hanariu\Database\Constraint\Foreign($identifier, $table);
	}
	
	public static function unique(array $keys)
	{
		return new \Hanariu\Database\Constraint\Unique($keys);
	}
	
	public static function check($column, $operator, $value)
	{
		return new \Hanariu\Database\Constraint\Check($column, $operator, $value);
	}
	

	public $name;
	protected $_db;
	
	abstract public function compile(\Hanariu\Database $db = NULL);
	
	abstract public function drop($table, \Hanariu\Database $db = NULL);
	
}
