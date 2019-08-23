<?php
namespace TM\Types;

/**
 * Почтовый ящик
 */
class Email extends \TM\Column
{
	public $type_sql = "varchar(127)";
	public $type_php = "string";
	public $prepare = "strtolower";

	public static function check($value, \TM\Column $column = null): bool
	{
		\TM\Column::check($value, $column);
		$value = (string)$value;

		if (!filter_var($value, FILTER_VALIDATE_EMAIL))
			throw new \Exception("Не является почтовым ящиком.");

		return true;
	}
}
?>