<?php
namespace TM\Types;

/**
 * Число со знаком
 */
class _Int extends \TM\Column
{
	public $type_sql = "int";
	public $type_php = "int";

	public static function check ($value, \TM\Column $column = null) : bool
	{
		if (!is_numeric($value))
			throw new \Exception("Не является числом.");

		if (is_float($value))
			throw new \Exception("Тип float.");

		if (is_string($value) && strpos($value, ".") !== false)
			throw new \Exception("Тип float.");

		return true;
	}
}

/* Алиасы */
class Integer extends _Int {};
class Number extends _Int {};
class Num extends _Int {};
?>