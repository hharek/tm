<?php
namespace TM\Method;

use TM\Exception;

/**
 * Подготавливаем данные для запроса
 */
trait Prepare
{
	use \TM\Table_Params,
		_Meta;

	/**
	 * Подготавливаем данные для запроса
	 *
	 * @param array $data
	 * @param bool $check
	 * @return array
	 */
	public static function prepare (array $data, bool $check = true) : array
	{
		static::_meta();

		if ($check)
			static::check($data);

		foreach ($data as $key => $value)
		{
			/* Определяем столбец */
			$column = null;
			foreach (static::$columns as $c)
			{
				if ($c->column === $key)
					$column = $c;
			}

			/* prepare */
			if ($column->prepare !== null && $value !== null)
				$data[$key] = call_user_func($column->prepare, $value);
		}

		return $data;
	}
}
?>