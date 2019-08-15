<?php
namespace TM;

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
	 * Использовать значение по умолчанию, если поле не указано при INSERT
	 * Если «true» необходимо задать параметр «default_value»
	 *
	 * @var boolean
	 */
	public $default = false;

	/**
	 * Значение по умолчанию
	 *
	 * @var mixed
	 * @example 100, true, "красный", null
	 */
	public $default_value;

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
	 * Можно ли вставить пустую строку в значение поля при INSERT или UPDATE.
	 *
	 * @var bool
	 */
	public $empty_allow = false;

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
	 *
	 * @var string
	 * @example "int", "string", "float", "boolean"
	 */
	public $type_php = "string";

	/**
	 * Функция проверка поля
	 * Может быть строка с наименованием функции или функцией обратного вызова. Функция принимает «string» и должна вернуть «boolean» значение или вызвать исключение.
	 * Если указан метод, то он должен быть статическим
	 *
	 * @var callable
	 * @example "is_numeric", ["static", "check"], function (string $value) : bool { if (!is_numeric($value)) throw new \Exception("Не число"); return true; };
	 */
	public $check = ["static", "check"];

	/**
	 * Функция обработки поля перед INSERT или UPDATE
	 * Может быть строка с наименованием функции или функцией обратного вызова. Функция должна вернуть «string».
	 *
	 * @var callable
	 * @example "strtolower", ["static", "prepare"], function (string $value) : string { return date ("Y-m-d", strtotime($value)); }
	 */
	public $prepare;

	/**
	 * Функция обработки поля после выборки select(), selectl().
	 *
	 * @var callable
	 * @example function ($value) { return json_decode($value, true); }
	 */
	public $process;

	/**
	 * Функция проверки поля
	 *
	 * @param string $value
	 * @return bool
	 */
	public static function check (string $value) : bool
	{
		throw new \Exception("Не задана функция self::check()");
	}
}

?>