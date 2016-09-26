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
{field}{primary}{foreign}
);\n
{sequence_owned}
{unique}
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
		"constraint_primary" => 
<<<SQL
,\n\tCONSTRAINT "{table}_PK" PRIMARY KEY ({pk_field})
SQL
,
		"constraint_foreign" =>
<<<SQL
,\n\tCONSTRAINT "{table}_{key}" FOREIGN KEY ("{identified}")
\t\tREFERENCES "{fk_table}" ("{fk_field}") ON DELETE CASCADE\n
SQL
,
		"index_unique" => 
<<<SQL
CREATE UNIQUE INDEX "{table}_{key}" ON "{table}" ({field});\n
SQL
,
		"index_unique_null" => 
<<<SQL
CREATE UNIQUE INDEX "{table}_{key}" ON "{table}" ({field_all}) WHERE "{field}" IS NOT NULL;
CREATE UNIQUE INDEX "{table}_{key}_NULL" ON "{table}" ({field_all_but}) WHERE "{field}" IS NULL;\n
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
,
		"unique" => 
<<<SQL
SELECT 
	true
FROM 
	"{table}"
WHERE 
	{where}
SQL
,
		"insert" => 
<<<SQL
INSERT INTO "{table}" ({field})
VALUES ({values_num})
RETURNING "{primary}"
SQL
,
		"update" => 
<<<SQL
UPDATE "{table}"
SET 
{field}
WHERE
	"{primary}" = \${num}
SQL
,
		"delete" => 
<<<SQL
DELETE
FROM
	"{table}"
WHERE
	"{primary}" = \$1
SQL
	];
	
	/**
	 * Таблица, с которой собраны meta-данные
	 * 
	 * @var string
	 */
	protected static $_meta_table = "";
	
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
		
		/* SQL */
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
	
		/* Запрос */
		$result = pg_query_params(static::$_db_conn, $query, [$primary]);
		$count = pg_num_rows($result);
		pg_free_result($result);
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
			$where_info = static::_data_info($where);
			
			$sql_where .= "WHERE\n";
			static::check($where);
			$param_num = 1;
			foreach ($where_info as $f)
			{
				if ($param_num !== 1)
				{
					$sql_where .= " AND\n";
				}
				
				$sql_where .= "\t" . TM_FType::get_sql_where($f, $param_num);
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
		$field_info = static::_data_info($field);
		$sql_field = "";
		foreach ($field_info as $key => $f)
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
	 * @param bool $exception_many
	 */
	public static function check(array $data, bool $exception_many = true)
	{
		/* Собираем данные */
		static::_meta();
		
		/* Массив с ошибками */
		$err = [];
		
		/* Отсутствуют данные */
		if (empty($data))
		{
			throw new Exception("Нет данных для проверки.");
		}
		
		/* Данные по полям */
		$fdata = static::_data_info($data);
		
		/* Проверка */
		foreach ($fdata as $f)
		{
			/* NULL */
			if ($f['value'] === null or $f['value'] === "")
			{
				if (isset($f['null']) and $f['null'] === true)
				{
					continue;
				}
			}
			
			/* Не является строкой */
			if (!is_scalar($f['value']))
			{
				$error = "Поле «{$f['name']}» задано неверно. Не является строкой.";
				if ($exception_many)
				{
					$err[$f['identified']] = $error;
					continue;
				}
				else
				{
					throw new Exception($error);
				}
			}
			
			/* Преобразуем в строку */
			if ($f['value'] === true or $f['value'] === false) 
			{
				$f['value'] = (string)(int)$f['value'];
			}
			else
			{
				$f['value'] = (string)$f['value'];
			}
			
			/* Не заполнено */
			if (!isset($f['default']) and trim($f['value']) === "")
			{
				$error = "«" . static::$_name . "». Поле «{$f['name']}» не заполнено.";
				if ($exception_many)
				{
					$err[$f['identified']] = $error;
					continue;
				}
				else
				{
					throw new Exception($error);
				}	
			}
			
			/* Существует ли поле */
			if (!TM_FType::check($f['type'], $f['value']))
			{
				$error = "Поле «{$f['name']}» задано неверно. " . TM_FType::get_last_error();
				if ($exception_many)
				{
					$err[$f['identified']] = $error;
					continue;
				}
				else
				{
					throw new Exception($error);
				}	
			}
		}
		
		if (!empty($err))
		{
			throw new Exception_Many($err);
		}
	}
	
	/**
	 * Проверка на уникальность
	 * 
	 * @param array $data
	 * @param string $primary
	 * @param boolean $exception
	 * @return array
	 */
	public static function unique(array $data, string $primary = null, bool $exception = false) : array
	{
		/* Собираем данные */
		static::_meta();
		
		/* Проверка */
		if (empty($data))
		{
			throw new Exception("Таблица «" . static::$_table . "». Не указаны данные для проверки уникальности.");	
		}
		static::check($data, !$exception);
		$fdata = static::_data_info($data);
		
		if (!empty($primary))
		{
			static::is($primary);
		}
		
		/* SQL полей */
		$where = ""; $num = 1;
		foreach ($fdata as $f)
		{
			if ($num > 1)
			{
				$where .= " AND\n\t";
			}
			
			$where .= TM_FType::get_sql_where($f, $num);
			$num++;
		}
		
		/* PRIMARY */
		if (!empty($primary))
		{
			$where .= " AND\n\t" . TM_FType::get_sql_where(static::$_primary, $num, true);
		}

		/* SQL */
		$query_data = 
		[
			"{table}" => static::$_table,
			"{where}" => $where
		];
		$query = str_replace(array_keys($query_data), array_values($query_data), self::$_sql['unique']);
		
		/* Запрос */
		$values = array_values($data);
		if (!empty($primary))
		{
			$values[] = $primary;
		}
		
		$result = pg_query_params(self::$_db_conn, $query, $values);
		
		/* Данные */
		$count = pg_num_rows($result);
		pg_free_result($result);
		if ($count > 0)
		{
			/* Текст ошибки */
			$error = "«" . static::$_name . "» с полем «{$fdata[0]['name']}» : «{$fdata[0]['value']}» уже существует.";
			if (!$exception)
			{
				return [$fdata[0]['identified'] => $error];
			}
			else
			{
				throw new Exception($error);
			}
		}
		
		return [];
	}
	
	/**
	 * Добавить данные
	 * 
	 * @param array $data
	 */
	public static function insert(array $data) : array
	{
		/* Собираем данные */
		static::_meta();
		
		/* Проверка */
		static::check($data);
		
		/* Поля обязательные для заполнения */
		foreach (static::$_field as $f)
		{
			if 
			(
				!in_array($f['type'], ["text","html","tags","id","order"]) and 
				!isset($f['default']) and
				!in_array($f['identified'], array_keys($data))
			)
			{
				throw new Exception("«" . static::$_name . "». Отсутствует поле «{$f['identified']}» обязательное для заполнения.");
			}
		}
		
		/* Внешние ключи */
		foreach (static::$_foreign as $fk_field)
		{
			if (array_key_exists("class", $fk_field))
			{
				/* Определяем значение внешнего ключа и делаем проверку по первичному ключу */
				foreach ($data as $identified => $value)
				{
					if ($identified === $fk_field['identified'])
					{
						if ($value === null)
						{
							break;
						}
						
						call_user_func([$fk_field['class'], "is"], $value);
						static::_meta();
						break;
					}
				}
			}
		}
		
		/* Уникальность */
		$err_unique = [];
		foreach (static::$_unique as $un_field)
		{
			$un_data = [];
			foreach ($un_field as $identified)
			{
				$un_data[$identified] = $data[$identified];
			}
			
			$err_unique = array_merge($err_unique, static::unique($un_data));
		}
		if (!empty($err_unique))
		{
			throw new Exception_Many($err_unique);
		}
		
		/* SQL */
		$values_num = []; $num = 1;
		foreach ($data as $f)
		{
			$values_num[] = $num;
			$num++;
		}
		
		$query_data = 
		[
			"{table}" => static::$_table,
			"{field}" => "\"" . implode("\", \"", array_keys($data)) . "\"",
			"{values_num}" => "\$" . implode(", \$", $values_num),
			"{primary}" => static::$_primary['identified']
		];
		
		$query = str_replace(array_keys($query_data), array_values($query_data), self::$_sql['insert']);
		
		/* Запрос */
		$result = pg_query_params(self::$_db_conn, $query, array_values($data));
		if ($result === false)
		{
			throw new Exception("«" . static::$_name . "». Не удалось вставить данные. " . pg_last_error(self::$_db_conn));
		}
		
		$row = pg_fetch_row($result);
		pg_free_result($result);
		
		/* Делаем выборку по первичному ключу и возвращаем результат */
		return static::get($row[0]);
	}
	
	/**
	 * Обновить данные по первичному ключу
	 * 
	 * @param array $data
	 * @param string $primary
	 * @return array
	 */
	public static function update(array $data, string $primary) : array
	{
		/* Собираем данные */
		static::_meta();
		
		/* Проверка */
		static::check($data);
		$old = static::get($primary);
		
		/* Внешние ключи */
		foreach (static::$_foreign as $fk_field)
		{
			/* Входит ли поле во внешний ключ */
			if (array_diff($fk_field, array_keys($data)) === $fk_field)
			{
				continue;
			}
			
			/* Если присутствует класс делаем проверку */
			if (array_key_exists("class", $fk_field))
			{
				/* Определяем значение внешнего ключа и делаем проверку по первичному ключу */
				foreach ($data as $identified => $value)
				{
					if ($identified === $fk_field['identified'])
					{
						if ($value === null)
						{
							break;
						}
						
						call_user_func([$fk_field['class'], "is"], $value);
						break;
					}
				}
			}
		}
		
		/* Уникальность */
		$err_unique = [];
		foreach (static::$_unique as $un_field)
		{
			/* Входит ли поле в ключ уникальности */
			if (array_diff($un_field, array_keys($data)) === $un_field)
			{
				continue;
			}
			
			/* Данные */
			$un_data = [];
			foreach ($un_field as $identified)
			{
				if (array_key_exists($identified, $data))
				{
					$un_data[$identified] = $data[$identified];
				}
				else
				{
					$un_data[$identified] = $old[$identified];
				}
			}
			
			/* Проверяем на уникальность */
			$err_unique = array_merge($err_unique, static::unique($un_data, $primary));
		}
		
		if (!empty($err_unique))
		{
			throw new Exception_Many($err_unique);
		}
		
		/* SQL */
		$fdata = static::_data_info($data);

		$field = ""; $num = 1;
		foreach ($fdata as $key => $f)
		{
			if ($key !== 0)
			{
				$field .= ",\n";
			}
			
			$field .= "\t\"{$f['identified']}\" = \${$num}";
			$num++;
		}
		
		$query_data = 
		[
			"{table}" => static::$_table,
			"{field}" => $field,
			"{primary}" => static::$_primary['identified'],
			"{num}" => $num
		];
		
		$query = str_replace(array_keys($query_data), array_values($query_data), self::$_sql['update']);
		
		/* Запрос */
		$values = array_values($data);
		$values[] = $primary;
		
		$result = pg_query_params(self::$_db_conn, $query, $values);
		if ($result === false)
		{
			throw new Exception("«" . static::$_name . "». Не удалось обновить данные. " . pg_last_error(self::$_db_conn));
		}
		pg_free_result($result);
		
		/* Делаем выборку по первичному ключу и возвращаем результат */
		return static::get($primary);
	}

	/**
	 * Удалить
	 * 
	 * @param string $primary
	 * @return array
	 */
	public static function delete(string $primary) : array
	{
		/* Собираем данные */
		static::_meta();
		
		/* Старые данные */
		$old = self::get($primary);
		
		/* SQL */
		$query_data = 
		[
			"{table}" => static::$_table,
			"{primary}" => static::$_primary['identified']
		];
		$query = str_replace(array_keys($query_data), array_values($query_data), self::$_sql['delete']);
		
		/* Запрос */
		$result = pg_query_params(self::$_db_conn, $query, [$primary]);
		if ($result === false)
		{
			throw new Exception("«" . static::$_name . "». Не удалось удалить данные. " . pg_last_error(self::$_db_conn));
		}
		pg_free_result($result);
		
		/* Возвращаем старые значения */
		return $old;
	}

	/**
	 * Получить SQL для создания таблицы
	 * 
	 * @param boolean $drop_if_exist
	 * @return boolean
	 */
	public static function create(bool $drop_if_exist = true) : bool
	{
		/* Собираем данные */
		static::_meta();
		
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
			$primary = str_replace(['{table}', '{pk_field}'], [static::$_table, $pk_field], self::$_sql['constraint_primary']);
		}
		
		/* UNIQUE */
		$unique = "";
		if (!empty(static::$_unique))
		{
			$num = 1;
			foreach (static::$_unique as $key => $field_un)
			{
				$field_un = static::_data_info($field_un);
								
				/* NULL полей нет */
				if (array_search(true, array_column($field_un, "null")) === false or count($field_un) === 1)
				{
					$data = 
					[
						"{table}" => static::$_table,
						"{key}" => "UN" . $num,
						"{field}" => "\"" . implode("\", \"", array_column($field_un, "identified")) . "\""
					];
					
					$unique .= str_replace(array_keys($data), array_values($data), self::$_sql['index_unique']);
				}
				/* Есть NULL поля */
				else
				{
					/* Идентификаторы полей с NULL */
					foreach ($field_un as $f)
					{
						if (isset($f['null']) and $f['null'] === true)
						{
							/* Все поля кроме текушего */
							$field_all_but = array_column($field_un, "identified");
							$key = array_search($f['identified'], $field_all_but);
							unset($field_all_but[$key]);
							
							
							$data = 
							[
								"{table}" => static::$_table,
								"{key}" => "UN" . $num,
								"{field}" => $f['identified'],
								"{field_all}" => "\"" . implode("\", \"", array_column($field_un, "identified")) . "\"",
								"{field_all_but}" => "\"" . implode("\", \"", $field_all_but) . "\"",
							];
							
							$unique .= str_replace(array_keys($data), array_values($data), self::$_sql['index_unique_null']);
						}
					}
					
				}
				
				$num++;
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
				
				$foreign .= str_replace(array_keys($data), array_values($data), self::$_sql['constraint_foreign']);
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
		
		$query = str_replace(array_keys($data), array_values($data), self::$_sql['create']);
		
		/* Запрос */
		$result = pg_query(self::$_db_conn, $query);
		if ($result === false)
		{
			return false;
		}
		
		pg_free_result($result);
		
		return true;
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
			/* Идентификатор */
			if (empty($f['identified']))
			{
				throw new Exception("Таблица «" . static::$_table . "». Не указан идентификатор у поля.");
			}

			if (!TM_FType::check("identified", $f['identified']))
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Идентификатор задан неверно. " . TM_FType::get_last_error());
			}

			/* Наименование поля */
			if (empty($f['name']))
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Наименование не задано.");
			}

			if (!TM_FType::check("string", $f['name']))
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Наименование задано неверно. " . TM_FType::get_last_error());
			}

			/* Тип */
			if (empty($f['type']))
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Не указан тип.");
			}
			if (!TM_FType::is($f['type']))
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Отсутствует тип столбца «{$f['type']}».");
			}

			/* NULL */
			if (isset($f['null']) and $f['null'] !== true and $f['null'] !== false)
			{	
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». NULL задан неверно. Необходимо указать «true» или «false».");
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
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». DEFAULT задан неверно. Не соответствие типу. " . TM_FType::get_last_error());
				}

				if (is_null($f['default']) and isset($f['null']) and $f['null'] === false)
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». DEFAULT не может быть NULL, т.к. столбец NOT NULL.");
				}
			}

			/* PRIMARY */
			if (isset($f['primary']) and $f['primary'] !== true and $f['primary'] !== false)
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». PRIMARY задан неверно. Необходиом указать «true» или «false».");
			}

			/* ORDER */
			if (isset($f['order']))
			{
				if (!is_string($f['order']))
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». ORDER задан неверно. Необходимо указать «asc» или «desc».");
				}

				$f['order'] = strtolower($f['order']);

				if ($f['order'] !== "asc" and $f['order'] !== "desc")
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». ORDER задан неверно. Необходимо указать «asc» или «desc».");
				}
			}

			/* UNIQUE */
			if (isset($f['unique']) and $f['unique'] !== true and $f['unique'] !== false)
			{
				throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». UNIQUE задан неверно. Необходиом указать «true» или «false».");
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
							throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». UNIQUE KEY задан неверно. " . TM_FType::get_last_error());
						}
					}
				}
			}

			/* FOREIGN */
			if (!empty($f['foreign']))
			{
				$foreign = $f['foreign'];
				if (empty($foreign['table']) or empty($foreign['field']))
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Не указан параметр «table» или «field».");
				}
				
				if (!TM_FType::check("identified", $foreign['table']))
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Параметр «table» задан неверно. " . TM_FType::get_last_error());
				}
				
				if (!TM_FType::check("identified", $foreign['field']))
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Параметр «field» задан неверно. " . TM_FType::get_last_error());
				}
				
				if (array_key_exists("key", $foreign) and !TM_FType::check("identified", $foreign['key']))
				{
					throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Параметр «key» задан неверно. " . TM_FType::get_last_error());
				}
				
				if (array_key_exists("class", $foreign))
				{
					if (!TM_FType::check("identified", $foreign['class']))
					{
						throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Параметр «class» задан неверно. " . TM_FType::get_last_error());
					}
					
					if (!class_exists($foreign['class']))
					{
						throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Класс «{$foreign['class']}» отсутствует.");
					}
					
					/* Только поле - первичный ключ может быть внешним ключом */
					$foreign_field = call_user_func([$foreign['class'], "get_field"]);
					
					if (!in_array($foreign['field'], array_column($foreign_field, "identified")))
					{
						throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Поле «{$foreign['table']}.{$foreign['field']}» отсутствует.");
					}
					
					foreach ($foreign_field as $ff)
					{
						if ($ff['identified'] === $foreign['field'])
						{
							if (!array_key_exists("primary", $ff) and $ff['type'] !== "id")
							{
								throw new Exception("Поле «" . static::$_table . ".{$f['identified']}». Foreign задан неверно. Поле «{$foreign['table']}.{$foreign['field']}» не является первичным ключом.");
							}
							
							break;
						}
					}
				}	
			}
		}
	}
	
	/**
	 * Вернуть все поля
	 */
	public static function get_field() : array
	{
		return static::$_field;
	}

	/**
	 * Получить сведения по полям на основании данных
	 * 
	 * @param array $data
	 * @return array
	 */
	protected static function _data_info(array $data) : array
	{
		$fdata = [];
		
		/* Массив-список - список идентификаторов */
		if ($data === array_values($data))
		{
			foreach ($data as $identified)
			{
				$isset = false;
				foreach (static::$_field as $f)
				{
					if ($identified === $f['identified'])
					{
						$fdata[] = $f;
						$isset = true;
						break;
					}
				}

				if ($isset === false)
				{
					throw new Exception("«" . static::$_name . "». Поля с идентификатором «{$identified}» не существует.");
				}
			}
		}
		/* Ассоциативный массив */
		else
		{
			foreach ($data as $identified => $value)
			{
				$isset = false;
				foreach (static::$_field as $f)
				{
					if ($identified === $f['identified'])
					{
						$f = array_merge($f, ["value" => $value]);
						$fdata[] = $f;
						$isset = true;
						break;
					}
				}

				if ($isset === false)
				{
					throw new Exception("«" . static::$_name . "». Поля с идентификатором «{$identified}» не существует.");
				}
			}
		}
		
		return $fdata;
	}

	/**
	 * Собираем информацию по таблице
	 */
	protected static function _meta() : bool
	{
		/* Не собирать повторно */
		if (self::$_meta_table === static::$_table)
		{
			return true;
		}
		
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
			if (array_key_exists("foreign", $f))
			{
				if (empty($f['foreign']['key']))
				{
					$f['foreign']['key'] = static::$_table . "_FK_" . $f['identified'];
				}
				
				$f['foreign']['identified'] = $f['identified'];
				
				static::$_foreign[] =  $f['foreign'];
			}
		}
		
		/* Ещё проверки */
		if (empty(static::$_primary))
		{
			throw new Exception("Таблица «" . static::$_table . "». Не задан первичный ключ.");
		}
		
		/* Укажим таблицу */
		self::$_meta_table = static::$_table;
		
		return true;
	}
}

/**
 * Исключение включающее список ошибок
 */
class Exception_Many extends Exception
{
	/**
	 * Список 
	 * 
	 * @var array
	 */
	private $_err = [];

	/**
	 * Конструктор
	 */
	public function __construct(array $err)
	{
		$this->_err = $err;
		
		parent::__construct(null);
	}
	
	/**
	 * Получить список ошибок
	 */
	public function get_err()
	{
		return $this->_err;
	}
}
?>