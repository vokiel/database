<?php namespace Hanariu\Database\Query\Builder;

class Select extends \Hanariu\Database\Query\Builder\Where {

	protected $_select = array();
	protected $_distinct = FALSE;
	protected $_from = array();
	protected $_join = array();
	protected $_group_by = array();
	protected $_having = array();
	protected $_offset = NULL;
	protected $_union = array();
	protected $_last_join;


	public function __construct(array $columns = NULL)
	{
		if ( ! empty($columns))
		{
			$this->_select = $columns;
		}

		parent::__construct(\Hanariu\Database::SELECT, '');
	}


	public function distinct($value)
	{
		$this->_distinct = (bool) $value;

		return $this;
	}


	public function select($columns = NULL)
	{
		$columns = func_get_args();
		$this->_select = array_merge($this->_select, $columns);
		return $this;
	}


	public function select_array(array $columns)
	{
		$this->_select = array_merge($this->_select, $columns);
		return $this;
	}


	public function from($tables)
	{
		$tables = func_get_args();
		$this->_from = array_merge($this->_from, $tables);
		return $this;
	}


	public function join($table, $type = NULL)
	{
		$this->_join[] = $this->_last_join = new \Hanariu\Database\Query\Builder\Join($table, $type);
		return $this;
	}


	public function on($c1, $op, $c2)
	{
		$this->_last_join->on($c1, $op, $c2);
		return $this;
	}


	public function using($columns)
	{
		$columns = func_get_args();
		call_user_func_array(array($this->_last_join, 'using'), $columns);
		return $this;
	}


	public function group_by($columns)
	{
		$columns = func_get_args();
		$this->_group_by = array_merge($this->_group_by, $columns);
		return $this;
	}


	public function having($column, $op, $value = NULL)
	{
		return $this->and_having($column, $op, $value);
	}

	public function and_having($column, $op, $value = NULL)
	{
		$this->_having[] = array('AND' => array($column, $op, $value));

		return $this;
	}

	public function or_having($column, $op, $value = NULL)
	{
		$this->_having[] = array('OR' => array($column, $op, $value));

		return $this;
	}

	public function having_open()
	{
		return $this->and_having_open();
	}

	public function and_having_open()
	{
		$this->_having[] = array('AND' => '(');

		return $this;
	}

	public function or_having_open()
	{
		$this->_having[] = array('OR' => '(');

		return $this;
	}

	public function having_close()
	{
		return $this->and_having_close();
	}

	public function and_having_close()
	{
		$this->_having[] = array('AND' => ')');

		return $this;
	}

	public function or_having_close()
	{
		$this->_having[] = array('OR' => ')');

		return $this;
	}


	public function union($select, $all = TRUE)
	{
		if (is_string($select))
		{
			$select = \Hanariu\DB::select()->from($select);
		}
		if ( ! $select instanceof \Hanariu\Database\Query\Builder\Select)
			throw new \Hanariu\Exception('first parameter must be a string or an instance of \Hanariu\Database\Query\Builder\Select');
		$this->_union []= array('select' => $select, 'all' => $all);
		return $this;
	}


	public function offset($number)
	{
		$this->_offset = $number;

		return $this;
	}


	public function compile($db = NULL)
	{
		if ( ! is_object($db))
		{

			$db = \Hanariu\Database::instance($db);
		}

		$quote_column = array($db, 'quote_column');
		$quote_table = array($db, 'quote_table');
		$query = 'SELECT ';

		if ($this->_distinct === TRUE)
		{
			$query .= 'DISTINCT ';
		}

		if (empty($this->_select))
		{
			$query .= '*';
		}
		else
		{
			$query .= implode(', ', array_unique(array_map($quote_column, $this->_select)));
		}

		if ( ! empty($this->_from))
		{
			$query .= ' FROM '.implode(', ', array_unique(array_map($quote_table, $this->_from)));
		}

		if ( ! empty($this->_join))
		{
			$query .= ' '.$this->_compile_join($db, $this->_join);
		}

		if ( ! empty($this->_where))
		{
			$query .= ' WHERE '.$this->_compile_conditions($db, $this->_where);
		}

		if ( ! empty($this->_group_by))
		{
			$query .= ' '.$this->_compile_group_by($db, $this->_group_by);
		}

		if ( ! empty($this->_having))
		{
			$query .= ' HAVING '.$this->_compile_conditions($db, $this->_having);
		}

		if ( ! empty($this->_order_by))
		{
			$query .= ' '.$this->_compile_order_by($db, $this->_order_by);
		}

		if ($this->_limit !== NULL)
		{
			$query .= ' LIMIT '.$this->_limit;
		}

		if ($this->_offset !== NULL)
		{
			$query .= ' OFFSET '.$this->_offset;
		}

		if ( ! empty($this->_union))
		{
			foreach ($this->_union as $u) {
				$query .= ' UNION ';
				if ($u['all'] === TRUE)
				{
					$query .= 'ALL ';
				}
				$query .= $u['select']->compile($db);
			}
		}

		$this->_sql = $query;

		return parent::compile($db);
	}

	public function reset()
	{
		$this->_select   =
		$this->_from     =
		$this->_join     =
		$this->_where    =
		$this->_group_by =
		$this->_having   =
		$this->_order_by =
		$this->_union = array();
		$this->_distinct = FALSE;
		$this->_limit     =
		$this->_offset    =
		$this->_last_join = NULL;
		$this->_parameters = array();
		$this->_sql = NULL;
		return $this;
	}

}
