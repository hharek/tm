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
				throw new Exception("Столбец «{$key}» отсутствует.", static::$schema, static::$table, static::$name);

			/* Проверяем столбец */
			static::_check_value($column, $value);
		}


		return true;
	}

	/**
	 * Проверить значение поля
	 *
	 * @param \TM\Column $column
	 * @param $value
	 */
	private static function _check_value (\TM\Column $column, $value)
	{
		/* null */
		if ($value === null)
		{
			if (!$column->null)
				throw new Exception("Не может быть задан как NULL.", static::$schema, static::$table, static::$name, $column);

			return;
		}

		/* empty */
		if (is_string($value) && trim($value) === "")
		{
			if (!$column->empty)
				throw new Exception("Указана пустая строка.", static::$schema, static::$table, static::$name, $column);

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