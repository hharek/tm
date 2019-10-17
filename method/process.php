<?php
namespace TM\Method;

/**
 * Обрабатываем данные после запроса
 */
trait Process
{
	use \TM\Table_Params;

	/**
	 * Обрабатываем данные после запроса
	 *
	 * @param array $data
	 */
	public static function process (array $data) : array
	{
		foreach ($data as $key => $value)
		{
			/* Определяем столбец */
			$column = null;
			foreach (static::$columns as $c)
			{
				if ($c->column === $key)
				{
					$column = $c;
					break;
				}
			}

			if ($column === null)
				break;

			/* process */
			if ($column->process !== null && $value !== null)
				$data[$key] = call_user_func($column->process, $value);
		}

		return $data;
	}
}
?>