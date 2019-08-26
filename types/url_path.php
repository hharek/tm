<?php
namespace TM\Types;

/**
 * Путь урла
 */
class Url_Path extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";
	public $prepare = "mb_strtolower";

	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_scalar($value))
			throw new \Exception("Не является строкой.");

		/* Срезаем символы слэша в начале и конце */
		if (mb_substr($value, 0, 1) === "/")
			$value = mb_substr($value, 1, mb_strlen($value) - 1);

		if (mb_substr($value, mb_strlen($value) - 1, 1) === "/")
			$value = mb_substr($value, 0, mb_strlen($value) - 1);

		/* Разбор */
		$value_ar = explode("/", $value);
		foreach ($value_ar as $v)
		{
			Url_Part::check($v);
		}

		return true;
	}
}
?>