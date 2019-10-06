<?php
namespace TM\Method;

/**
 * Получить информацию по таблице
 */
trait _Info
{
	use \TM\Table_Params,
		_Table,
		DB_Conn;

	/**
	 * Получить информацию по таблице и по колонкам табилицы
	 *
	 * @param string $schema
	 * @param string $table
	 * @return array
	 */
	private static function _info (string $schema, string $table) : array
	{
		$data = [];
		$data['schema'] = $schema;
		$data['table'] = $table;

		if (!static::$db_conn)
			throw new \Exception("INFO. Невозможно выполнить запрос. Не назначен ресурс подключения.");

		/* Данные по колонкам */
		$query =
<<<SQL
SELECT 
	*
FROM 
	"information_schema"."columns"
WHERE
	"table_schema" = $1 AND
	"table_name" = $2
SQL;

		$result = pg_query_params(static::$db_conn, $query, [$schema, $table]);
		$column = pg_fetch_all($result);
		if (empty($column))
			throw new \Exception("INFO. Указана несуществующая таблица или у таблицы отсутствуют столбцы.");
		pg_free_result($result);

		/* Комментарии по таблице и по колонкам */
		$query =
<<<SQL
SELECT
	*
FROM
	"pg_catalog"."pg_description"
WHERE
	"objoid" = $1::regclass::oid
SQL;
		$result = pg_query_params(static::$db_conn, $query, [$schema . "." . $table]);
		$comment = pg_fetch_all($result);
		pg_free_result($result);

		/* Комментарий к таблице */
		$data['comment'] = "";
		if (!empty($comment))
		{
			foreach ($comment as $c)
			{
				if ((int)$c['objsubid'] == 0)
				{
					$data['comment'] = $c['description'];
				}
			}
		}

		/* Добавляем к колонкам комментарии */
		foreach ($column as &$col)
		{
			$col['comment'] = "";
			if (!empty($comment))
			{
				foreach ($comment as $c)
				{
					if ($col['ordinal_position'] == $c['objsubid'])
						$col['comment'] = $c['description'];
				}
			}
		}
		unset($col);

		/* Первичные ключи */
		$primary = static::_primary($schema, $table);
		foreach ($column as &$col)
		{
			$col['primary'] = false;
			if (in_array($col['column_name'], $primary))
			{
				$col['primary'] = true;

				/* Первичный ключ состоит из одного или более столбцов */
				if (count($primary) > 1)
					$col['primary_column'] = $primary;
			}
		}
		unset($col);

		/* Уникальные ключи */
		$unique = static::_unique($schema, $table);
		foreach ($column as &$col)
		{
			$col['unique'] = false;
			foreach ($unique as $unique_column)
			{
				if (in_array($col['column_name'], $unique_column))
				{
					$col['unique'] = true;

					/* Уникальный ключ состоит из одного или более столбцов */
					if (count($unique_column) > 1)
						$col['unique_column'] = $unique_column;
				}
			}
		}
		unset($col);

		/* Неуникальные индексы */
		$index = static::_index($schema, $table);
		foreach ($column as &$col)
		{
			$col['index'] = false;
			foreach ($index as $index_column)
			{
				if (in_array($col['column_name'], $index_column))
				{
					$col['index'] = true;

					/* Уникальный ключ состоит из одного или более столбцов */
					if (count($index_column) > 1)
						$col['index_column'] = $index_column;
				}
			}
		}
		unset($col);

		$data['column'] = $column;
		return $data;
	}

	/**
	 * Первичные ключи по таблице
	 *
	 * @param string $schema
	 * @param string $table
	 * @return array
	 */
	private static function _primary (string $schema, string $table) : array
	{
		$query =
<<<SQL
SELECT
	"a"."attname"
FROM
	"pg_index" as "i" JOIN
	"pg_attribute" as "a" ON (i.indrelid = a.attrelid AND a.attnum = ANY(i.indkey))
WHERE
	"i"."indrelid" = $1::regclass AND
	"i"."indisprimary" = true
SQL;
		$result = pg_query_params(static::$db_conn, $query, [$schema . "." . $table]);
		$primary = pg_fetch_all_columns($result, 0);
		pg_free_result($result);
		return $primary;
	}

	/**
	 * Уникальные ключи по таблице
	 *
	 * @param string $schema
	 * @param string $table
	 * @return array
	 */
	private static function _unique (string $schema, string $table) : array
	{
		$query =
<<<SQL
SELECT
	"a"."attname",
	"i"."indkey"
FROM
	"pg_index" as "i" JOIN
	"pg_attribute" as "a" ON (i.indrelid = a.attrelid AND a.attnum = ANY(i.indkey))
WHERE
	"i"."indrelid" = $1::regclass AND
	"i"."indisunique" = true AND
	"i"."indisprimary" = false
SQL;
		$result = pg_query_params(static::$db_conn, $query, [$schema . "." . $table]);
		$pg_index = pg_fetch_all($result);
		pg_free_result($result);

		$unique = [];

		if ($pg_index !== false)
		{
			foreach ($pg_index as $i)
			{
				$key = $i['indkey'];
				if (empty($unique[$key]))
					$unique[$key] = [];

				$unique[$key][] = $i['attname'];
			}

			$unique = array_values($unique);
		}

		return $unique;
	}

	/**
	 * Не уникальные индексы
	 *
	 * @param string $schema
	 * @param string $table
	 * @return array
	 */
	private static function _index (string $schema, string $table) : array
	{
		$query =
<<<SQL
SELECT
	"a"."attname",
	"i"."indkey"
FROM
	"pg_index" as "i" JOIN
	"pg_attribute" as "a" ON (i.indrelid = a.attrelid AND a.attnum = ANY(i.indkey))
WHERE
	"i"."indrelid" = $1::regclass AND
	"i"."indisunique" = false AND
	"i"."indisprimary" = false
SQL;
		$result = pg_query_params(static::$db_conn, $query, [$schema . "." . $table]);
		$pg_index = pg_fetch_all($result);
		pg_free_result($result);

		$index = [];
		if ($pg_index !== false)
		{
			foreach ($pg_index as $i)
			{
				$key = $i['indkey'];
				if (empty($index[$key]))
					$index[$key] = [];

				$index[$key][] = $i['attname'];
			}

			$index = array_values($index);
		}

		return $index;
	}
}
?>