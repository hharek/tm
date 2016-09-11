<?php
/**
 * Table Manager - управляющий таблицой
 */
class TM
{
	/**
	 * Ресурс подключения к БД
	 * 
	 * @var resource
	 */
	protected static $_db_conn;
	
	/**
	 * Наименование схемы
	 * 
	 * @var string
	 */
	protected static $_schema = "public";

	/**
	 * Таблица
	 * 
	 * @var string
	 */
	protected static $_table;
	
	/**
	 * Наименование
	 * 
	 * @var string
	 */
	protected static $_name;

	/**
	 * Поля
	 * 
	 * @var array
	 */
	protected static $_field = [];
	
	/**
	 * Поле с первичным ключом
	 * 
	 * @var array
	 */
	protected static $_primary = [];
	
	/**
	 * Поля сортировки
	 * 
	 * @var array
	 */
	protected static $_order = [];
	
	/**
	 * Массив уникальных ключей
	 * 
	 * @var array
	 */
	protected static $_unique = [];
	
	/**
	 * Массив внешних ключей
	 * 
	 * @var array
	 */
	protected static $_foreign = [];
	
	/**
	 * Идентификатор поля с типом ID
	 * 
	 * @var string
	 */
	protected static $_id;
	
	/**
	 * Шаблоны SQL
	 * 
	 * @var array
	 */
	protected static $_sql = 
	[
		"create" => 
<<<SQL
{drop_table}
{sequence_create}
CREATE TABLE "{table}"
(
{field}{primary}{unique}{foreign}
);\n
{sequence_owned}
{comment_table}
{comment_column}
SQL
,
		"drop_table" =>
<<<SQL
DROP TABLE IF EXISTS "{table}" CASCADE;\n
SQL
,
		"sequence_create" => 
<<<SQL
CREATE SEQUENCE "{table}_seq" RESTART;\n
SQL
,		
		"sequence_owned" => 
<<<SQL
ALTER SEQUENCE "{table}_seq" OWNED BY "{table}"."{id}";\n
SQL
,		
		"field" => 
<<<SQL
\t"{identified}" {sql_type}{null}{default}
SQL
,
		"primary" => 
<<<SQL
,\n\tCONSTRAINT "{table}_PK" PRIMARY KEY ({pk_field})
SQL
,
		"unique" => 
<<<SQL
,\n\tCONSTRAINT "{table}_{key}" UNIQUE ({un_field})
SQL
,
		"foreign" =>
<<<SQL
,\n\tCONSTRAINT "{table}_{key}" FOREIGN KEY ("{identified}")
\t\tREFERENCES "{fk_table}" ("{fk_field}") ON DELETE CASCADE\n
SQL
,
		"comment_table" => 
<<<SQL
COMMENT ON TABLE "{table}" IS '{name}';\n
SQL
,
		"comment_column" => 
<<<SQL
COMMENT ON COLUMN "{table}"."{identified}" IS '{name}';\n
SQL
,
		"get" => 
<<<SQL
SELECT
{field}
FROM
	"{table}"
WHERE
	"{column}" = $1
SQL
,
		"select" => 
<<<SQL
SELECT
{field}
FROM
	"{table}"
{where}
{order}
{limit}
SQL
	];
	
	/**
	 * Проверка на существование по ID
	 * 
	 * @param string $primary
	 * @param bool $exception
	 * @return bool
	 */
	public static function is(string $primary, bool $exception = true) : bool
	{
		/* Собираем данные */
		static::_meta();
		
		/* Проверка на соответствие типу */
		if (!TM_FType::check(static::$_primary['type'], $primary))
		{
			if ($exception)
			{
				throw new Exception("Поле «" . static::$_primary['name'] . "» задано неверно. " . TM_FType::get_last_error());
			}
			else
			{
				return false;
			}
		}
		
		/* Запрос */
		$table = static::$_table;
		$column = static::$_primary['identified'];
		$query = 
<<<SQL
SELECT 
	true
FROM 
	"{$table}"
WHERE 
	"{$column}" = $1
SQL;
		$result = pg_query_params(static::$_db_conn, $query, [$primary]);
		$count = pg_num_rows($result);
		if ($count === 0)
		{
			if ($exception)
			{
				throw new Exception("«" . static::$_name . "» с полем «" . static::$_primary['identified'] . "» = «" . $primary . "» отсутствует.");
			}
			else
			{
				return false;
			}
		}
		
		pg_free_result($result);
		
		return true;
	}
	
	/**
	 * Выборка по первичному ключу
	 * 
	 * @param string $primary
	 * @return array
	 */
	public static function get(string $primary) : array
	{
		/* Собираем данные */
		static::_meta();
		
		/* Проверка на существование */
		static::is($primary);
		
		/* Формируем запрос */
		$field = "";
		foreach (static::$_field as $key => $f)
		{
			/* Запятая */
			if ($key !== 0)
			{
				$field .= ",\n";
			}

			$field .= "\t" . TM_FType::get_sql_select($f);
		}
		
		/* Основной запрос */
		$data = 
		[
			"{field}" => $field,
			"{table}" => static::$_table,
			"{column}" => static::$_primary['identified']
		];
		$query = str_replace(array_keys($data), array_values($data), self::$_sql['get']);
		
		/* Запрос */
		$result = pg_query_params(self::$_db_conn, $query, [$primary]);
		
		/* Данные */
		$data = pg_fetch_assoc($result);
		if ($data === false)
		{
			$data = [];
		}
		
		pg_free_result($result);
		
		return $data;
	}
	
	/**
	 * Выборка по условию
	 * 
	 * @param array $where
	 * @param array $field
	 * @param int $page
	 * @param int $limit
	 * @param array $order
	 * @return array
	 */
	public static function select(array $where = [], array $field = [], int $page = 0, int $limit = 0, array $order = []) : array
	{
		/* Собираем данные */
		static::_meta();
		
		/* Проверка поля where */
		$sql_where = "";
		if (!empty($where))
		{
			$sql_where .= "WHERE\n";
			static::check($where);
			$param_num = 1;
			foreach ($where as $identified => $value)
			{
				if ($param_num !== 1)
				{
					$sql_where .= " AND\n";
				}
				
				$sql_where .= "\t\"{$identified}\" = \${$param_num}";
				$param_num++;
			}
		}
		
		/* Колонки для выборки */
		if (!empty($field))
		{
			if ($field !== array_values($field))
			{
				throw new Exception("Список полей задан неверно, необходимо указать список, к примеру «['ID', 'Name', 'Content']».");
			}
			
			/* Существуют ли указанные колонки */
			$tm_identified = array_column(static::$_field, "identified");
			foreach ($field as $identified)
			{
				if (!in_array($identified, $tm_identified))
				{
					throw new Exception("Таблица «" . static::$_table . "». Поле «{$identified}» отсутствует.");
				}
			}
		}
		/* Колонки для выборки. По умолчанию все */
		else
		{
			$field = array_column(static::$_field, "identified");
		}
		
		/* SQL для полей */
		$sql_field = "";
		foreach (static::$_field as $key => $f)
		{
			if (!in_array($f['identified'], $field))
			{
				continue;
			}
			
			if ($key !== 0)
			{
				$sql_field .= ",\n";
			}

			$sql_field .= "\t" . TM_FType::get_sql_select($f);
		}
		
		/* Order */
		if (!empty($order))
		{
			$tm_identified = array_column(static::$_field, "identified");
			foreach ($order as $identified => $value)
			{
				if (!in_array($identified, $tm_identified))
				{
					throw new Exception("Таблица «" . static::$_table . "». Поле для сортировки «{$identified}» отсутствует.");
				}
				
				$value = strtolower($value);
				if ($value !== "asc" and $value !== "desc")
				{
					throw new Exception("Таблица «" . static::$_table . "». Сортировка должна указываться как «asc» или «desc».");
				}
			}
		}
		/* По умолчанию */
		else
		{
			$order = static::$_order;
		}
		
		/* SQL ORDER */
		$sql_order = "";
		if (!empty($order))
		{
			$sql_order .= "ORDER BY\n"; $order_num = 0;
			foreach ($order as $identified => $value)
			{
				if ($order_num !== 0)
				{
					$sql_order .= ",\n";
				}
				$sql_order .= "\t\"{$identified}\" " . strtoupper($value);
				$order_num++;
			}
		}
		
		/* OFFSET LIMIT */
		$sql_limit = "";
		if ($page > 0 and $limit > 0)
		{
			$offset = ($page -1 ) * $limit;
			$sql_limit .= "OFFSET " . $offset . "\n";
			$sql_limit .= "LIMIT " . $limit;
		}
		
		/* Формируем запрос */
		$data = 
		[
			"{field}" => $sql_field,
			"{table}" => static::$_table,
			"{where}" => $sql_where,
			"{order}" => $sql_order,
			"{limit}" => $sql_limit
		];
		
		$query = str_replace(array_keys($data), array_values($data), self::$_sql['select']);
		
		/* Запрос */
		$result = pg_query_params(self::$_db_conn, $query, array_values($where));
		$data = pg_fetch_all($result);
		if ($data === false)
		{
			$data = [];
		}
		pg_free_result($result);
		
		return $data;
	}
	
	/**
	 * Проверка данных
	 * 
	 * @param array $data
	 * @param bool $exception
	 * @return array
	 */
	public static function check(array $data, bool $exception = false) : array
	{
		/* Массив с ошибками */
		$err = [];
		
		/* Отсутствуют данные */
		if (empty($data))
		{
			throw new Exception("Нет данных для проверки.");
		}
		
		/* Проверка */
		foreach ($data as $identified => $value)
		{
			/* Определяем данные по полю */
			$field = [];
			foreach (static::$_field as $f)
			{
				if ($f['identified'] === $identified)
				{
					$field = $f;
					break;
				}
			}
			
			if (empty($field))
			{
				throw new Exception("Таблица «" . static::$_table . "». Поле с идентификтором «{$identified}» отсутствует.");
			}
			
			/* Не является строкой */
			if (!is_scalar($value))
			{
				$error = "Поле «{$field['name']}» задано неверно. Не является строкой.";
				if ($exception)
				{
					throw new Exception($error);
				}
				else
				{
					$err[$identified] = $error;
					continue;
				}
			}
			$value = (string)$value;

			/* Не заполнено */
			if (!isset($field['default']) and trim($value) === "")
			{
				$error = "Поле «{$field['name']}» не заполнено.";
				if ($exception)
				{
					throw new Exception($error);
				}
				else
				{
					$err[$identified] = $error;
					continue;
				}	
			}
			
			/* Существует ли поле */
			if (!TM_FType::check($field['type'], $value))
			{
				$error = "Поле «{$field['name']}» задано неверно. " . TM_FType::get_last_error();
				if ($exception)
				{
					throw new Exception($error);
				}
				else
				{
					$err[$identified] = $error;
					continue;
				}	
			}
		}
		
		return $err;
	}

	/**
	 * Назначить ресурс подключения к БД
	 * 
	 * @param resource $resource
	 */
	public static function set_db_conn($resource)
	{
		self::$_db_conn = $resource;
	}

	/**
	 * Проверка структуры таблицы
	 */
	public static function check_struct()
	{
		foreach (static::$_field as $f)
		{
			static::_check_field_one($f);
		}
	}
	
	/**
	 * Собираем информацию по таблице
	 */
	public static function _meta()
	{
		/* Опустошаем элементы чтобы не переназначились другим классам */
		static::$_primary = [];
		static::$_order = [];
		static::$_unique = [];
		static::$_foreign = [];
		static::$_id = null;
		
		$un_num = 0;
		foreach (static::$_field as $f)
		{
			/* ID */
			if ($f['type'] === "id")
			{
				static::$_id = $f['identified'];
				$f['primary'] = true;
			}
			
			/* Primary */
			if (isset($f['primary']) and $f['primary'] === true)
			{	
				if (!empty(static::$_primary))
				{
					throw new Exception("Таблица «" . static::$_table . "». Два первичных ключа.");
				}
				
				static::$_primary = 
				[
					"identified" => $f['identified'],
					"name" => $f['name'],
					"type" => $f['type']
				];
			}
			
			/* Order */
			if (isset($f['order']))
			{
				static::$_order[$f['identified']] = strtolower($f['order']);
			}
			
			/* Unique */
			if (isset($f['unique']) and $f['unique'] === true)
			{
				if (!isset($f['unique_key']))
				{
					$unique_key = ["UN_" . $un_num];
					$un_num++;
				}
				else
				{
					if (is_scalar($f['unique_key']))
					{
						$unique_key = [$f['unique_key']];
					}
					else
					{
						$unique_key = $f['unique_key'];
					}
				}
				
				foreach ($unique_key as $un)
				{
					static::$_unique[$un][] = $f['identified'];
				}
			}
			
			/* Foreign */
			if (isset($f['foreign']))
			{
				static::$_foreign[] = 
				[
					"identified" => $f['identified'],
					"key" => "FK_" . $f['identified'],
					"table" => $f['foreign'][0],
					"field" => $f['foreign'][1]
				];
			}
		}
		
		/* Ещё проверки */
		if (empty(static::$_primary))
		{
			throw new Exception("Таблица «" . static::$_table . "». Не задан первичный ключ.");
		}
	}
	
	/**
	 * Получить SQL для создания таблицы
	 * 
	 * @param boolean $drop_if_exist
	 * @return string
	 */
	public static function _sql_create(bool $drop_if_exist = true) : string
	{
		/* Удалить таблицу при наличии */
		if ($drop_if_exist)
		{
			$drop_table = str_replace("{table}", static::$_table, self::$_sql['drop_table']);
		}
		
		/* Счётчик */
		$sequence_create = ""; $sequence_owned = "";
		if (!empty(static::$_id))
		{
			$sequence_create .= str_replace("{table}", static::$_table, self::$_sql['sequence_create']);
			$sequence_owned .= str_replace(["{table}", "{id}"], [static::$_table, static::$_id], self::$_sql['sequence_owned']);
		}
		
		/* Поля */
		$field = "";
		foreach (static::$_field as $key => $f)
		{
			/* Запятая */
			if ($key !== 0)
			{
				$field .= ",\n";
			}
			
			$data = [];
			$data['{identified}'] = $f['identified'];
			
			/* ID */
			if ($f['type'] === "id")
			{
				$data['{sql_type}'] = "int";
				$data['{null}'] = " NOT NULL";
				$data['{default}'] = " DEFAULT nextval('" . static::$_table . "_seq')";
				$field .= str_replace(array_keys($data), array_values($data), self::$_sql['field']);
				continue;
			}
			elseif ($f['type'] === "order")
			{
				$data['{sql_type}'] = "int";
				$data['{null}'] = " NOT NULL";
				$data['{default}'] = " DEFAULT currval('" . static::$_table . "_seq')";
				$field .= str_replace(array_keys($data), array_values($data), self::$_sql['field']);
				continue;
			}
			
			/* SQL тип */
			$data['{sql_type}'] = TM_FType::get_sql_create($f['type']);
			
			/* NULL */
			$data['{null}'] = "";
			if (isset($f['null']) and $f['null'] === true)
			{
				$data['{null}'] = " NULL";
			}
			else
			{
				$data['{null}'] = " NOT NULL";
			}
			
			/* DEFAULT */
			$data['{default}'] = "";
			if (array_key_exists('default', $f))
			{
				if (is_string($f['default']))
				{
					$data['{default}'] = " DEFAULT '" . $f['default'] . "'";
				}
				elseif (is_int($f['default']) or is_float($f['default']))
				{
					$data['{default}'] = " DEFAULT " . $f['default'];
				}
				elseif (is_bool($f['default']))
				{
					$f['default'] = $f['default'] ? "true" : "false";
					$data['{default}'] = " DEFAULT " . $f['default'];
				}
				elseif ($f['default'] === null)
				{
					$data['{default}'] = " DEFAULT NULL";
				}
			}
			
			$field .= str_replace(array_keys($data), array_values($data), self::$_sql['field']);
		}
		
		/* PRIMARY */
		$primary = "";
		if (!empty(static::$_primary))
		{
			$pk_field = '"' . static::$_primary['identified'] . '"';
			$primary = str_replace(['{table}', '{pk_field}'], [static::$_table, $pk_field], self::$_sql['primary']);
		}
		
		/* UNIQUE */
		$unique = "";
		if (!empty(static::$_unique))
		{
			foreach (static::$_unique as $key => $un_val)
			{
				$un_field = '"' . implode('","', $un_val) . '"';
				$data = 
				[
					"{table}" => static::$_table,
					"{key}" => $key,
					"{un_field}" => $un_field
				];
				$unique .= str_replace(array_keys($data), array_values($data), self::$_sql['unique']);
			}
		}
		
		/* Foreign */
		$foreign = "";
		if (!empty(static::$_foreign))
		{
			foreach (static::$_foreign as $v)
			{
				$data = 
				[
					"{table}" => static::$_table,
					"{key}" => $v['key'],
					"{identified}" => $v['identified'],
					"{fk_table}" => $v['table'],
					"{fk_field}" => $v['field']
				];
				
				$foreign .= str_replace(array_keys($data), array_values($data), self::$_sql['foreign']);
			}
		}
		
		/* COMMENT TABLE */
		$comment_table = "";
		if (!empty(static::$_name))
		{
			$comment_table .= str_replace(['{table}','{name}'], [static::$_table, static::$_name], self::$_sql['comment_table']);
		}
		
		/* COMMENT COLUMN */
		$comment_column = "";
		foreach (static::$_field as $f)
		{
			if (!empty($f['name']))
			{
				$data = 
				[
					"{table}" => static::$_table,
					"{identified}" => $f['identified'],
					"{name}" => pg_escape_string($f['name'])
				];
				
				$comment_column .= str_replace(array_keys($data), array_values($data), self::$_sql['comment_column']);
			}
		}
		
		/* Формируем общий SQL */
		$data = 
		[
			"{drop_table}" => $drop_table,
			"{sequence_create}" => $sequence_create,
			"{sequence_owned}" => $sequence_owned,
			"{table}" => static::$_table,
			"{field}" => $field,
			"{primary}" => $primary,
			"{unique}" => $unique,
			"{foreign}" => $foreign,
			"{comment_table}" => $comment_table,
			"{comment_column}" => $comment_column
		];
		
		$sql = str_replace(array_keys($data), array_values($data), self::$_sql['create']);
		
		return $sql;
	}
	
	/**
	 * Проверка одного поля
	 */
	protected static function _check_field_one($f)
	{
		$table = static::$_table;
		
		/* Идентификатор */
		if (empty($f['identified']))
		{
			throw new Exception("Таблица «{$table}». Не указан идентификатор у поля.");
		}
	
		if (!TM_FType::check("identified", $f['identified']))
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». Идентификатор задан неверно. " . TM_FType::get_last_error());
		}
		
		/* Наименование поля */
		if (empty($f['name']))
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». Наименование не задано.");
		}
		
		if (!TM_FType::check("string", $f['name']))
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». Наименование задано неверно. " . TM_FType::get_last_error());
		}

		/* Тип */
		if (empty($f['type']))
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». Не указан тип.");
		}
		if (!TM_FType::is($f['type']))
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». Отсутствует тип столбца «{$f['type']}».");
		}
		
		/* NULL */
		if (isset($f['null']) and $f['null'] !== true and $f['null'] !== false)
		{	
			throw new Exception("Поле «{$table}.{$f['identified']}». NULL задан неверно. Необходимо указать «true» или «false».");
		}
		
		/* DEFAULT */
		if (array_key_exists("default", $f))
		{
			if (is_bool($f['default']))
			{
				$f['default'] = (string)(int)$f['default'];
			}
			
			if (!is_null($f['default']) and !TM_FType::check($f['type'], $f['default']))
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». DEFAULT задан неверно. Не соответствие типу. " . TM_FType::get_last_error());
			}
			
			if (is_null($f['default']) and isset($f['null']) and $f['null'] === false)
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». DEFAULT не может быть NULL, т.к. столбец NOT NULL.");
			}
		}
		
		/* PRIMARY */
		if (isset($f['primary']) and $f['primary'] !== true and $f['primary'] !== false)
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». PRIMARY задан неверно. Необходиом указать «true» или «false».");
		}
		
		/* ORDER */
		if (isset($f['order']))
		{
			if (!is_string($f['order']))
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». ORDER задан неверно. Необходимо указать «asc» или «desc».");
			}
			
			$f['order'] = strtolower($f['order']);
			
			if ($f['order'] !== "asc" and $f['order'] !== "desc")
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». ORDER задан неверно. Необходимо указать «asc» или «desc».");
			}
		}
		
		/* UNIQUE */
		if (isset($f['unique']) and $f['unique'] !== true and $f['unique'] !== false)
		{
			throw new Exception("Поле «{$table}.{$f['identified']}». UNIQUE задан неверно. Необходиом указать «true» или «false».");
		}
		
		if (isset($f['unique']) and $f['unique'] === true)
		{
			if (isset($f['unique_key']))
			{
				if (is_scalar($f['unique_key']))
				{
					$f['unique_key'] = (array)$f['unique_key'];
				}
			
				foreach ($f['unique_key'] as $un)
				{
					if (!TM_FType::check("identified", $un))
					{
						throw new Exception("Поле «{$table}.{$f['identified']}». UNIQUE KEY задан неверно. " . TM_FType::get_last_error());
					}
				}
			}
		}
		
		/* FOREIGN */
		if (!empty($f['foreign']))
		{
			if (!is_array($f['foreign']) or (array_values($f['foreign']) !== $f['foreign']) or count($f['foreign']) !== 2)
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». Foreign задан неверно. Указать нужно массив к примеру «['table','ID']»");
			}
			
			if (!TM_FType::check("identified", $f['foreign'][0]))
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». Foreign задан неверно. Идентификатор таблицы задан неверно. " . TM_FType::get_last_error());
			}
			
			if (!TM_FType::check("identified", $f['foreign'][1]))
			{
				throw new Exception("Поле «{$table}.{$f['identified']}». Foreign задан неверно. Идентификатор поля задан неверно. " . TM_FType::get_last_error());
			}
		}
	}
}
?>