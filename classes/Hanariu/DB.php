<?php namespace Hanariu;

class DB {

	public static function query($type, $sql)
	{
		return new \Hanariu\Database\Query($type, $sql);
	}

	public static function select($columns = NULL)
	{
		return new \Hanariu\Database\Query\Builder\Select(func_get_args());
	}

	public static function select_array(array $columns = NULL)
	{
		return new \Hanariu\Database\Query\Builder\Select($columns);
	}

	public static function insert($table = NULL, array $columns = NULL)
	{
		return new \Hanariu\Database\Query\Builder\Insert($table, $columns);
	}

	public static function update($table = NULL)
	{
		return new \Hanariu\Database\Query\Builder\Update($table);
	}

	public static function delete($table = NULL)
	{
		return new \Hanariu\Database\Query\Builder\Delete($table);
	}

	public static function expr($string, $parameters = array())
	{
		return new \Hanariu\Database\Expression($string, $parameters);
	}

	public static function alter($table)
	{
		return new \Hanariu\Database\Query\Builder\Alter($table);
	}

	public static function create($table)
	{
		return new \Hanariu\Database\Query\Builder\Create($table);
	}
	
	public static function drop($type, $name)
	{
		return new \Hanariu\Database\Query\Builder\Drop($type, $name);
	}
	
	public static function truncate($table)
	{
		return new \Hanariu\Database\Query\Builder\Truncate($table);
	}

}
