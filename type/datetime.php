<?php
namespace TM\Type;

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

	public static function verify(array $info): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-datetime */

		if (in_array($info['data_type'], ["timestamp without time zone", "timestamp with time zone"]))
			return true;

		return false;
	}
}
?>