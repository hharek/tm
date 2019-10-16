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

	public function check ($value) : bool
	{
		if (strtotime($value) === false)
			throw new \Exception("Не является строкой даты или времени.");

		return true;
	}

	public function prepare ($value) : string
	{
		return date ("Y-m-d", strtotime($value));
	}

	public function process (string $value)
	{
		return date ($this->date_format, strtotime($value));
	}

	public static function verify (array $info, string $table) : bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-datetime */

		if ($info['data_type'] === "date")
			return true;

		return false;
	}
}
?>