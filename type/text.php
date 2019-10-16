<?php
namespace TM\Type;

/**
 * Cтрока без html-тегов
 */
class Text extends \TM\Column
{
	public $type_sql = "text";
	public $type_php = "string";
	public $lite = false;
	public $equal = "ilike";
	public $empty = true;

	public function check ($value) : bool
	{
		if (!is_string($value))
			throw new \Exception("Не является строкой.");

		if (strpos($value, chr(0)) !== false)
			throw new \Exception("Обнаружен нулевой символ.");

		if (mb_detect_encoding($value, "UTF-8") === false)
			throw new \Exception("Бинарная строка, либо символы не в UTF-8.");

		if (strpbrk($value, "><") !== false)
			throw new \Exception("HTML-символы.");

		return true;
	}
}
?>