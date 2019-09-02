<?php
namespace TM;

/* Шаблоны SQL */
const SQL_CREATE =
<<<SQL
{drop_table}
CREATE TABLE {table}
(
{fields}
{primary}
{unique}
);
{comment_table}
{comment_column}
SQL;

const SQL_CREATE_COLUMN =
<<<SQL
{column} {type}{null}{default}
SQL;

const SQL_CREATE_DROP_TABLE =
<<<SQL
DROP TABLE IF EXISTS {table} CASCADE;
SQL;

namespace TM\Method;

/**
 * Создание таблицы
 */
trait Create
{
	use _Meta,
		_Table;

	/**
	 * @var string
	 */
	public static $schema = "public";

	/**
	 * @var string
	 */
	public static $table;

	/**
	 * @var string
	 */
	public static $name;

	/**
	 * @var \TM\Column[]
	 */
	public static $columns = [];

	/**
	 * @var \TM\Column
	 */
	protected static $_primary;

	/**
	 * @var \TM\Column[]
	 */
	protected static $_unique = [];

	/**
	 * Создание таблицы
	 *
	 * @param bool $drop_if_exist
	 */
	public static function create (bool $drop_if_exist = false)
	{
		static::_meta();

		/* DROP TABLE */
		$sql_drop_table = "";
		if ($drop_if_exist)
			$sql_drop_table = strtr(\TM\SQL_CREATE_DROP_TABLE, ["{table}" => static::_table(true)]) . "\n";

		/* Столбцы */
		$sql_fileds = "";
		foreach (static::$columns as $c)
		{
			$sql_fileds .= "\t";
			$sql_fileds .= strtr(\TM\SQL_CREATE_COLUMN,
			[
				"{column}" 	=> '"' . $c->column . '"',
				"{type}" 	=> $c->type_sql,
				"{null}" 	=> $c->null ? "" : " NOT NULL",
				"{default}" => static::_create_default_sql($c)
			]);
			$sql_fileds .= ",\n";
		}

		/* Первичный ключ */
		$primary_column = static::$_primary->column;
		$sql_primary =
<<<SQL
\tPRIMARY KEY ("{$primary_column}")
SQL;

		/* Уникальные ключи */
		$sql_unique = "";
		foreach (static::$_unique as $u)
		{
			$unique_columns = array_column((array)$u, "column");
			$sql_unique_columns = implode('","', $unique_columns);
			$sql_unique .=
<<<SQL
\tUNIQUE ({$sql_unique_columns})
SQL;
		}


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
			return "DEFAULT" . $column->default_sql;

		/* DEFAULT NULL */
		if ($column->default === null)
		{
			if ($column->null)
				return "DEFAULT NULL";
			else
				return "";
		}

		/* DEFAULT */
		if ($column->default !== null)
		{
			switch ($column->type_php)
			{
				case "string":
					return "DEFAULT '" . pg_escape_string($column->default) . "'";
					break;

				case "int":
				case "integer":
				case "float":
				case "double":
				case "real":
					return "DEFAULT " . $column->default;
					break;

				case "bool":
				case "boolean":
					if (in_array($column->default, [true, 1]) || in_array($column->default, \TM\PGSQL_BOOLEAN_TRUE))
						return "DEFAULT true";
					elseif (in_array($column->default, [false, 0]) || in_array($column->default, \TM\PGSQL_BOOLEAN_FALSE))
						return "DEFAULT false";
					break;

				case "array":
				case "object":
					return "DEFAULT '" . pg_escape_string(json_encode($column->default, \TM\PREPARE_JSON_ENCODE)) . "'::jsonb";
					break;
			}
		}

		return "";
	}
}
?>