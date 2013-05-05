<?php namespace Hanariu\Database\Query\Builder;

class Join extends \Hanariu\Database\Query\Builder {

	protected $_type;
	protected $_table;
	protected $_on = array();
	protected $_using = array();

	public function __construct($table, $type = NULL)
	{
		$this->_table = $table;

		if ($type !== NULL)
		{
			$this->_type = (string) $type;
		}
	}


	public function on($c1, $op, $c2)
	{
		if ( ! empty($this->_using))
		{
			throw new \Hanariu\Exception('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		$this->_on[] = array($c1, $op, $c2);

		return $this;
	}


	public function using($columns)
	{
		if ( ! empty($this->_on))
		{
			throw new \Hanariu\Exception('JOIN ... ON ... cannot be combined with JOIN ... USING ...');
		}

		$columns = func_get_args();

		$this->_using = array_merge($this->_using, $columns);

		return $this;
	}


	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		if ($this->_type)
		{
			$sql = strtoupper($this->_type).' JOIN';
		}
		else
		{
			$sql = 'JOIN';
		}

		$sql .= ' '.$db->quote_table($this->_table);

		if ( ! empty($this->_using))
		{
			$sql .= ' USING ('.implode(', ', array_map(array($db, 'quote_column'), $this->_using)).')';
		}
		else
		{
			$conditions = array();
			foreach ($this->_on as $condition)
			{
				list($c1, $op, $c2) = $condition;

				if ($op)
				{
					$op = ' '.strtoupper($op);
				}

				$conditions[] = $db->quote_column($c1).$op.' '.$db->quote_column($c2);
			}

			$sql .= ' ON ('.implode(' AND ', $conditions).')';
		}

		return $sql;
	}

	public function reset()
	{
		$this->_type =
		$this->_table = NULL;
		$this->_on = array();
	}

}
