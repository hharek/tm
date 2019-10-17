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
	public $index = true;

	/**
	 * Формат даты и времени
	 *
	 * @var string
	 */
	public $datetime_format = "d.m.Y - H:i:s";

	/**
	 * Формат даты и времени в запросе
	 *
	 * @var string
	 */
	public $datetime_format_sql = "Y-m-d H:i:s";

	public function check ($value): bool
	{
		if (is_string($value) && in_array($value, ["now", "now()"]))
			return true;

		if (strtotime($value) === false)
			throw new \Exception("Не является строкой даты или времени.");

		return true;
	}

	public function prepare ($value): string
	{
		if (is_string($value) && in_array($value, ["now", "now()"]))
			return "now()";

		return date($this->datetime_format_sql, strtotime($value));
	}

	public function process (string $value)
	{
		return date($this->datetime_format, strtotime($value));
	}

	public static function verify (array $info, string $table): bool
	{
		/* https://postgrespro.ru/docs/postgresql/11/datatype-datetime */

		if (in_array($info['data_type'], ["timestamp without time zone", "timestamp with time zone"]))
			return true;

		return false;
	}
}
?>