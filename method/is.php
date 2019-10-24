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

const SQL_IS_COLUMN =
<<<SQL
\t"{column}" = \${num}
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
	public static function is ($value, bool $exception = true) : bool
	{
		/* Мета */
		static::_meta();

		/* Нет первичных ключей */
		if (empty(static::$_primary))
			throw new \TM\Exception("Отсутствуют первичные ключи.", static::$schema, static::$table, static::$name);

		/* Преобразуем в массив [column => value] */
		if (is_scalar($value))
		{
			if (count(static::$_primary) > 1)
				throw new \TM\Exception("Необходимо указать несколько первичных ключей.", static::$schema, static::$table, static::$name);

			$column = static::$_primary[0]->column;
			$data[$column] = $value;
		}
		else
		{
			$data = $value;
		}

		/* Проверяем */
		static::check($data);

		/* Подготавливаем */
		$data = static::prepare($data, false);

		/* Формируем запрос */
		$column_ar = [];
		$num = 1;
		foreach ($data as $column => $value)
			$column_ar[] = strtr(\TM\SQL_IS_COLUMN, ["{column}" => $column, "{num}" => $num++]);

		$sql_column = implode(" AND\n", $column_ar);
		$query = strtr(\TM\SQL_IS,
		[
			"{table}" => static::$table,
			"{column}" => $sql_column
		]);

		/* Отладка */
		if (static::$debug)
		{
			static::_debug_query($query, array_values($data));
			return true;
		}

		/* Выполнить запрос */
		if (!static::$db_conn)
			throw new \Exception("IS. Невозможно выполнить запрос. Не назначен ресурс подключения.");

		$result = pg_query_params(static::$db_conn, $query, array_values($data));
		if ($result === false)
			throw new \Exception("IS. Ошибка при выполнении запроса.");

		$count = pg_num_rows($result);

		pg_free_result($result);

		/* Вывод */
		if ($count === 0)
		{
			if ($exception)
			{
				if (count($data) === 1)
				{
					foreach ($data as $column => $value)
						throw new \Exception("«" . static::$name . "» с параметром «" . $column . "» = «" . $value . "» отсутствует.");
				}
				else
				{
					$column_value = [];
					foreach ($data as $column => $value)
						$column_value[] = "«" . $column . "» = «" . $value . "»";

					throw new \Exception("«" . static::$name . "» с параметрами: " . implode(", ", $column_value) . " отсутствует.");
				}
			}
			else
			{
				return false;
			}
		}

		return true;
	}
}
?>