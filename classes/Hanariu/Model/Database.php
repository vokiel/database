<?php namespace Hanariu\Model;

abstract class Database extends \Hanariu\Model {

	public static function factory($name, $db = NULL)
	{
		$class = '\\Hanariu\\Model\\'.$name;

		return new $class($db);
	}

	protected $_db;

	public function __construct($db = NULL)
	{
		if ($db)
		{
			$this->_db = $db;
		}
		elseif ( ! $this->_db)
		{
			$this->_db = \Hanariu\Database::$default;
		}

		if (is_string($this->_db))
		{
			$this->_db = \Hanariu\Database::instance($this->_db);
		}
	}

} 
