<?php
namespace TM\Type;

/**
 * Цена - два числа после точки всегда положительная
 */
class Price extends \TM\Column
{
	public $type_sql = "numeric(10,2)";
	public $type_php = "float";

	public static function check($value, \TM\Column $column = null): bool
	{
		if (is_string($value))
			$value = str_replace(",", ".", $value);

		if (!is_numeric($value))
			throw new \Exception("Не является числом.");

		if ((int)$value < 0)
			throw new \Exception("Отрицательное число.");

		return true;
	}

	public static function prepare($value, \TM\Column $column = null): string
	{
		if (is_string($value))
			return str_replace(",", ".", $value);
		else
			return (string)$value;
	}
}
?>