<?php
namespace TM;

/* Шаблоны SQL */
const SQL_CREATE =
<<<SQL
{drop_table}
CREATE TABLE {table}
(
{column}{primary}{unique}
);

{comment_table}
{comment_column}
SQL;

const SQL_CREATE_COLUMN =
<<<SQL
\t{column} {type}{null}{default}
SQL;

const SQL_CREATE_DROP_TABLE =
<<<SQL
DROP TABLE IF EXISTS {table} CASCADE;
SQL;

const SQL_CREATE_PRIMARY =
<<<SQL
\tPRIMARY KEY ("{column}")
SQL;

const SQL_CREATE_UNIQUE =
<<<SQL
\tUNIQUE ({column})
SQL;

const SQL_CREATE_COMMENT_TABLE =
<<<SQL
COMMENT ON TABLE {table} IS '{comment}';
SQL;

const SQL_CREATE_COMMENT_COLUMN =
<<<SQL
COMMENT ON COLUMN {table}."{column}" IS '{comment}';
SQL;

namespace TM\Method;

/**
 * Создание таблицы
 */
trait Create
{
	use \TM\Table_Params,
		_Table,
		TCheck,
		_Meta,
		Debug,
		DB_Conn;

	/**
	 * Создание таблицы
	 *
	 * @param bool $drop_if_exist Удалять таблицу если существует перед CREATE
	 * @param bool $tm_comment Добавлять специальные комментарии
	 */
	public static function create (bool $drop_if_exist = false, bool $tm_comment = true)
	{
		/* Проверяем правильность заполнение класса */
		static::tcheck();

		/* Собираем мету */
		static::_meta();

		/* DROP TABLE */
		$sql_drop_table = "";
		if ($drop_if_exist)
			$sql_drop_table = strtr(\TM\SQL_CREATE_DROP_TABLE, ["{table}" => static::_table(true)]);

		/* Столбцы */
		$sql_column_part = [];
		foreach (static::$columns as $c)
		{
			$sql_column_part[] = strtr(\TM\SQL_CREATE_COLUMN,
			[
				"{column}" 	=> '"' . $c->column . '"',
				"{type}" 	=> $c->type_sql,
				"{null}" 	=> $c->null ? "" : " NOT NULL",
				"{default}" => static::_create_default_sql($c)
			]);
		}
		$sql_column = implode(",\n", $sql_column_part);

		/* Первичный ключ */
		$sql_primary = ",\n" . strtr(\TM\SQL_CREATE_PRIMARY, ["{column}" => static::$_primary->column]);

		/* Уникальные ключи */
		$sql_unique_part = [];
		foreach (static::$_unique as $u)
		{
			$unique_column = array_column((array)$u, "column");
			$sql_unique_part[] = strtr(\TM\SQL_CREATE_UNIQUE,
			[
				"{column}" => '"' . implode('", "', $unique_column) . '"'
			]);
		}
		$sql_unique = ",\n" . implode(",\n", $sql_unique_part);

		/* Комментарий таблицы */
		$comment = pg_escape_string(static::$name);

		if ($tm_comment)	/* Специальный комментарий */
		{
			$table_data =
			[
				"schema" => static::$schema,
				"table" => static::$table,
				"name" => static::$name,
			];
			$comment .= " | " . pg_escape_string(json_encode($table_data, JSON_UNESCAPED_UNICODE));
		}

		$sql_comment_table = strtr(\TM\SQL_CREATE_COMMENT_TABLE,
		[
			"{table}" => static::_table(true),
			"{comment}" => $comment
		]);

		/* Комментарии по столбцам */
		$sql_comment_column = "";
		foreach (static::$columns as $c)
		{
			$comment = pg_escape_string($c->name);

			if ($tm_comment) /* Специальный комментарий по столбцу */
			{
				$data = static::_column_data_used($c);
				$comment .= " | " . pg_escape_string(json_encode($data, JSON_UNESCAPED_UNICODE));
			}

			/* SQL */
			$sql_comment_column .= strtr(\TM\SQL_CREATE_COMMENT_COLUMN,
			[
				"{table}" => static::_table(true),
				"{column}" => $c->column,
				"{comment}" => $comment
			]);
			$sql_comment_column .= "\n";
		}

		/* Общий SQL */
		$sql = strtr(\TM\SQL_CREATE,
		[
			"{drop_table}" => $sql_drop_table,
			"{table}" => static::_table(true),
			"{column}" => $sql_column,
			"{primary}" => $sql_primary,
			"{unique}" => $sql_unique,
			"{comment_table}" => $sql_comment_table,
			"{comment_column}" => $sql_comment_column,
		]);

		/* Отладка */
		if (static::$debug)
		{
			static::_debug_query($sql);
			return;
		}

		/* Выполнить запрос */
		if (!static::$db_conn)
			throw new \Exception("CREATE. Невозможно выполнить запрос. Не назначен ресурс подключения.");

		$result = pg_query(static::$db_conn, $sql);
		if ($result === false)
			throw new \Exception("CREATE. Ошибка при выполнении запроса.");

		pg_free_result($result);
	}

	/**
	 * Получить SQL
	 *
	 * @param \TM\Column
	 * @return string
	 */
	private static function _create_default_sql (\TM\Column $column) : string
	{
		/* DEFAULT SQL */
		if ($column->default_sql !== null)
			return " DEFAULT" . $column->default_sql;

		/* DEFAULT */
		if ($column->default !== null)
		{
			switch ($column->type_php)
			{
				case "string":
					return " DEFAULT '" . pg_escape_string($column->default) . "'";
					break;

				case "int":
				case "integer":
				case "float":
				case "double":
				case "real":
					return " DEFAULT " . $column->default;
					break;

				case "bool":
				case "boolean":
					if (in_array($column->default, [true, 1]) || in_array($column->default, \TM\PGSQL_BOOLEAN_TRUE))
						return " DEFAULT true";
					elseif (in_array($column->default, [false, 0]) || in_array($column->default, \TM\PGSQL_BOOLEAN_FALSE))
						return " DEFAULT false";
					break;

				case "array":
				case "object":
					return " DEFAULT '" . pg_escape_string(json_encode($column->default, \TM\PREPARE_JSON_ENCODE)) . "'::jsonb";
					break;
			}
		}

		return "";
	}

	/**
	 * Вычислить выбранные пользователем данные
	 *
	 * @param \TM\Column $column
	 * @return object
	 */
	private static function _column_data_used (\TM\Column $column) : object
	{
		$used = new \stdClass();
		$default = new \TM\Column();

		foreach ($column as $param => $value)
		{
			if ($column->{$param} !== $default->{$param})
				$used->{$param} = $value;
		}

		return $used;
	}
}
?>