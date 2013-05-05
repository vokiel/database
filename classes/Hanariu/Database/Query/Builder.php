<?php namespace Hanariu\Database\Query;

abstract class Builder extends \Hanariu\Database\Query {

	protected function _compile_join(\Hanariu\Database $db, array $joins)
	{
		$statements = array();

		foreach ($joins as $join)
		{
			$statements[] = $join->compile($db);
		}

		return implode(' ', $statements);
	}

	protected function _compile_conditions(\Hanariu\Database $db, array $conditions)
	{
		$last_condition = NULL;

		$sql = '';
		foreach ($conditions as $group)
		{
			// Process groups of conditions
			foreach ($group as $logic => $condition)
			{
				if ($condition === '(')
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						$sql .= ' '.$logic.' ';
					}

					$sql .= '(';
				}
				elseif ($condition === ')')
				{
					$sql .= ')';
				}
				else
				{
					if ( ! empty($sql) AND $last_condition !== '(')
					{
						$sql .= ' '.$logic.' ';
					}

					// Split the condition
					list($column, $op, $value) = $condition;

					if ($value === NULL)
					{
						if ($op === '=')
						{
							$op = 'IS';
						}
						elseif ($op === '!=')
						{
							$op = 'IS NOT';
						}
					}

					$op = strtoupper($op);

					if ($op === 'BETWEEN' AND is_array($value))
					{
						list($min, $max) = $value;

						if ((is_string($min) AND array_key_exists($min, $this->_parameters)) === FALSE)
						{
							$min = $db->quote($min);
						}

						if ((is_string($max) AND array_key_exists($max, $this->_parameters)) === FALSE)
						{
							$max = $db->quote($max);
						}

						$value = $min.' AND '.$max;
					}
					elseif ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE)
					{
						$value = $db->quote($value);
					}

					if ($column)
					{
						if (is_array($column))
						{
							$column = $db->quote_identifier(reset($column));
						}
						else
						{
							$column = $db->quote_column($column);
						}
					}

					$sql .= trim($column.' '.$op.' '.$value);
				}

				$last_condition = $condition;
			}
		}

		return $sql;
	}

	protected function _compile_set(Database $db, array $values)
	{
		$set = array();
		foreach ($values as $group)
		{
			list ($column, $value) = $group;
			$column = $db->quote_column($column);

			if ((is_string($value) AND array_key_exists($value, $this->_parameters)) === FALSE)
			{
				$value = $db->quote($value);
			}

			$set[$column] = $column.' = '.$value;
		}

		return implode(', ', $set);
	}

	protected function _compile_group_by(\Hanariu\Database $db, array $columns)
	{
		$group = array();

		foreach ($columns as $column)
		{
			if (is_array($column))
			{
				$column = $db->quote_identifier(end($column));
			}
			else
			{
				$column = $db->quote_column($column);
			}

			$group[] = $column;
		}

		return 'GROUP BY '.implode(', ', $group);
	}

	protected function _compile_order_by(\Hanariu\Database $db, array $columns)
	{
		$sort = array();
		foreach ($columns as $group)
		{
			list ($column, $direction) = $group;

			if (is_array($column))
			{
				$column = $db->quote_identifier(end($column));
			}
			else
			{
				$column = $db->quote_column($column);
			}

			if ($direction)
			{
				$direction = ' '.strtoupper($direction);
			}

			$sort[] = $column.$direction;
		}

		return 'ORDER BY '.implode(', ', $sort);
	}

	abstract public function reset();

}
