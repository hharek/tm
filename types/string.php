<?php
namespace TM\Types;

/**
 * Строка не более 255 символов, и без пробельных символов
 */
class _String extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";
	public $equal = "ilike";

	public static function check ($value, \TM\Column $column = null) : bool
	{
		\TM\Column::check($value, $column);
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
}

/* Алиасы */
class Str extends _String {};
?>