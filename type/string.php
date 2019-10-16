<?php
namespace TM\Type;

/**
 * Строка не более 255 символов, и без пробельных символов
 */
class _String extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";
	public $equal = "ilike";

	public function check ($value) : bool
	{
		if (!is_string($value) || is_numeric($value))
			throw new \Exception("Недопустимое значение.");
		$value = (string)$value;

		if (strpos($value, chr(0)) !== false)
			throw new \Exception("Обнаружен нулевой символ.");

		if (mb_detect_encoding($value, "UTF-8") === false)
			throw new \Exception("Бинарная строка, либо символы не в UTF-8.");

		if (strpbrk($value, "\n\r\t\v\f") !== false)
			throw new \Exception("Недопустимые символы.");

		if (strpbrk($value, "><") !== false)
			throw new \Exception("HTML-символы.");

		if (mb_strlen($value) > 255)
			throw new \Exception("Большая строка.");

		return true;
	}

	public static function verify (array $info, string $table) : bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-character */

		if (!in_array($info['data_type'], ["character varying", "character"]))
			return false;

		if ((int)$info['character_maximum_length'] > 255)
			return false;

		return true;
	}
}

/* Алиасы */
class Str extends _String {};
?>