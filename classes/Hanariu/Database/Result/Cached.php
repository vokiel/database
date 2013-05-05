<?php namespace Hanariu\Database\Result;

class Cached extends \Hanariu\Database\Result {

	public function __construct(array $result, $sql, $as_object = NULL)
	{
		parent::__construct($result, $sql, $as_object);
		$this->_total_rows = count($result);
	}

	public function __destruct()
	{
	}

	public function cached()
	{
		return $this;
	}

	public function seek($offset)
	{
		if ($this->offsetExists($offset))
		{
			$this->_current_row = $offset;

			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function current()
	{
		return $this->valid() ? $this->_result[$this->_current_row] : NULL;
	}

}
