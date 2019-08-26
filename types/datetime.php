<?php
namespace TM\Types;

/**
 * Дата и время
 */
class Datetime extends \TM\Column
{
	public $type_sql = "timestamp";
	public $type_php = "string";
	public $default_sql = "now()";
	public $require = false;

	public static function check($value, \TM\Column $column = null): bool
	{
		if (strtotime($value) === false)
			throw new \Exception("Не является строкой даты или времени.");

		return true;
	}

	public static function prepare($value, \TM\Column $column = null): string
	{
		return date ("Y-m-d H:i:s", strtotime($value));
	}
}
?>