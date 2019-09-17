<?php
namespace TM\Type;

/**
 * Дата
 */
class Date extends \TM\Column
{
	public $type_sql = "date";
	public $type_php = "string";
	public $index = true;

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

	/**
	 * @param string $value
	 * @param \TM\Column|Date|null $column
	 * @return string
	 */
	public static function process(string $value, \TM\Column $column = null)
	{
		return date ($column->date_format, strtotime($value));
	}

	public static function verify(array $info, string $table): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-datetime */

		if ($info['data_type'] === "date")
			return true;

		return false;
	}
}
?>