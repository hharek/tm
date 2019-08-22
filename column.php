<?php
namespace TM;

use mysql_xdevapi\Exception;

/**
 * Столбец
 *
 * @package TM
 */
class Column
{
	/**
	 * Наименование столбца
	 *
	 * @var string
	 * @example "Price", "My column"
	 */
	public $column;

	/**
	 * Наименование
	 * Добавляется в комментарий к столбцу и используется при выводе сообщений об ошибках
	 *
	 * @var string
	 * @example "Цена", "Мой столбец"
	 */
	public $name;

	/**
	 * Является ли значения в столбце уникальными
	 *
	 * @var boolean
	 */
	public $unique = false;

	/**
	 * Наименование уникального индекса для столбца
	 * Если в уникальном индексе используется один или более столбцов. Пример: CREATE UNIQUE INDEX "Name_UN" ON "product" ("Name", "Category_ID");
	 *
	 * @var string
	 * @example "Name_UN"
	 */
	public $unique_key;

	/**
	 * Является ли столбец первичным ключом.
	 *
	 * @var boolean
	 */
	public $primary = false;

	/**
	 * Использовать столбец для сортировки в методах select(), selectl()
	 *
	 * @var string
	 * @example "asc", "desc"
	 */
	public $order;

	/**
	 * Порядок сортировки по нескольким столбцам
	 * Если запрос нужно сортировать по нескольким столбцам, то необходимо задать каждому столбцу индекс. Чем меньше тем приоритетней
	 *
	 * @var int
	 */
	public $order_index = 0;

	/**
	 * Значение по умолчанию. Используется при INSERT если не указано значение
	 *
	 * @var mixed
	 * @example 100, true, "красный", null
	 */
	public $default;

	/**
	 * Значение по умолчанию в формате SQL
	 *
	 * @var string
	 * @example "now()", "currval('product_seq')"
	 */
	public $default_sql;

	/**
	 * Может ли поле иметь значение NULL
	 *
	 * @var bool
	 */
	public $null = false;

	/**
	 * Можно ли использовать пустую строку ("") при INSERT или UPDATE.
	 *
	 * @var bool
	 */
	public $empty = false;

	/**
	 * Нужно ли значение обрабатывать функцией trim
	 *
	 * @var bool
	 */
	public $trim = false;

	/**
	 * Обязательно ли указывать столбец при INSERT.
	 *
	 * @var bool
	 */
	public $require = true;

	/**
	 * Показать столбец в выборке selectl()
	 *
	 * @var bool
	 */
	public $lite = true;

	/**
	 * Тип столбца. Используется при выполнении функции create
	 *
	 * @var string
	 * @example "varchar(255)", "text", "int(4)", "boolean"
	 */
	public $type_sql;

	/**
	 * PHP тип значений столбца. Приведение к PHP типу осуществляется после выборки: select(), selectl(), get()
	 * Если необходимо привести к типу «array» или другому классу, воспользуйтесь параметром «process»
	 * Допустимые значения: "string", "int", "integer", "float", "double", "real", "bool", "boolean", "array", "object"
	 *
	 * @var string
	 * @example "int", "string", "float", "boolean"
	 */
	public $type_php;

	/**
	 * Функция проверка значения перед запросом
	 * Может быть строка с наименованием функции или функцией обратного вызова. Функция должна вернуть «boolean» значение или вызвать исключение.
	 * Если указан метод, то он должен быть статическим. Чтобы не проверять значение укажите «null»
	 *
	 * @var callable
	 * @example "is_numeric", ["static", "check"], function (string $value) : bool { if (!is_numeric($value)) throw new \Exception("Не число"); return true; }; , null
	 */
	public $check = ["static", "check"];

	/**
	 * Функция обработки значения перед запросом
	 * Может быть строка с наименованием функции или функцией обратного вызова. Функция должна вернуть «string». Чтобы не обрабатывать значение укажите «null»
	 *
	 * @var callable
	 * @example "strtolower", ["static", "prepare"], function (string $value) : string { return date ("Y-m-d", strtotime($value)); }; null
	 */
	public $prepare = ["static", "prepare"];

	/**
	 * Функция обработки значения после запроса
	 *
	 * @var callable
	 * @example function ($value) { return json_decode($value, true); }
	 */
	public $process = ["static", "process"];

	/**
	 * Функция проверка значения перед запросом
	 * Используется по умолчанию, если не указана другая функция в переменной $this->check
	 *
	 * @param mixed $value
	 * @return bool
	 */
	public static function check ($value, Column $column = null) : bool
	{
		switch ($column->type_php)
		{
			case "int":
			case "integer":
			case "float":
			case "double":
			case "real":
				if (is_numeric($value))
					return true;
				else
					return false;
				break;

			case "bool":
			case "boolean":
				if ($value === true || $value === false)
				{
					return true;
				}
				elseif ($value === 1 || $value === 0)
				{
					return true;
				}
				elseif (is_string($value))
				{
					if (in_array($value, PGSQL_BOOLEAN_TRUE) || in_array($value, PGSQL_BOOLEAN_FALSE))
						return true;
					else
						return false;
				}
				break;

			case "array":
				if (is_array($value))
					return true;
				else
					return false;
				break;

			case "object":
				if (is_object($value) && is_a($value, \stdClass::class))
					return true;
				else
					return false;
		}

		return true;
	}

	/**
	 * Функция обработки значения перед запросом
	 * Используется по умолчанию, если не указана другая функция в переменной $this->prepare
	 *
	 * @param mixed $value
	 * @param Column $column
	 * @return string
	 */
	public static function prepare ($value, Column $column = null) : string
	{
		switch ($column->type_php)
		{
			case "bool":
			case "boolean":
				if (is_bool($value))
				{
					if ($value === true)
						return "1";
					else if ($value === false)
						return "0";
				}
				elseif (is_string($value))
				{
					if (in_array($value, PGSQL_BOOLEAN_TRUE))
						return "1";
					elseif (in_array($value, PGSQL_BOOLEAN_FALSE))
						return "0";
				}
				else
				{
					return (string)$value;
				}
				break;

			case "array":
			case "object":
				return json_encode($value, PREPARE_JSON_ENCODE);
				break;
		}

		return (string)$value;
	}

	/**
	 * Функция обработки значения после запроса
	 * Используется по умолчанию, если не указана другая функция в переменной $this->process
	 *
	 * @param string $value
	 * @param Column|null $column
	 * @return mixed
	 */
	public static function process (string $value, Column $column = null)
	{
		switch ($column->type_php)
		{
			case "int":
			case "integer":
				return (int)$value;
				break;

			case "float":
			case "double":
			case "real":
				return (float)$value;
				break;

			case "bool":
			case "boolean":
				if ($value == "t")
					return true;
				else if ($value == "f")
					return false;
				break;

			case "array":
				return json_decode($value, true);
				break;

			case "object":
				return json_decode($value, false, 512, JSON_THROW_ON_ERROR);
				break;
		}

		return $value;
	}
}

?>