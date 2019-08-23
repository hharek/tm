<?php
namespace TM\Types;

/**
 * Строка без содержания тега <script>
 */
class Html extends Text
{
	public static function check($value, \TM\Column $column = null): bool
	{
		\TM\Column::check($value, $column);
		$value = (string)$value;

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