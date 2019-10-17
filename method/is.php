<?php
namespace TM;

const SQL_IS =
<<<SQL
SELECT 
	true
FROM 
	{table}
WHERE 
	{column}
SQL;

namespace TM\Method;

/**
 * Проверка на существование по первичному ключу
 */
trait Is
{
	use \TM\Table_Params,
		_Meta;

	/**
	 * Проверка на существование по первичному ключу
	 *
	 * @param $value
	 */
	public static function is ($value)
	{
		/* Мета */
		static::_meta();

		/* Нет первичных ключей */
		if (empty(static::$_primary))
			throw new \TM\Exception("Отсутствуют первичные ключи.", static::$schema, static::$table, static::$name);

		/* Преобразуем в массив [column => value] */
		$data = [];
		if (is_scalar($value))
		{
			if (count(static::$_primary) > 1)
				throw new \TM\Exception("Необходимо указать несколько первичных ключей.", static::$schema, static::$table, static::$name);

			$column = static::$_primary[0]->column;
			$data[$column] = $value;
		}

		/* Проверяем */
		static::check($data);

		/* Подготавливаем */

		

		/* Формируем запрос */

	}
}
?>