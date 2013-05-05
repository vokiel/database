<?php namespace Hanariu\Database;

abstract class Result implements \Countable, \Iterator, \SeekableIterator, \ArrayAccess {

	protected $_query;
	protected $_result;
	protected $_total_rows  = 0;
	protected $_current_row = 0;
	protected $_as_object;
	protected $_object_params = NULL;

	public function __construct($result, $sql, $as_object = FALSE, array $params = NULL)
	{
		$this->_result = $result;
		$this->_query = $sql;

		if (is_object($as_object))
		{
			$as_object = get_class($as_object);
		}

		$this->_as_object = $as_object;

		if ($params)
		{
			$this->_object_params = $params;
		}
	}

	abstract public function __destruct();

	public function cached()
	{
		return new Hanariu\Database\Result\Cached($this->as_array(), $this->_query, $this->_as_object);
	}

	public function as_array($key = NULL, $value = NULL)
	{
		$results = array();

		if ($key === NULL AND $value === NULL)
		{
			// Indexed rows

			foreach ($this as $row)
			{
				$results[] = $row;
			}
		}
		elseif ($key === NULL)
		{

			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[] = $row->$value;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[] = $row[$value];
				}
			}
		}
		elseif ($value === NULL)
		{

			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[$row->$key] = $row;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[$row[$key]] = $row;
				}
			}
		}
		else
		{

			if ($this->_as_object)
			{
				foreach ($this as $row)
				{
					$results[$row->$key] = $row->$value;
				}
			}
			else
			{
				foreach ($this as $row)
				{
					$results[$row[$key]] = $row[$value];
				}
			}
		}

		$this->rewind();

		return $results;
	}

	public function get($name, $default = NULL)
	{
		$row = $this->current();

		if ($this->_as_object)
		{
			if (isset($row->$name))
				return $row->$name;
		}
		else
		{
			if (isset($row[$name]))
				return $row[$name];
		}

		return $default;
	}

	public function count()
	{
		return $this->_total_rows;
	}

	public function offsetExists($offset)
	{
		return ($offset >= 0 AND $offset < $this->_total_rows);
	}

	public function offsetGet($offset)
	{
		if ( ! $this->seek($offset))
			return NULL;

		return $this->current();
	}

	final public function offsetSet($offset, $value)
	{
		throw new \Hanariu\Exception('Database results are read-only');
	}

	final public function offsetUnset($offset)
	{
		throw new \Hanariu\Exception('Database results are read-only');
	}

	public function key()
	{
		return $this->_current_row;
	}

	public function next()
	{
		++$this->_current_row;
		return $this;
	}

	public function prev()
	{
		--$this->_current_row;
		return $this;
	}

	public function rewind()
	{
		$this->_current_row = 0;
		return $this;
	}

	public function valid()
	{
		return $this->offsetExists($this->_current_row);
	}

}
