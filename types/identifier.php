<?php
namespace TM\Types;

/**
 * Идентификатор
 * Строка в 127 символов. Уникальна.
 */
class Identifier extends \TM\Column
{
	public $type_sql = "varchar(127)";
	public $type_php = "string";
	public $prepare = "strtolower";
	public $unique = true;

	public static function check($value, \TM\Column $column = null): bool
	{
		\TM\Column::check($value, $column);
		$value = (string)$value;

		if (ctype_alnum(str_replace("_", "", $value)) === false)
			throw new \Exception("Допускаются символы: a-z,0-9,\"_\" .");

		if (mb_strlen($value) > 127)
			throw new \Exception("Большая строка.");

		return true;
	}
}
?>