<?php
namespace TM\Type;

/**
 * Дата
 */
class Date extends \TM\Column
{
	public $type_sql = "date";
	public $type_php = "string";

	/**
	 * Формат представления даты
	 *
	 * @var string
	 * @example "d.m.Y", "Y-m-d"
	 */
	public $date_format = "d.m.Y";

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

	public static function process(string $value, Date $column = null)
	{
		return date ($column->date_format, strtotime($value));
	}
}
?>