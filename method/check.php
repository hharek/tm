<?php
namespace TM\Method;

use TM\Exception;

/**
 * Проверить данные
 */
trait Check
{
	use \TM\Table_Params;

	/**
	 * Проверить данные
	 *
	 * @param array $data
	 */
	public static function check (array $data)
	{
		/* Не указаны данные */
		if (empty($data))
			return;

		/* Проверка */
		$err = [];
		foreach ($data as $key => $value)
		{
			/* Определяем столбец */
			$column = null;
			foreach (static::$columns as $c)
			{
				if ($c->column === $key)
					$column = $c;
			}

			if ($column === null)
				$err[] = new Exception("Столбец «{$key}» отсутствует.", static::$schema, static::$table, static::$name);

			/* Проверяем столбец */
			static::_check_value($column, $value);
		}

		if (empty($err))
			throw new \TM\Exception_Many($err);

		return true;
	}

	/**
	 * Проверить значение поля
	 *
	 * @param \TM\Column $column
	 * @param $value
	 * @param \TM\Exception[] $err
	 */
	private static function _check_value (\TM\Column $column, $value, &$err = null)
	{
		/* null */
		if (!$column->null && $value === null)
		{
			if ($err !== null)
				throw new Exception("Не может быть задан как NULL.", static::$schema, static::$table, static::$name, $column);
			else
				$err[$column->column] = new Exception("Не может быть задан как NULL.", static::$schema, static::$table, static::$name, $column);

			return;
		}

		/* empty */
		if (!$column->empty && is_string($value) && trim($value) === "")
		{
			if ($err !== null)
				throw new Exception("Указана пустая строка.", static::$schema, static::$table, static::$name, $column);
			else
				$err[$column->column] = new Exception("Указана пустая строка.", static::$schema, static::$table, static::$name, $column);

			return;
		}

		/* check */
		if ($column->check !== null)
		{
			try
			{
				call_user_func(get_class($column) . "::check", $value, $column);
			}
			catch (\Exception $e)
			{
				throw new Exception($e->getMessage(), static::$schema, static::$table, static::$name, $column);
			}
		}
	}
}
?>