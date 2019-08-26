<?php
namespace TM\Types;

/**
 * Поле со списком допустимых значений
 */
class Enum extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";

	/**
	 * Допустимые значения для поля
	 *
	 * @var array
	 */
	public $enum_values = [];

	public static function check($value, Enum $column = null): bool
	{
		if (!is_scalar($value))
			throw new \Exception("Не является строкой.");
		$value = (string)$value;

		if (!in_array($value, $column->enum_values))
			throw new \Exception("Доступные значения: " . implode(", ", $column->enum_values) . ".");

		return true;
	}
}
?>