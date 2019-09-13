<?php
namespace TM\Type;

/**
 * Поле со списком допустимых значений
 */
class Enum extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";

	/**
	 * Массив допустимых значении для поля.
	 * Только скалярные типы
	 *
	 * @var array
	 */
	public $enum_values = [];

	/**
	 * @param mixed $value
	 * @param \TM\Column|null $column
	 * @return bool
	 */
	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_scalar($value))
			throw new \Exception("Недопустимое значение");

		if (!in_array($value, $column->enum_values))
			throw new \Exception("Доступные значения: " . implode(", ", $column->enum_values) . ".");

		return true;
	}
}
?>