<?php
namespace TM\Types;

/**
 * Дата
 */
class Date extends \TM\Column
{
	public $type_sql = "date";
	public $type_php = "string";

	public static function check($value, \TM\Column $column = null): bool
	{
		if (strtotime($value) === false)
			throw new \Exception("Не является строкой даты или времени.");

		return true;
	}

	public static function prepare($value, \TM\Column $column = null): string
	{
		return date ("Y-m-d", strtotime($value));
	}
}
?>