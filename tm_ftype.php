<?php
/**
 * Table Manager. Field Type - Типы полей таблицы
 */
class TM_FType
{
	/**
	 * Текст последней ошибки
	 * 
	 * @var string
	 */
	private static $_error;
	
	/**
	 * Доступные типы
	 * 
	 * @var array
	 */
	private static $_type = 
	[
		"identified",
		"string",
		"text",
		"html",
		"int",
		"uint",
		"boolean",
		"email",
		"price",
		"date",
		"datetime",
		"url_part",
		"url_path",
		"url",
		"tags",
		"path",
		"ip",
		"id",
		"order"
	];

	/**
	 * SQL по запросу CREATE по каждому типу
	 * 
	 * @var array
	 */
	private static $_sql_create = 
	[
		"identified" => "varchar(255)",
		"string" => "varchar(255)",
		"text" => "text",
		"html" => "text",
		"int" => "int",
		"uint" => "int",
		"boolean" => "boolean",
		"email" => "varchar(127)",
		"price" => "numeric(10,2)",
		"date" => "date",
		"datetime" => "datetime",
		"url_part" => "varchar(255)",
		"url_path" => "varchar(255)",
		"url" => "varchar(255)",
		"tags" => "text",
		"path" => "varchar(255)",
		"ip" => "varchar(15)",
		"id" => "int",
		"order" => "int"
	];
	
	/**
	 * Проверка поля
	 * 
	 * @param string $type
	 * @param string $str
	 * @return boolean
	 */
	public static function check(string $type, string $str) : bool
	{
		try
		{
			/* Доступный тип */
			if (!in_array($type, self::$_type))
			{
				throw new Exception("Указанный тип «{$type}» отсутствует.");
			}

			/* Пустая строка */
			if (trim($str) === "")
			{
				throw new Exception("Пустая строка");
			}

			/* Нулевой символ */
			if (strpos($str, chr(0)) !== false)
			{
				throw new Exception("Обнаружен нулевой символ.");
			}

			/* Символы не в UTF-8 */
			if (mb_detect_encoding($str, "UTF-8") === false)
			{
				throw new Exception("Бинарная строка, либо символы не в UTF-8.");
			}

			/* Проверка по типу */
			self::{"_" . $type}($str);
			
			return true;
		}
		catch (Exception $e)
		{
			self::$_error = $e->getMessage();
			
			return false;
		}
	}
	
	/**
	 * Текст последней ошибки
	 * 
	 * @return string
	 */
	public static function get_last_error() : string
	{
		return self::$_error;
	}
	
	/**
	 * Доступен ли указанный тип
	 * 
	 * @param string $type
	 */
	public static function is(string $type) : bool
	{
		return in_array($type, self::$_type);
	}
	
	/**
	 * Получить SQL по типу для запроса CREATE
	 * 
	 * @param string $type
	 * @return string
	 */
	public static function get_sql_create(string $type) : string
	{
		if (!in_array($type, self::$_type))
		{
			throw new Exception("Указанный тип «{$type}» отсутствует.");
		}
		
		return self::$_sql_create[$type];
	}
	
	/**
	 * Получить SQL для запроса SELECT
	 * 
	 * @param array $field
	 * @return string
	 */
	public static function get_sql_select(array $field) : string
	{
		/* INT и NULL */
		if 
		(
			($field['type'] === "int" or $field['type'] === "uint") and
			(isset($field['null']) and $field['null'] === true)
		)
		{
			$sql = 
<<<SQL
COALESCE ("{$field['identified']}", 0) as "{$field['identified']}"
SQL;
			return $sql;
		}

		/* Добавляем */
		$sql = "\"" . $field['identified'] . "\"";

		/* boolean */
		if ($field['type'] === "boolean")
		{
			$sql .= "::int";
		}
		
		return $sql;
	}

	/**
	 * Идентификатор
	 * 
	 * @param string $str
	 */
	private static function _identified(string $str)
	{
		if (ctype_alnum(str_replace("_", "", $str)) === false)
		{
			throw new Exception("Допускаются символы: a-z,0-9,\"_\" .");
		}
	}
	
	/**
	 * Строка не более 255 символов, и без пробельных символов
	 * 
	 * @param string $str
	 */
	private static function _string(string $str)
	{
		if (strpbrk($str, "\n\r\t\v\f") !== false)
		{
			throw new Exception("Недопустимые символы.");
		}

		if (strpbrk($str, "><") !== false)
		{
			throw new Exception("HTML-символы.");
		}

		if (mb_strlen($str) > 255)
		{
			throw new Exception("Большая строка.");
		}
	}

	/**
	 * Cтрока без html-тегов
	 * 
	 * @param string $str
	 */
	private static function _text(string $str)
	{
		if (strpbrk($str, "><") !== false)
		{
			throw new Exception("HTML-символы.");
		}
	}

	/**
	 * Строка без содержания тега <script>
	 * 
	 * @param string $str
	 */
	private static function _html(string $str)
	{
		$str = mb_strtolower($str);
		if (mb_strpos($str, "<script") !== false)
		{
			throw new Exception("Наличие тега «script».");
		}
	}

	/**
	 * Число со знаком
	 * 
	 * @param string $str
	 */
	private static function _int(string $str)
	{
		if (!is_numeric($str))
		{
			throw new Exception("Не является числом.");
		}
		
		if (strpos($str, ".") !== false)
		{
			throw new Exception("Тип float.");
		}
	}

	/**
	 * Число без знака
	 * 
	 * @param string $str
	 */
	private static function _uint(string $str)
	{
		self::_int($str);

		$str = (int) $str;

		if ($str !== abs($str))
		{
			throw new Exception("Отрицательное число.");
		}
	}
	
	/**
	 * Булёвое значение
	 * 
	 * @param string $str
	 */
	private static function _boolean(string $str)
	{
		if ($str !== "0" and $str !== "1")
		{
			throw new Exception("Необходимо указать «0» или «1».");
		}
	}
	
	/**
	 * Почтовый ящик
	 * 
	 * @param string $str
	 */
	private static function _email(string $str)
	{
		if (!filter_var($str, FILTER_VALIDATE_EMAIL))
		{
			throw new Exception("Не прошёл валидацию.");
		}
	}

	/**
	 * Цена - два числа после точки всегда положительная
	 * 
	 * @param string $str
	 */
	private static function _price(string $str)
	{
		if (!is_numeric($str))
		{
			throw new Exception("Не является числом.");
		}

		if (strpos($str, ".") === false)
		{
			throw new Exception("Целое число.");
		}

		if (substr($str, -3, 1) !== ".")
		{
			throw new Exception("Необходимо две цифры после точки.");
		}
		
		if ((int)$str !== abs((int)$str))
		{
			throw new Exception("Отрицательное число.");
		}
	}

	/**
	 * Дата
	 * 
	 * @param string $str
	 */
	private static function _date(string $str)
	{
		if (strtotime($str) === false)
		{
			throw new Exception("Не является строкой даты или времени.");
		}
	}
	
	/**
	 * Дата и время
	 * 
	 * @param string $str
	 */
	private static function _datetime(string $str)
	{
		self::_date($str);
	}

	/**
	 * Часть урла
	 * 
	 * @param string $str
	 */
	private static function _url_part(string $str)
	{
		/* В нижний регистра */
		$str = mb_strtolower($str);
		
		/* «.» или «..» */
		if ($str === "." or $str === "..")
		{
			throw new Exception("Урл не может быть указан как «.» или «..».");
		}
		
		/* Недопустимые символы */
		$str = strtr
		(
			$str, 
			[
				'0'=>'', '1'=>'', '2'=>'', '3'=>'', '4'=>'', '5'=>'', '6'=>'', '7'=>'', '8'=>'', '9'=>'',
				'a'=>'', 'b'=>'', 'c'=>'', 'd'=>'', 'e'=>'', 'f'=>'', 'g'=>'', 'h'=>'', 'i'=>'', 'j'=>'',
				'k'=>'', 'l'=>'', 'm'=>'', 'n'=>'', 'o'=>'', 'p'=>'', 'q'=>'', 'r'=>'', 's'=>'', 't'=>'',
				'u'=>'', 'v'=>'', 'w'=>'', 'x'=>'', 'y'=>'', 'z'=>'', 
				'а'=>'', 'б'=>'', 'в'=>'', 'г'=>'', 'д'=>'', 'е'=>'', 'ё'=>'', 'ж'=>'', 'з'=>'', 'и'=>'',
				'й'=>'', 'к'=>'', 'л'=>'', 'м'=>'', 'н'=>'', 'о'=>'', 'п'=>'', 'р'=>'', 'с'=>'', 'т'=>'', 
				'у'=>'', 'ф'=>'', 'х'=>'', 'ц'=>'', 'ч'=>'', 'ш'=>'', 'щ'=>'', 'ъ'=>'', 'ы'=>'', 'ь'=>'',
				'э'=>'', 'ю'=>'', 'я'=>'',
				'_'=>'', '-'=>'', '.'=>'', ' '=>'_'
			]
		);
		
		if ($str !== "")
		{
			throw new Exception("Допускаются символы: 0-9,a-z,а-я,«_»,«-»,«.» .");
		}
	}

	/**
	 * Путь урла
	 * 
	 * @param string $str
	 */
	private static function _url_path(string $str)
	{
		/* Срезаем символы слэша в начале и конце */
		if (mb_substr($str, 0, 1) === "/")
		{
			$str = mb_substr($str, 1, mb_strlen($str) - 1);
		}

		if (mb_substr($str, mb_strlen($str) - 1, 1) === "/")
		{
			$str = mb_substr($str, 0, mb_strlen($str) - 1);
		}
		
		/* Разбор */
		$str_ar = explode("/", $str);
		foreach ($str_ar as $val)
		{
			self::_url_part($val);
		}
	}
	
	/**
	 * Урл
	 * 
	 * @param string $str
	 */
	private static function _url(string $str)
	{
		$parse_url = parse_url($str);
		
		if (!is_string($str))
		{
			throw new Exception("Не является строкой");
		}
		
		if ($parse_url === false)
		{
			throw new Exception("Не прошёл парсинг");
		}
		
		if (!empty($parse_url['path']))
		{
			self::_url_path($parse_url['path']);
		}
		
		if (!empty($parse_url['query']))
		{
			self::_string($parse_url['query']);
		}
	}

	/**
	 * Строка с тэгами через запятую
	 * 
	 * @param string $str
	 */
	private static function _tags(string $str)
	{
		/* В нижний регистр */
		$str = mb_strtolower($str);
		
		/* Проверка на наличие недопустимых символов */
		$str_trim = strtr
		(
			$str, 
			[
				'0'=>'', '1'=>'', '2'=>'', '3'=>'', '4'=>'', '5'=>'', '6'=>'', '7'=>'', '8'=>'', '9'=>'',
				'a'=>'', 'b'=>'', 'c'=>'', 'd'=>'', 'e'=>'', 'f'=>'', 'g'=>'', 'h'=>'', 'i'=>'', 'j'=>'',
				'k'=>'', 'l'=>'', 'm'=>'', 'n'=>'', 'o'=>'', 'p'=>'', 'q'=>'', 'r'=>'', 's'=>'', 't'=>'',
				'u'=>'', 'v'=>'', 'w'=>'', 'x'=>'', 'y'=>'', 'z'=>'', 
				'а'=>'', 'б'=>'', 'в'=>'', 'г'=>'', 'д'=>'', 'е'=>'', 'ё'=>'', 'ж'=>'', 'з'=>'', 'и'=>'',
				'й'=>'', 'к'=>'', 'л'=>'', 'м'=>'', 'н'=>'', 'о'=>'', 'п'=>'', 'р'=>'', 'с'=>'', 'т'=>'', 
				'у'=>'', 'ф'=>'', 'х'=>'', 'ц'=>'', 'ч'=>'', 'ш'=>'', 'щ'=>'', 'ъ'=>'', 'ы'=>'', 'ь'=>'',
				'э'=>'', 'ю'=>'', 'я'=>'',
				','=>'', ' '=>''
			]
		);
		
		if ($str_trim !== "")
		{
			throw new Exception("Допускаются символы: 0-9,a-z,а-я,«,».");
		}
		
		/* Проверка тэгов на пустоту */
		$ar = explode(",", $str);
		if (empty($ar))
		{
			throw new Exception("Не указано ни одного тэга");
		}

		foreach ($ar as $key => $val)
		{
			$val = trim($val);
			if (empty($val))
			{
				throw new Exception("Пустой тэг или лишняя зяпятая");
			}

			$ar[$key] = $val;
		}

		/* Повторяющиеся тэги */
		if (count($ar) !== count(array_unique($ar)))
		{
			throw new Exception("Повторяющиеся тэги");
		}
	}

	/**
	 * Путь к файлу или каталогу
	 * 
	 * @param string $str
	 */
	private static function _path(string $str)
	{
		/* Символ "." */
		if ($str === "." or $str === "/")
		{
			return true;
		}

		/* Срезаем символы слэша в начале и конце */
		if (mb_substr($str, 0, 1) === "/")
		{
			$str = mb_substr($str, 1, mb_strlen($str) - 1);
		}

		if (mb_substr($str, mb_strlen($str) - 1, 1) === "/")
		{
			$str = mb_substr($str, 0, mb_strlen($str) - 1);
		}

		/* Разбор */
		$str_ar = explode("/", $str);
		foreach ($str_ar as $val)
		{
			/* Указание в пути ".." и "." */
			if ($val === "." or $val === "..")
			{
				throw new Exception("Использовать имя файла как «..» и «.» запрещено.");
			}

			/* Строка с начальными или конечными пробелами */
			if (mb_strlen($val) !== mb_strlen(trim($val)))
			{
				throw new Exception("Пробелы в начале или в конце имени файла.");
			}

			/* Не указано имя файла */
			if (trim($val) === "")
			{
				throw new Exception("Не задано имя файла.");
			}
		}
	}
	
	/**
	 * IP-адрес
	 * 
	 * @param string $str
	 */
	private static function _ip(string $str)
	{
		if (!filter_var($str, FILTER_VALIDATE_IP))
		{
			throw new Exception("Не прошёл валидацию.");
		}
	}
	
	/**
	 * Порядковые номер (int DEFAULT nextval(SEQUENCE) PRIMARY)
	 * 
	 * @param string $str
	 */
	private static function _id(string $str)
	{
		self::_uint($str);
	}
	
	/**
	 * Поле сортировки (int DEFAULT currval(ID SEQUENCE))
	 * 
	 * @param string $str
	 */
	private static function _order(string $str)
	{
		self::_uint($str);
	}
}
?>