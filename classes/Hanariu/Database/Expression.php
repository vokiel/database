<?php namespace Hanariu\Database;

class Expression {

	protected $_parameters;
	protected $_value;

	public function __construct($value, $parameters = array())
	{
		$this->_value = $value;
		$this->_parameters = $parameters;
	}

	public function bind($param, & $var)
	{
		$this->_parameters[$param] =& $var;
		return $this;
	}

	public function param($param, $value)
	{
		$this->_parameters[$param] = $value;

		return $this;
	}

	public function parameters(array $params)
	{
		$this->_parameters = $params + $this->_parameters;
		return $this;
	}

	public function value()
	{
		return (string) $this->_value;
	}

	public function __toString()
	{
		return $this->value();
	}

	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{
			$db = \Hanariu\Database::instance($db);
		}

		$value = $this->value();

		if ( ! empty($this->_parameters))
		{
			$params = array_map(array($db, 'quote'), $this->_parameters);
			$value = strtr($value, $params);
		}

		return $value;
	}

}
