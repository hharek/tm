<?php
namespace TM\Type;

/**
 * Строка без содержания тега <script>
 */
class Html extends Text
{
	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_string($value))
			throw new \Exception("Не является строкой.");

		if (strpos($value, chr(0)) !== false)
			throw new \Exception("Обнаружен нулевой символ.");

		if (mb_detect_encoding($value, "UTF-8") === false)
			throw new \Exception("Бинарная строка, либо символы не в UTF-8.");

		if (mb_stripos($value, "<script") !== false)
			throw new \Exception("Обнаружен тег «script».");

		return true;
	}
}
?>