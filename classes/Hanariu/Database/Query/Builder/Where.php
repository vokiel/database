<?php namespace Hanariu\Database\Query\Builder;

abstract class Where extends \Hanariu\Database\Query\Builder {

	protected $_where = array();
	protected $_order_by = array();
	protected $_limit = NULL;

	public function where($column, $op, $value)
	{
		return $this->and_where($column, $op, $value);
	}

	public function and_where($column, $op, $value)
	{
		$this->_where[] = array('AND' => array($column, $op, $value));
		return $this;
	}

	public function or_where($column, $op, $value)
	{
		$this->_where[] = array('OR' => array($column, $op, $value));
		return $this;
	}

	public function where_open()
	{
		return $this->and_where_open();
	}

	public function and_where_open()
	{
		$this->_where[] = array('AND' => '(');
		return $this;
	}

	public function or_where_open()
	{
		$this->_where[] = array('OR' => '(');
		return $this;
	}

	public function where_close()
	{
		return $this->and_where_close();
	}

	public function where_close_empty()
	{
		$group = end($this->_where);

		if ($group AND reset($group) === '(')
		{
			array_pop($this->_where);
			return $this;
		}

		return $this->where_close();
	}

	public function and_where_close()
	{
		$this->_where[] = array('AND' => ')');
		return $this;
	}

	public function or_where_close()
	{
		$this->_where[] = array('OR' => ')');
		return $this;
	}

	public function order_by($column, $direction = NULL)
	{
		$this->_order_by[] = array($column, $direction);
		return $this;
	}


	public function limit($number)
	{
		$this->_limit = $number;
		return $this;
	}

}
